<?php

namespace App\Models;

use App\Events\AssetCheckedOut;
use App\Events\CheckoutableCheckedOut;
use App\Exceptions\CheckoutNotAllowed;
use App\Helpers\Helper;
use App\Http\Traits\UniqueSerialTrait;
use App\Http\Traits\UniqueUndeletedTrait;
use App\Models\Traits\Acceptable;
use App\Models\Traits\Searchable;
use App\Presenters\Presentable;
use MovementPresenter;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Watson\Validating\ValidatingTrait;

/**
 * Model for movements.
 *
 * @version    v1.0
 */
class Movement extends Depreciable
{

    protected $presenter = \App\Presenters\MovementPresenter::class;

    use CompanyableTrait;
    use HasFactory, Loggable, Requestable, Presentable, SoftDeletes, ValidatingTrait, UniqueUndeletedTrait, UniqueSerialTrait;

    public const LOCATION = 'location';
    public const MOVEMENT = 'movement';
    public const USER = 'user';

    use Acceptable;

    /**
     * Run after the checkout acceptance was declined by the user
     *
     * @param  User   $acceptedBy
     * @param  string $signature
     */
    public function declinedCheckout(User $declinedBy, $signature)
    {
      $this->assigned_to = null;
      $this->assigned_type = null;
      $this->accepted = null;
      $this->save();
    }

    /**
    * The database table used by the model.
    *
    * @var string
    */
    protected $table = 'movement';

    /**
    * Whether the model should inject it's identifier to the unique
    * validation rules before attempting validation. If this property
    * is not set in the model it will default to true.
    *
     * @var bool
    */
    protected $injectUniqueIdentifier = true;

    protected $casts = [
        'purchase_date' => 'date',
        'last_checkout' => 'datetime',
        'expected_checkin' => 'date',
        'last_audit_date' => 'datetime',
        'next_audit_date' => 'date',
        'model_id'       => 'integer',
        'status_id'      => 'integer',
        'company_id'     => 'integer',
        'location_id'    => 'integer',
        'rtd_company_id' => 'integer',
        'supplier_id'    => 'integer',
        'byod'           => 'boolean',
        'created_at'     => 'datetime',
        'updated_at'   => 'datetime',
        'deleted_at'  => 'datetime',
    ];

    protected $rules = [
        'name'            => 'required|max:255|nullable',
        'model_id'        => 'required|integer|exists:models,id,deleted_at,NULL',
        'status_id'       => 'required|integer|exists:status_labels,id',
        'company_id'      => 'integer|nullable',
        'warranty_months' => 'numeric|nullable|digits_between:0,240',
        'physical'        => 'numeric|max:1|nullable',
        'last_checkout'    => 'date_format:Y-m-d H:i:s|nullable',
        'expected_checkin' => 'date|nullable',
        'location_id'     => 'exists:locations,id|nullable',
        'rtd_location_id' => 'exists:locations,id|nullable',
        'movement_tag'       => 'required|min:1|max:255|unique_undeleted',
        'purchase_date'   => 'date|date_format:Y-m-d|nullable',
        'serial'          => 'unique_serial|nullable',
        'purchase_cost'   => 'numeric|nullable|gte:0',
        'supplier_id'     => 'exists:suppliers,id|nullable',
        'movement_eol_date'  => 'date|max:10|min:10|nullable',
    ];

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
    protected $fillable = [
        'movement_tag',
        'assigned_to',
        'assigned_type',
        'company_id',
        'image',
        'location_id',
        'model_id',
        'name',
        'notes',
        'order_number',
        'purchase_cost',
        'purchase_date',
        'rtd_location_id',
        'serial',
        'status_id',
        'supplier_id',
        'warranty_months',
        'requestable',
        'last_checkout',
        'expected_checkin',
        'byod',
        'movement_eol_date',
        'last_audit_date',
        'next_audit_date',
    ];

    use Searchable;

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = [
      'name',
      'movement_tag',
      'serial',
      'order_number',
      'purchase_cost',
      'notes',
      'created_at',
      'updated_at',
      'purchase_date',
      'expected_checkin',
      'next_audit_date',
      'last_audit_date',
      'movement_eol_date',
    ];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [
        'movementstatus'        => ['name'],
        'supplier'           => ['name'],
        'company'            => ['name'],
        'defaultLoc'         => ['name'],
        'location'           => ['name'],
        'model'              => ['name', 'model_number', 'eol'],
        'model.category'     => ['name'],
        'model.manufacturer' => ['name'],
    ];

    // To properly set the expected checkin as Y-m-d
    public function setExpectedCheckinAttribute($value)
    {
        if ($value == '') {
            $value = null;
        }
        $this->attributes['expected_checkin'] = $value;
    }

    /**
     * This handles the custom field validation for movements
     *
     * @var array
     */
    public function save(array $params = [])
    {
        if ($this->model_id != '') {
            $model = MovementModel::find($this->model_id);

            if (($model) && ($model->fieldset)) {

                foreach ($model->fieldset->fields as $field){
                    if($field->format == 'BOOLEAN'){
                        $this->{$field->db_column} = filter_var($this->{$field->db_column}, FILTER_VALIDATE_BOOLEAN);
                    }
                }

                $this->rules += $model->fieldset->validation_rules();

                foreach ($this->model->fieldset->fields as $field){
                    if($field->format == 'BOOLEAN'){
                        $this->{$field->db_column} = filter_var($this->{$field->db_column}, FILTER_VALIDATE_BOOLEAN);
                    }
                }
            }
        }



        return parent::save($params);
    }


    public function getDisplayNameAttribute()
    {
        return $this->present()->name();
    }

    /**
     * Returns the warranty expiration date as Carbon object
     * @return \Carbon|null
     */
    public function getWarrantyExpiresAttribute()
    {
        if (isset($this->attributes['warranty_months']) && isset($this->attributes['purchase_date'])) {
            if (is_string($this->attributes['purchase_date']) || is_string($this->attributes['purchase_date'])) {
                $purchase_date = \Carbon\Carbon::parse($this->attributes['purchase_date']);
            } else {
                $purchase_date = \Carbon\Carbon::instance($this->attributes['purchase_date']);
            }
            $purchase_date->setTime(0, 0, 0);

            return $purchase_date->addMonths((int) $this->attributes['warranty_months']);
        }

        return null;
    }


    /**
     * Establishes the movement -> company relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class, 'company_id');
    }

    /**
     * Determines if an movement is available for checkout.
     * This checks to see if the it's checked out to an invalid (deleted) user
     * OR if the assigned_to and deleted_at fields on the movement are empty AND
     * that the status is deployable
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     * @return bool
     */
    public function availableForCheckout()
    {

        // This movement is not currently assigned to anyone and is not deleted...
        if ((! $this->assigned_to) && (! $this->deleted_at)) {

            // The movement status is not archived and is deployable
            if (($this->movementstatus) && ($this->movementstatus->archived == '0')
                && ($this->movementstatus->deployable == '1'))
            {
                return true;

            }
        }
        return false;
    }


    /**
     * Checks the movement out to the target
     *
     * @todo The admin parameter is never used. Can probably be removed.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param User $user
     * @param User $admin
     * @param Carbon $checkout_at
     * @param Carbon $expected_checkin
     * @param string $note
     * @param null $name
     * @return bool
     * @since [v3.0]
     * @return bool
     */
    public function checkOut($target, $admin = null, $checkout_at = null, $expected_checkin = null, $note = null, $name = null, $location = null)
    {
        if (! $target) {
            return false;
        }
        if ($this->is($target)) {
            throw new CheckoutNotAllowed('You cannot check an movement out to itself.');
        }

        if ($expected_checkin) {
            $this->expected_checkin = $expected_checkin;
        }

        $this->last_checkout = $checkout_at;
        $this->name = $name;

        $this->assignedTo()->associate($target);

        if ($location != null) {
            $this->location_id = $location;
        } else {
            if (isset($target->location)) {
                $this->location_id = $target->location->id;
            }
            if ($target instanceof Location) {
                $this->location_id = $target->id;
            }
        }

        if ($this->save()) {
            if (is_int($admin)) {
                $checkedOutBy = User::findOrFail($admin);
            } elseif (get_class($admin) === \App\Models\User::class) {
                $checkedOutBy = $admin;
            } else {
                $checkedOutBy = Auth::user();
            }
            event(new CheckoutableCheckedOut($this, $target, $checkedOutBy, $note));

            $this->increment('checkout_counter', 1);

            return true;
        }

        return false;
    }

    /**
     * Sets the detailedNameAttribute
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     * @return string
     */
    public function getDetailedNameAttribute()
    {
        if ($this->assignedto) {
            $user_name = $this->assignedto->present()->name();
        } else {
            $user_name = 'Unassigned';
        }

        return $this->movement_tag.' - '.$this->name.' ('.$user_name.') '.($this->model) ? $this->model->name : '';
    }

    /**
     * Pulls in the validation rules
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     * @return array
     */
    public function validationRules()
    {
        return $this->rules;
    }


    /**
     * Establishes the movement -> depreciation relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function depreciation()
    {
        return $this->model->belongsTo(\App\Models\Depreciation::class, 'depreciation_id');
    }


    /**
     * Get components assigned to this movement
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function components()
    {
        return $this->belongsToMany('\App\Models\Component', 'components_movements', 'movement_id', 'component_id')->withPivot('id', 'assigned_qty', 'created_at');
    }


    /**
     * Get depreciation attribute from associated movement model
     *
     * @todo Is this still needed?
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function get_depreciation()
    {
        if (($this->model) && ($this->model->depreciation)) {
            return $this->model->depreciation;
        }
    }


    /**
     * Get uploads for this movement
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function uploads()
    {
        return $this->hasMany('\App\Models\Actionlog', 'item_id')
                  ->where('item_type', '=', Movement::class)
                  ->where('action_type', '=', 'uploaded')
                  ->whereNotNull('filename')
                  ->orderBy('created_at', 'desc');
    }

    /**
     * Determines whether the movement is checked out to a user
     *
     * Even though we allow allow for checkout to things beyond users
     * this method is an easy way of seeing if we are checked out to a user.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @return bool
     */
    public function checkedOutToUser()
    {
      return $this->assignedType() === self::USER;
    }

    /**
     * Get the target this movement is checked out to
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function assignedTo()
    {
        return $this->morphTo('assigned', 'assigned_type', 'assigned_to')->withTrashed();
    }

    /**
     * Gets movements assigned to this movement
     *
     * Sigh.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function assignedMovement()
    {
        return $this->morphMany(self::class, 'assigned', 'assigned_type', 'assigned_to')->withTrashed();
    }


    /**
     * Get the movement's location based on the assigned user
     *
     * @todo Refactor this if possible. It's awful.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @return \ArrayObject
     */
    public function movementLoc($iterations = 1, $first_movement = null)
    {
        if (! empty($this->assignedType())) {
            if ($this->assignedType() == self::movement) {
                if (! $first_movement) {
                    $first_movement = $this;
                }
                if ($iterations > 10) {
                    throw new \Exception('movement assignment Loop for movement ID: '.$first_movement->id);
                }
                $assigned_to = self::find($this->assigned_to); //have to do this this way because otherwise it errors
                if ($assigned_to) {
                    return $assigned_to->movementLoc($iterations + 1, $first_movement);
                } // Recurse until we have a final location
            }
            if ($this->assignedType() == self::LOCATION) {
                if ($this->assignedTo) {
                    return $this->assignedTo;
                }

            }
            if ($this->assignedType() == self::USER) {
                if (($this->assignedTo) && $this->assignedTo->userLoc) {
                    return $this->assignedTo->userLoc;
                }
                //this makes no sense
                return $this->defaultLoc;

            }

        }
        return $this->defaultLoc;
    }

    /**
     * Gets the lowercased name of the type of target the movement is assigned to
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @return string
     */
    public function assignedType()
    {
        return strtolower(class_basename($this->assigned_type));
    }



    /**
     * This is annoying, but because we don't say "movements" in our route names, we have to make an exception here
     * @todo - normalize the route names - API endpoint URLS can stay the same
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v6.1.0]
     * @return string
     */
    public function targetShowRoute()
    {
        $route = str_plural($this->assignedType());
        if ($route=='movement') {
            return 'movement';
        }

        return $route;

    }


    /**
     * Get the movement's location based on default RTD location
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v2.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function defaultLoc()
    {
        return $this->belongsTo(\App\Models\Location::class, 'rtd_location_id');
    }

    /**
     * Get the image URL of the movement.
     *
     * Check first to see if there is a specific image uploaded to the movement,
     * and if not, check for an image uploaded to the movement model.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v2.0]
     * @return string | false
     */
    public function getImageUrl()
    {
        if ($this->image && ! empty($this->image)) {
            return Storage::disk('public')->url(app('movement_upload_path').e($this->image));
        } elseif ($this->model && ! empty($this->model->image)) {
            return Storage::disk('public')->url(app('models_upload_path').e($this->model->image));
        }

        return false;
    }


    /**
     * Get the movement's logs
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v2.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function movementlog()
    {
        return $this->hasMany(\App\Models\Actionlog::class, 'item_id')
                  ->where('item_type', '=', self::class)
                  ->orderBy('created_at', 'desc')
                  ->withTrashed();
    }

    /**
     * Get the list of checkouts for this movement
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v2.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function checkouts()
    {
        return $this->movementlog()->where('action_type', '=', 'checkout')
            ->orderBy('created_at', 'desc')
            ->withTrashed();
    }

    /**
     * Get the list of checkins for this movement
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v2.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function checkins()
    {
        return $this->movementlog()
            ->where('action_type', '=', 'checkin from')
            ->orderBy('created_at', 'desc')
            ->withTrashed();
    }

    /**
     * Get the movement's user requests
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v2.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function userRequests()
    {
        return $this->movementlog()
            ->where('action_type', '=', 'requested')
            ->orderBy('created_at', 'desc')
            ->withTrashed();
    }


    /**
     * Get maintenances for this movement
     *
     * @author  Vincent Sposato <vincent.sposato@gmail.com>
     * @since 1.0
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function movementmaintenances()
    {
        return $this->hasMany(\App\Models\AssetMaintenance::class, 'asset_id')
                  ->orderBy('created_at', 'desc');
    }

    /**
     * Get action logs history for this movement
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function adminuser()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }



    /**
     * Establishes the movement -> status relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function movementtatus()
    {
        return $this->belongsTo(\App\Models\Statuslabel::class, 'status_id');
    }

    /**
     * Establishes the movement -> model relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function model()
    {
        return $this->belongsTo(\App\Models\MovementModel::class, 'model_id')->withTrashed();
    }

    /**
     * Return the movement with a warranty expiring within x days
     *
     * @param $days
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v2.0]
     * @return mixed
     */
    public static function getExpiringWarrantee($days = 30)
    {
        $days = (is_null($days)) ? 30 : $days;

        return self::where('archived', '=', '0')
            ->whereNotNull('warranty_months')
            ->whereNotNull('purchase_date')
            ->whereNull('deleted_at')
            ->whereRaw('DATE_ADD(`purchase_date`,INTERVAL `warranty_months` MONTH) <= DATE(NOW() + INTERVAL '
                                 . $days
                                 . ' DAY) AND DATE_ADD(`purchase_date`, INTERVAL `warranty_months` MONTH) > NOW()')
            ->orderByRaw('DATE_ADD(`purchase_date`,INTERVAL `warranty_months` MONTH)')
            ->get();
    }


    /**
     * Establishes the movement -> assigned licenses relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function licenses()
    {
        return $this->belongsToMany(\App\Models\License::class, 'license_seats', 'movement_id', 'license_id');
    }

    /**
     * Establishes the movement -> status relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function licenseseats()
    {
        return $this->hasMany(\App\Models\LicenseSeat::class, 'movement_id');
    }

    /**
     * Establishes the movement -> aupplier relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v2.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function supplier()
    {
        return $this->belongsTo(\App\Models\Supplier::class, 'supplier_id');
    }

    /**
     * Establishes the movement -> location relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v2.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function location()
    {
        return $this->belongsTo(\App\Models\Location::class, 'location_id');
    }



   /**
 * Get the next autoincremented movement tag
 *
 * @author [A. Gianotto] [<snipe@snipe.net>]
 * @since [v4.0]
 * @return string | false
 */
public static function autoincrement_movement()
{
    $settings = \App\Models\Setting::getSettings();

    if ($settings->auto_increment_movement == '1') {
        $today = now()->format('dmY');
        $latestMovement = self::where('movement_tag', 'like', "{$today}%")->latest()->first();

        if ($latestMovement) {
            $suffix = (int)explode('-', $latestMovement->movement_tag)[1];
            $suffix++;
        } else {
            $suffix = 1;
        }

        if ($settings->zerofill_count > 0) {
            return $settings->auto_increment_prefix.self::zerofill($suffix, $settings->zerofill_count);
        }

        return $settings->auto_increment_prefix.$suffix;
    } else {
        return false;
    }
}



    /**
     * Get the next base number for the auto-incrementer.
     *
     * We'll add the zerofill and prefixes on the fly as we generate the number.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @return int
     */
    public static function nextAutoIncrement($movement)
    {

        $max = 1;

        foreach ($movement as $movement) {
            $results = preg_match("/\d+$/", $movement['movement_tag'], $matches);

            if ($results)
            {
                $number = $matches[0];

                if ($number > $max)
                {
                    $max = $number;
                }
            }
        }


    }



    /**
     * Add zerofilling based on Settings
     *
     * We'll add the zerofill and prefixes on the fly as we generate the number.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @return string
     */
    public static function zerofill($num, $zerofill = 3)
    {
        return str_pad($num, $zerofill, '0', STR_PAD_LEFT);
    }

    /**
     * Determine whether to send a checkin/checkout email based on
     * movement model category
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @return bool
     */
    public function checkin_email()
    {
        if (($this->model) && ($this->model->category)) {
            return $this->model->category->checkin_email;
        }
    }

    /**
     * Determine whether this movement requires acceptance by the assigned user
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @return bool
     */
    public function requireAcceptance()
    {
        if (($this->model) && ($this->model->category)) {
            return $this->model->category->require_acceptance;
        }

    }

    /**
     * Checks for a category-specific EULA, and if that doesn't exist,
     * checks for a settings level EULA
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @return string | false
     */
    public function getEula()
    {

        if (($this->model) && ($this->model->category)) {
            if ($this->model->category->eula_text) {
                return Helper::parseEscapedMarkedown($this->model->category->eula_text);
            } elseif ($this->model->category->use_default_eula == '1') {
                return Helper::parseEscapedMarkedown(Setting::getSettings()->default_eula_text);
            } else {
                return false;
            }
        }

        return false;
    }
    public function getComponentCost(){
        $cost = 0;
        foreach($this->components as $component) {
            $cost += $component->pivot->assigned_qty*$component->purchase_cost;
        }
        return $cost;
    }

    /**
    * -----------------------------------------------
    * BEGIN QUERY SCOPES
    * -----------------------------------------------
    **/

    /**
     * Run additional, advanced searches.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array  $terms The search terms
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function advancedTextSearch(Builder $query, array $terms)
    {

        /**
         * Assigned user
         */
        $query = $query->leftJoin('users as movements_users', function ($leftJoin) {
            $leftJoin->on('movements_users.id', '=', 'movements.assigned_to')
                ->where('movements.assigned_type', '=', User::class);
        });

        foreach ($terms as $term) {

            $query = $query
                ->orWhere('movements_users.first_name', 'LIKE', '%'.$term.'%')
                ->orWhere('movements_users.last_name', 'LIKE', '%'.$term.'%')
                ->orWhere('movements_users.username', 'LIKE', '%'.$term.'%')
                ->orWhereMultipleColumns([
                    'movements_users.first_name',
                    'movements_users.last_name',
                ], $term);
        }

        /**
         * Assigned location
         */
        $query = $query->leftJoin('locations as movements_locations', function ($leftJoin) {
            $leftJoin->on('movements_locations.id', '=', 'movements.assigned_to')
                ->where('movements.assigned_type', '=', Location::class);
        });

        foreach ($terms as $term) {

            $query = $query->orWhere('movements_locations.name', 'LIKE', '%'.$term.'%');
        }

        /**
         * Assigned movements
         */
        $query = $query->leftJoin('movements as assigned_movements', function ($leftJoin) {
            $leftJoin->on('assigned_movements.id', '=', 'movements.assigned_to')
                ->where('movements.assigned_type', '=', self::class);
        });

        foreach ($terms as $term) {
            $query = $query->orWhere('assigned_movements.name', 'LIKE', '%'.$term.'%');

        }

        return $query;
    }


    /**
    * Query builder scope for movement
    *
    * @param  \Illuminate\Database\Query\Builder $query Query builder instance
    *
    * @return \Illuminate\Database\Query\Builder          Modified query builder
    */

    public function scopeMovement($query)
    {
        return $query->where('physical', '=', '1');
    }

    /**
    * Query builder scope for pending movements
    *
    * @param  \Illuminate\Database\Query\Builder $query Query builder instance
    *
    * @return \Illuminate\Database\Query\Builder          Modified query builder
    */

    public function scopePending($query)
    {
        return $query->whereHas('movementstatus', function ($query) {
            $query->where('deployable', '=', 0)
                ->where('pending', '=', 1)
                ->where('archived', '=', 0);
        });
    }


    /**
    * Query builder scope for searching location
    *
    * @param  \Illuminate\Database\Query\Builder $query Query builder instance
    *
    * @return \Illuminate\Database\Query\Builder          Modified query builder
    */

    public function scopeMovementByLocation($query, $location)
    {
        return $query->where(function ($query) use ($location) {
            $query->whereHas('assignedTo', function ($query) use ($location) {
                $query->where([
                    ['users.location_id', '=', $location->id],
                    ['movements.assigned_type', '=', User::class],
                ])->orWhere([
                    ['locations.id', '=', $location->id],
                    ['movements.assigned_type', '=', Location::class],
                ])->orWhere([
                    ['movements.rtd_location_id', '=', $location->id],
                    ['movements.assigned_type', '=', self::class],
                ]);
            })->orWhere(function ($query) use ($location) {
                $query->where('movements.rtd_location_id', '=', $location->id);
                $query->whereNull('movements.assigned_to');
            });
        });
    }


    /**
    * Query builder scope for RTD movements
    *
    * @param  \Illuminate\Database\Query\Builder $query Query builder instance
    *
    * @return \Illuminate\Database\Query\Builder          Modified query builder
    */

    public function scopeRTD($query)
    {
        return $query->whereNull('movements.assigned_to')
                   ->whereHas('movementstatus', function ($query) {
                       $query->where('deployable', '=', 1)
                             ->where('pending', '=', 0)
                             ->where('archived', '=', 0);
                   });
    }

  /**
   * Query builder scope for Undeployable movements
   *
   * @param  \Illuminate\Database\Query\Builder $query Query builder instance
   *
   * @return \Illuminate\Database\Query\Builder          Modified query builder
   */

    public function scopeUndeployable($query)
    {
        return $query->whereHas('movementstatus', function ($query) {
            $query->where('deployable', '=', 0)
                ->where('pending', '=', 0)
                ->where('archived', '=', 0);
        });
    }

    /**
     * Query builder scope for non-Archived movements
     *
     * @param  \Illuminate\Database\Query\Builder $query Query builder instance
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */

    public function scopeNotArchived($query)
    {
        return $query->whereHas('movementstatus', function ($query) {
            $query->where('archived', '=', 0);
        });
    }

    /**
     * Query builder scope for movements that are due for auditing, based on the movements.next_audit_date
     * and settings.audit_warning_days.
     *
     * This is/will be used in the artisan command snipeit:upcoming-audits and also
     * for an upcoming API call for retrieving a report on movements that will need to be audited.
     *
     * Due for audit soon:
     * next_audit_date greater than or equal to now (must be in the future)
     * and (next_audit_date - threshold days) <= now ()
     *
     * Example:
     * next_audit_date = May 4, 2025
     * threshold for alerts = 30 days
     * now = May 4, 2019
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since v4.6.16
     * @param Setting $settings
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */

    public function scopeDueForAudit($query, $settings)
    {
        $interval = $settings->audit_warning_days ?? 0;

        return $query->whereNotNull('movements.next_audit_date')
            ->where('movements.next_audit_date', '>=', Carbon::now())
            ->whereRaw("DATE_SUB(movements.next_audit_date, INTERVAL $interval DAY) <= '".Carbon::now()."'")
            ->where('movements.archived', '=', 0)
            ->NotArchived();
    }

    /**
     * Query builder scope for movements that are OVERDUE for auditing, based on the movements.next_audit_date
     * and settings.audit_warning_days. It checks to see if movements.next audit_date is before now
     *
     * This is/will be used in the artisan command snipeit:upcoming-audits and also
     * for an upcoming API call for retrieving a report on overdue movements.
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since v4.6.16
     * @param Setting $settings
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */

    public function scopeOverdueForAudit($query)
    {
        return $query->whereNotNull('movements.next_audit_date')
            ->where('movements.next_audit_date', '<', Carbon::now())
            ->where('movements.archived', '=', 0)
            ->NotArchived();
    }

    /**
     * Query builder scope for movements that are due for auditing OR overdue, based on the movements.next_audit_date
     * and settings.audit_warning_days.
     *
     * This is/will be used in the artisan command snipeit:upcoming-audits and also
     * for an upcoming API call for retrieving a report on movements that will need to be audited.
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since v4.6.16
     * @param Setting $settings
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */

    public function scopeDueOrOverdueForAudit($query, $settings)
    {
        $interval = $settings->audit_warning_days ?? 0;

        return $query->whereNotNull('movements.next_audit_date')
            ->whereRaw('DATE_SUB('.DB::getTablePrefix()."movements.next_audit_date, INTERVAL $interval DAY) <= '".Carbon::now()."'")
            ->where('movements.archived', '=', 0)
            ->NotArchived();
    }


    /**
     * Query builder scope for Archived movements counting
     *
     * This is primarily used for the tab counters so that IF the admin
     * has chosen to not display archived movements in their regular lists
     * and views, it will return the correct number.
     *
     * @param  \Illuminate\Database\Query\Builder $query Query builder instance
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */

    public function scopeMovementForShow($query)
    {

        if (Setting::getSettings()->show_archived_in_list!=1) {
            return $query->whereHas('movementstatus', function ($query) {
                $query->where('archived', '=', 0);
            });
        } else {
            return $query;
        }

    }

  /**
   * Query builder scope for Archived movements
   *
   * @param  \Illuminate\Database\Query\Builder $query Query builder instance
   *
   * @return \Illuminate\Database\Query\Builder          Modified query builder
   */

    public function scopeArchived($query)
    {
        return $query->whereHas('movementstatus', function ($query) {
            $query->where('deployable', '=', 0)
                ->where('pending', '=', 0)
                ->where('archived', '=', 1);
        });
    }

  /**
   * Query builder scope for Deployed movements
   *
   * @param  \Illuminate\Database\Query\Builder $query Query builder instance
   *
   * @return \Illuminate\Database\Query\Builder          Modified query builder
   */

    public function scopeDeployed($query)
    {
        return $query->where('assigned_to', '>', '0');
    }

  /**
   * Query builder scope for Requestable movements
   *
   * @param  \Illuminate\Database\Query\Builder $query Query builder instance
   *
   * @return \Illuminate\Database\Query\Builder          Modified query builder
   */

    public function scopeRequestableMovements($query)
    {
        $table = $query->getModel()->getTable();

        return Company::scopeCompanyables($query->where($table.'.requestable', '=', 1))
        ->whereHas('movementstatus', function ($query) {
            $query->where(function ($query) {
                $query->where('deployable', '=', 1)
                      ->where('archived', '=', 0); // you definitely can't request something that's archived
            })->orWhere('pending', '=', 1); // we've decided that even though an movement may be 'pending', you can still request it
        });
    }


    /**
   * scopeInModelList
   * Get all movements in the provided listing of model ids
   *
   * @param       $query
   * @param array $modelIdListing
   *
   * @return mixed
   * @author  Vincent Sposato <vincent.sposato@gmail.com>
   * @version v1.0
   */
    public function scopeInModelList($query, array $modelIdListing)
    {
        return $query->whereIn('movements.model_id', $modelIdListing);
    }

  /**
  * Query builder scope to get not-yet-accepted movements
  *
  * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
  *
  * @return \Illuminate\Database\Query\Builder          Modified query builder
  */
    public function scopeNotYetAccepted($query)
    {
        return $query->where('accepted', '=', 'pending');
    }

  /**
  * Query builder scope to get rejected movements
  *
  * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
  *
  * @return \Illuminate\Database\Query\Builder          Modified query builder
  */
    public function scopeRejected($query)
    {
        return $query->where('accepted', '=', 'rejected');
    }


  /**
  * Query builder scope to get accepted movements
  *
  * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
  *
  * @return \Illuminate\Database\Query\Builder          Modified query builder
  */
    public function scopeAccepted($query)
    {
        return $query->where('accepted', '=', 'accepted');
    }

    /**
     * Query builder scope to search on text for complex Bootstrap Tables API.
     *
     * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
     * @param  text                              $search      Search term
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeAssignedSearch($query, $search)
    {
        $search = explode(' OR ', $search);

        return $query->leftJoin('users as movements_users', function ($leftJoin) {
            $leftJoin->on('movements_users.id', '=', 'movements.assigned_to')
                ->where('movements.assigned_type', '=', User::class);
        })->leftJoin('locations as movements_locations', function ($leftJoin) {
            $leftJoin->on('movements_locations.id', '=', 'movements.assigned_to')
                ->where('movements.assigned_type', '=', Location::class);
        })->leftJoin('movements as assigned_movements', function ($leftJoin) {
            $leftJoin->on('assigned_movements.id', '=', 'movements.assigned_to')
                ->where('movements.assigned_type', '=', self::class);
        })->where(function ($query) use ($search) {
            foreach ($search as $search) {
                $query->whereHas('model', function ($query) use ($search) {
                    $query->whereHas('category', function ($query) use ($search) {
                        $query->where(function ($query) use ($search) {
                            $query->where('categories.name', 'LIKE', '%'.$search.'%')
                                ->orWhere('models.name', 'LIKE', '%'.$search.'%')
                                ->orWhere('models.model_number', 'LIKE', '%'.$search.'%');
                        });
                    });
                })->orWhereHas('model', function ($query) use ($search) {
                    $query->whereHas('manufacturer', function ($query) use ($search) {
                        $query->where(function ($query) use ($search) {
                            $query->where('manufacturers.name', 'LIKE', '%'.$search.'%');
                        });
                    });
                })->orWhere(function ($query) use ($search) {
                    $query->where('movements_users.first_name', 'LIKE', '%'.$search.'%')
                        ->orWhere('movements_users.last_name', 'LIKE', '%'.$search.'%')
                        ->orWhereMultipleColumns([
                            'movements_users.first_name',
                            'movements_users.last_name',
                        ], $search)
                        ->orWhere('movements_users.username', 'LIKE', '%'.$search.'%')
                        ->orWhere('movements_locations.name', 'LIKE', '%'.$search.'%')
                        ->orWhere('assigned_movements.name', 'LIKE', '%'.$search.'%');
                })->orWhere('movements.name', 'LIKE', '%'.$search.'%')
                    ->orWhere('movements.movement_tag', 'LIKE', '%'.$search.'%')
                    ->orWhere('movements.serial', 'LIKE', '%'.$search.'%')
                    ->orWhere('movements.order_number', 'LIKE', '%'.$search.'%')
                    ->orWhere('movements.notes', 'LIKE', '%'.$search.'%');
            }

        })->withTrashed()->whereNull('movements.deleted_at'); //workaround for laravel bug
    }

    /**
     * Query builder scope to search the department ID of users assigned to movements
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v5.0]
     * @return string | false
     *
     * @return \Illuminate\Database\Query\Builder Modified query builder
     */
    public function scopeCheckedOutToTargetInDepartment($query, $search)
    {
        return $query->leftJoin('users as movements_dept_users', function ($leftJoin) {
            $leftJoin->on('movements_dept_users.id', '=', 'movements.assigned_to')
                ->where('movements.assigned_type', '=', User::class);
        })->where(function ($query) use ($search) {
                    $query->where('movements_dept_users.department_id', '=', $search);

        })->withTrashed()->whereNull('movements.deleted_at'); //workaround for laravel bug
    }



    /**
     * Query builder scope to search on text filters for complex Bootstrap Tables API
     *
     * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
     * @param  text   $filter   JSON array of search keys and terms
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeByFilter($query, $filter)
    {
        return $query->where(function ($query) use ($filter) {
            foreach ($filter as $key => $search_val) {

                $fieldname = str_replace('custom_fields.', '', $key);

                if ($fieldname == 'movement_tag') {
                    $query->where('movements.movement_tag', 'LIKE', '%'.$search_val.'%');
                }

                if ($fieldname == 'name') {
                    $query->where('movements.name', 'LIKE', '%'.$search_val.'%');
                }


                if ($fieldname =='serial') {
                    $query->where('movements.serial', 'LIKE', '%'.$search_val.'%');
                }

                if ($fieldname == 'purchase_date') {
                    $query->where('movements.purchase_date', 'LIKE', '%'.$search_val.'%');
                }

                if ($fieldname == 'purchase_cost') {
                    $query->where('movements.purchase_cost', 'LIKE', '%'.$search_val.'%');
                }

                if ($fieldname == 'notes') {
                    $query->where('movements.notes', 'LIKE', '%'.$search_val.'%');
                }

                if ($fieldname == 'order_number') {
                    $query->where('movements.order_number', 'LIKE', '%'.$search_val.'%');
                }

                if ($fieldname == 'status_label') {
                    $query->whereHas('movementstatus', function ($query) use ($search_val) {
                        $query->where('status_labels.name', 'LIKE', '%'.$search_val.'%');
                    });
                }

                if ($fieldname == 'location') {
                    $query->whereHas('location', function ($query) use ($search_val) {
                        $query->where('locations.name', 'LIKE', '%'.$search_val.'%');
                    });
                }

                if ($fieldname =='assigned_to') {
                    $query->whereHasMorph('assignedTo', [User::class], function ($query) use ($search_val) {
                        $query->where(function ($query) use ($search_val) {
                            $query->where('users.first_name', 'LIKE', '%'.$search_val.'%')
                                ->orWhere('users.last_name', 'LIKE', '%'.$search_val.'%');
                        });
                    });
                }


                if ($fieldname == 'manufacturer') {
                    $query->whereHas('model', function ($query) use ($search_val) {
                        $query->whereHas('manufacturer', function ($query) use ($search_val) {
                            $query->where(function ($query) use ($search_val) {
                                $query->where('manufacturers.name', 'LIKE', '%'.$search_val.'%');
                            });
                        });
                    });
                }

                if ($fieldname == 'category') {
                    $query->whereHas('model', function ($query) use ($search_val) {
                        $query->whereHas('category', function ($query) use ($search_val) {
                            $query->where(function ($query) use ($search_val) {
                                $query->where('categories.name', 'LIKE', '%'.$search_val.'%')
                                    ->orWhere('models.name', 'LIKE', '%'.$search_val.'%')
                                    ->orWhere('models.model_number', 'LIKE', '%'.$search_val.'%');
                            });
                        });
                    });
                }

                if ($fieldname == 'model') {
                    $query->where(function ($query) use ($search_val) {
                        $query->whereHas('model', function ($query) use ($search_val) {
                            $query->where('models.name', 'LIKE', '%'.$search_val.'%');
                        });
                    });
                }

                if ($fieldname == 'model_number') {
                    $query->where(function ($query) use ($search_val) {
                        $query->whereHas('model', function ($query) use ($search_val) {
                            $query->where('models.model_number', 'LIKE', '%'.$search_val.'%');
                        });
                    });
                }


                if ($fieldname == 'company') {
                    $query->where(function ($query) use ($search_val) {
                        $query->whereHas('company', function ($query) use ($search_val) {
                            $query->where('companies.name', 'LIKE', '%'.$search_val.'%');
                        });
                    });
                }

                if ($fieldname == 'supplier') {
                    $query->where(function ($query) use ($search_val) {
                        $query->whereHas('supplier', function ($query) use ($search_val) {
                            $query->where('suppliers.name', 'LIKE', '%'.$search_val.'%');
                        });
                    });
                }


            /**
             * THIS CLUNKY BIT IS VERY IMPORTANT
             *
             * Although inelegant, this section matters a lot when querying against fields that do not
             * exist on the movement table. There's probably a better way to do this moving forward, for
             * example using the Schema:: methods to determine whether or not a column actually exists,
             * or even just using the $searchableRelations variable earlier in this file.
             *
             * In short, this set of statements tells the query builder to ONLY query against an
             * actual field that's being passed if it doesn't meet known relational fields. This
             * allows us to query custom fields directly in the movementsv table
             * (regardless of their name) and *skip* any fields that we already know can only be
             * searched through relational searches that we do earlier in this method.
             *
             * For example, we do not store "location" as a field on the movements table, we store
             * that relationship through location_id on the movements table, therefore querying
             * movements.location would fail, as that field doesn't exist -- plus we're already searching
             * against those relationships earlier in this method.
             *
             * - snipe
             *
             */

            if (($fieldname!='category') && ($fieldname!='model_number') && ($fieldname!='rtd_location') && ($fieldname!='location') && ($fieldname!='supplier')
                && ($fieldname!='status_label') && ($fieldname!='assigned_to') && ($fieldname!='model') && ($fieldname!='company') && ($fieldname!='manufacturer')) {
                    $query->where('movements.'.$fieldname, 'LIKE', '%' . $search_val . '%');
            }


            }


        });

    }


    /**
    * Query builder scope to order on model
    *
    * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
    * @param  text                              $order       Order
    *
    * @return \Illuminate\Database\Query\Builder          Modified query builder
    */
    public function scopeOrderModels($query, $order)
    {
        return $query->join('models as movement_models', 'movements.model_id', '=', 'movement_models.id')->orderBy('movement_models.name', $order);
    }

    /**
    * Query builder scope to order on model number
    *
    * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
    * @param  text                              $order       Order
    *
    * @return \Illuminate\Database\Query\Builder          Modified query builder
    */
    public function scopeOrderModelNumber($query, $order)
    {
        return $query->leftJoin('models as model_number_sort', 'movements.model_id', '=', 'model_number_sort.id')->orderBy('model_number_sort.model_number', $order);
    }


    /**
    * Query builder scope to order on assigned user
    *
    * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
    * @param  text                              $order       Order
    *
    * @return \Illuminate\Database\Query\Builder          Modified query builder
    */
    public function scopeOrderAssigned($query, $order)
    {
        return $query->leftJoin('users as users_sort', 'movements.assigned_to', '=', 'users_sort.id')->select('movements.*')->orderBy('users_sort.first_name', $order)->orderBy('users_sort.last_name', $order);
    }

    /**
    * Query builder scope to order on status
    *
    * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
    * @param  text                              $order       Order
    *
    * @return \Illuminate\Database\Query\Builder          Modified query builder
    */
    public function scopeOrderStatus($query, $order)
    {
        return $query->join('status_labels as status_sort', 'movements.status_id', '=', 'status_sort.id')->orderBy('status_sort.name', $order);
    }

    /**
    * Query builder scope to order on company
    *
    * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
    * @param  text                              $order       Order
    *
    * @return \Illuminate\Database\Query\Builder          Modified query builder
    */
    public function scopeOrderCompany($query, $order)
    {
        return $query->leftJoin('companies as company_sort', 'movements.company_id', '=', 'company_sort.id')->orderBy('company_sort.name', $order);
    }


    /**
     * Query builder scope to return results of a category
     *
     * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
     * @param  text $order Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeInCategory($query, $category_id)
    {
        return $query->join('models as category_models', 'movements.model_id', '=', 'category_models.id')
            ->join('categories', 'category_models.category_id', '=', 'categories.id')->where('category_models.category_id', '=', $category_id);
    }

    /**
     * Query builder scope to return results of a manufacturer
     *
     * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
     * @param  text $order Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeByManufacturer($query, $manufacturer_id)
    {
        return $query->join('models', 'movements.model_id', '=', 'models.id')
            ->join('manufacturers', 'models.manufacturer_id', '=', 'manufacturers.id')->where('models.manufacturer_id', '=', $manufacturer_id);
    }



    /**
    * Query builder scope to order on category
    *
    * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
    * @param  text                              $order         Order
    *
    * @return \Illuminate\Database\Query\Builder          Modified query builder
    */
    public function scopeOrderCategory($query, $order)
    {
        return $query->join('models as order_model_category', 'movements.model_id', '=', 'order_model_category.id')
            ->join('categories as category_order', 'order_model_category.category_id', '=', 'category_order.id')
            ->orderBy('category_order.name', $order);
    }


    /**
     * Query builder scope to order on manufacturer
     *
     * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
     * @param  text                              $order         Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderManufacturer($query, $order)
    {
        return $query->join('models as order_movement_model', 'movements.model_id', '=', 'order_movement_model.id')
            ->leftjoin('manufacturers as manufacturer_order', 'order_movement_model.manufacturer_id', '=', 'manufacturer_order.id')
            ->orderBy('manufacturer_order.name', $order);
    }

   /**
    * Query builder scope to order on location
    *
    * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
    * @param  text                              $order       Order
    *
    * @return \Illuminate\Database\Query\Builder          Modified query builder
    */
    public function scopeOrderLocation($query, $order)
    {
        return $query->leftJoin('locations as movement_locations', 'movement_locations.id', '=', 'movements.location_id')->orderBy('movement_locations.name', $order);
    }

    /**
     * Query builder scope to order on default
     * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
     * @param  text                              $order       Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderRtdLocation($query, $order)
    {
        return $query->leftJoin('locations as rtd_movement_locations', 'rtd_movement_locations.id', '=', 'movements.rtd_location_id')->orderBy('rtd_movement_locations.name', $order);
    }


    /**
     * Query builder scope to order on supplier name
     *
     * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
     * @param  text                              $order       Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderSupplier($query, $order)
    {
        return $query->leftJoin('suppliers as suppliers_movements', 'movements.supplier_id', '=', 'suppliers_movements.id')->orderBy('suppliers_movements.name', $order);
    }

    /**
     * Query builder scope to search on location ID
     *
     * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
     * @param  text                              $search      Search term
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeByLocationId($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->whereHas('location', function ($query) use ($search) {
                $query->where('locations.id', '=', $search);
            });
        });

    }


    /**
     * Query builder scope to search on depreciation name
     * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
     * @param  text                              $search      Search term
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeByDepreciationId($query, $search)
    {
        return $query->join('models', 'movements.model_id', '=', 'models.id')
            ->join('depreciations', 'models.depreciation_id', '=', 'depreciations.id')->where('models.depreciation_id', '=', $search);

    }


}

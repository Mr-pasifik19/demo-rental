<?php

namespace App\Http\Controllers\Api;

use App\Events\CheckoutableCheckedIn;
use Illuminate\Support\Facades\Gate;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\MovementCheckoutRequest;
use App\Http\Transformers\MovementsTransformer;
use App\Http\Transformers\DepreciationReportTransformer;
use App\Http\Transformers\LicensesTransformer;
use App\Http\Transformers\SelectlistTransformer;
use App\Models\Actionlog;
use App\Models\Movement;
use App\Models\MovementModel;
use App\Models\Company;
use App\Models\CustomField;
use App\Models\License;
use App\Models\Location;
use App\Models\Setting;
use App\Models\User;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use App\Http\Requests\ImageUploadRequest;
use Input;
use Paginator;
use Slack;
use Str;
use TCPDF;
use Validator;
use Route;

/**
 * This class controls all actions related to Movements for
 * the Snipe-IT Movement Management application.
 *
 * @version    v1.0
 * @author [A. Gianotto] [<snipe@snipe.net>]
 */
class MovementsController extends Controller
{
    /**
     * Returns JSON listing of all Movements
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $movementId
     * @since [v4.0]
     * @return JsonResponse
     */
    public function index(Request $request, $audit = null) 
    {

        $filter_non_deprecable_movements = false;

        /**
         * This looks MAD janky (and it is), but the movementsController@index does a LOT of heavy lifting throughout the 
         * app. This bit here just makes sure that someone without permission to view movements doesn't 
         * end up with priv escalations because they asked for a different endpoint. 
         * 
         * Since we never gave the specification for which transformer to use before, it should default 
         * gracefully to just use the movementTransformer by default, which shouldn't break anything. 
         * 
         * It was either this mess, or repeating ALL of the searching and sorting and filtering code, 
         * which would have been far worse of a mess. *sad face*  - snipe (Sept 1, 2021)
         */
        if (Route::currentRouteName()=='api.depreciation-report.index') {
            $filter_non_deprecable_movements = true;
            $transformer = 'App\Http\Transformers\DepreciationReportTransformer';
            $this->authorize('reports.view');
        } else {
            $transformer = 'App\Http\Transformers\MovementsTransformer';
            $this->authorize('index', Movement::class);
        }
        
       
        $settings = Setting::getSettings();

        $allowed_columns = [
            'id',
            'name',
            'movement_tag',
            'serial',
            'model_number',
            'last_checkout',
            'notes',
            'expected_checkin',
            'order_number',
            'image',
            'assigned_to',
            'created_at',
            'updated_at',
            'purchase_date',
            'purchase_cost',
            'last_audit_date',
            'next_audit_date',
            'warranty_months',
            'checkout_counter',
            'checkin_counter',
            'requests_counter',
            'byod',
            'movement_eol_date',
        ];

        $filter = [];

        if ($request->filled('filter')) {
            $filter = json_decode($request->input('filter'), true);
        }

        $all_custom_fields = CustomField::all(); //used as a 'cache' of custom fields throughout this page load
        foreach ($all_custom_fields as $field) {
            $allowed_columns[] = $field->db_column_name();
        }

        $movements = Movement::select('movements.*')
            ->with('location', 'movementstatus', 'company', 'defaultLoc','assignedTo',
                'model.category', 'model.manufacturer', 'model.fieldset','supplier'); //it might be tempting to add 'movementlog' here, but don't. It blows up update-heavy users.


        if ($filter_non_deprecable_movements) {
            $non_deprecable_models = MovementModel::select('id')->whereNotNull('depreciation_id')->get();
            $movements->InModelList($non_deprecable_models->toArray());
        }



        // These are used by the API to query against specific ID numbers.
        // They are also used by the individual searches on detail pages like
        // locations, etc.

        // Search custom fields by column name
        foreach ($all_custom_fields as $field) {
            if ($request->filled($field->db_column_name())) {
                $movements->where($field->db_column_name(), '=', $request->input($field->db_column_name()));
            }
        }

        if ((! is_null($filter)) && (count($filter)) > 0) {
            $movements->ByFilter($filter);
        } elseif ($request->filled('search')) {
            $movements->TextSearch($request->input('search'));
        }

        // This is used by the audit reporting routes
        if (Gate::allows('audit', Movement::class)) {
            switch ($audit) {
                case 'due':
                    $movements->DueOrOverdueForAudit($settings);
                    break;
                case 'overdue':
                    $movements->overdueForAudit($settings);
                    break;
            }
        }


        // This is used by the sidenav, mostly

        // We switched from using query scopes here because of a Laravel bug
        // related to fulltext searches on complex queries.
        // I am sad. :(
        switch ($request->input('status')) {
            case 'Deleted':
                $movements->onlyTrashed();
                break;
            case 'Pending':
                $movements->join('status_labels AS status_alias', function ($join) {
                    $join->on('status_alias.id', '=', 'movements.status_id')
                        ->where('status_alias.deployable', '=', 0)
                        ->where('status_alias.pending', '=', 1)
                        ->where('status_alias.archived', '=', 0);
                });
                break;
            case 'RTD':
                $movements->whereNull('movements.assigned_to')
                    ->join('status_labels AS status_alias', function ($join) {
                        $join->on('status_alias.id', '=', 'movements.status_id')
                            ->where('status_alias.deployable', '=', 1)
                            ->where('status_alias.pending', '=', 0)
                            ->where('status_alias.archived', '=', 0);
                    });
                break;
            case 'Undeployable':
                $movements->Undeployable();
                break;
            case 'Archived':
                $movements->join('status_labels AS status_alias', function ($join) {
                    $join->on('status_alias.id', '=', 'movements.status_id')
                        ->where('status_alias.deployable', '=', 0)
                        ->where('status_alias.pending', '=', 0)
                        ->where('status_alias.archived', '=', 1);
                });
                break;
            case 'Requestable':
                $movements->where('movements.requestable', '=', 1)
                    ->join('status_labels AS status_alias', function ($join) {
                        $join->on('status_alias.id', '=', 'movements.status_id')
                            ->where('status_alias.deployable', '=', 1)
                            ->where('status_alias.pending', '=', 0)
                            ->where('status_alias.archived', '=', 0);
                    });

                break;
            case 'Deployed':
                // more sad, horrible workarounds for laravel bugs when doing full text searches
                $movements->whereNotNull('movements.assigned_to');
                break;
            case 'byod':
                // This is kind of redundant, since we already check for byod=1 above, but this keeps the
                // sidebar nav links a little less chaotic
                $movements->where('movements.byod', '=', '1');
                break;
            default:

                if ((! $request->filled('status_id')) && ($settings->show_archived_in_list != '1')) {
                    // terrible workaround for complex-query Laravel bug in fulltext
                    $movements->join('status_labels AS status_alias', function ($join) {
                        $join->on('status_alias.id', '=', 'movements.status_id')
                            ->where('status_alias.archived', '=', 0);
                    });

                    // If there is a status ID, don't take show_archived_in_list into consideration
                } else {
                    $movements->join('status_labels AS status_alias', function ($join) {
                        $join->on('status_alias.id', '=', 'movements.status_id');
                    });
                }

        }


        // Leave these under the TextSearch scope, else the fuzziness will override the specific ID (status ID, etc) requested
        if ($request->filled('status_id')) {
            $movements->where('movements.status_id', '=', $request->input('status_id'));
        }

        if ($request->filled('movement_tag')) {
            $movements->where('movements.movement_tag', '=', $request->input('movement_tag'));
        }

        if ($request->filled('serial')) {
            $movements->where('movements.serial', '=', $request->input('serial'));
        }

        if ($request->input('requestable') == 'true') {
            $movements->where('movements.requestable', '=', '1');
        }

        if ($request->filled('model_id')) {
            $movements->InModelList([$request->input('model_id')]);
        }

        if ($request->filled('category_id')) {
            $movements->InCategory($request->input('category_id'));
        }

        if ($request->filled('location_id')) {
            $movements->where('movements.location_id', '=', $request->input('location_id'));
        }

        if ($request->filled('rtd_location_id')) {
            $movements->where('movements.rtd_location_id', '=', $request->input('rtd_location_id'));
        }

        if ($request->filled('supplier_id')) {
            $movements->where('movements.supplier_id', '=', $request->input('supplier_id'));
        }

        if ($request->filled('movement_eol_date')) {
            $movements->where('movements.movement_eol_date', '=', $request->input('movement_eol_date'));
        }

        if (($request->filled('assigned_to')) && ($request->filled('assigned_type'))) {
            $movements->where('movements.assigned_to', '=', $request->input('assigned_to'))
                ->where('movements.assigned_type', '=', $request->input('assigned_type'));
        }

        if ($request->filled('company_id')) {
            $movements->where('movements.company_id', '=', $request->input('company_id'));
        }

        if ($request->filled('manufacturer_id')) {
            $movements->ByManufacturer($request->input('manufacturer_id'));
        }

        if ($request->filled('depreciation_id')) {
            $movements->ByDepreciationId($request->input('depreciation_id'));
        }

        if ($request->filled('byod')) {
            $movements->where('movements.byod', '=', $request->input('byod'));
        }

        if ($request->filled('order_number')) {
            $movements->where('movements.order_number', '=', $request->get('order_number'));
        }

        // This is kinda gross, but we need to do this because the Bootstrap Tables
        // API passes custom field ordering as custom_fields.fieldname, and we have to strip
        // that out to let the default sorter below order them correctly on the movements table.
        $sort_override = str_replace('custom_fields.', '', $request->input('sort'));

        // This handles all of the pivot sorting (versus the movements.* fields
        // in the allowed_columns array)
        $column_sort = in_array($sort_override, $allowed_columns) ? $sort_override : 'movements.created_at';

        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        
        switch ($sort_override) {
            case 'model':
                $movements->OrderModels($order);
                break;
            case 'model_number':
                $movements->OrderModelNumber($order);
                break;
            case 'category':
                $movements->OrderCategory($order);
                break;
            case 'manufacturer':
                $movements->OrderManufacturer($order);
                break;
            case 'company':
                $movements->OrderCompany($order);
                break;
            case 'location':
                $movements->OrderLocation($order);
            case 'rtd_location':
                $movements->OrderRtdLocation($order);
                break;
            case 'status_label':
                $movements->OrderStatus($order);
                break;
            case 'supplier':
                $movements->OrderSupplier($order);
                break;
            case 'assigned_to':
                $movements->OrderAssigned($order);
                break;
            default:
                $movements->orderBy($column_sort, $order);
                break;
        }


        // Make sure the offset and limit are actually integers and do not exceed system limits
        $offset = ($request->input('offset') > $movements->count()) ? $movements->count() : abs($request->input('offset'));
        $limit = app('api_limit_value');

        $total = $movements->count();
        $movements = $movements->skip($offset)->take($limit)->get();
        

        /**
         * Include additional associated relationships
         */  
        if ($request->input('components')) {
            $movements->loadMissing(['components' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }]);
        }



        /**
         * Here we're just determining which Transformer (via $transformer) to use based on the 
         * variables we set earlier on in this method - we default to movementsTransformer.
         */
        return (new $transformer)->transformMovements($movements, $total, $request);
    }


    /**
     * Returns JSON with information about an movement (by tag) for detail view.
     *
     * @param string $tag
     * @since [v4.2.1]
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @return \Illuminate\Http\JsonResponse
     */
    public function showByTag(Request $request, $tag)
    {
        $this->authorize('index', Movement::class);
        $movements = Movement::where('movement_tag', $tag)->with('movementstatus')->with('assignedTo');

        // Check if they've passed ?deleted=true
        if ($request->input('deleted', 'false') == 'true') {
            $movements = $movements->withTrashed();
        }

        if (($movements = $movements->get()) && ($movements->count()) > 0) {

            // If there is exactly one result and the deleted parameter is not passed, we should pull the first (and only)
            // movement from the returned collection, since transformmovement() expects an movement object, NOT a collection
            if (($movements->count() == 1) && ($request->input('deleted') != 'true')) {
                return (new MovementsTransformer)->transformMovement($movements->first());

                // If there is more than one result OR if the endpoint is requesting deleted items (even if there is only one
                // match, return the normal collection transformed.
            } else {
                return (new MovementsTransformer)->transformMovements($movements, $movements->count());
            }

        }

        // If there are 0 results, return the "no such movement" response
        return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/movement/message.does_not_exist')), 200);

    }

    /**
     * Returns JSON with information about an movement (by serial) for detail view.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param string $serial
     * @since [v4.2.1]
     * @return \Illuminate\Http\JsonResponse
     */
    public function showBySerial(Request $request, $serial)
    {
        $this->authorize('index', Movement::class);
        $movements = Movement::where('serial', $serial)->with('movementstatus')->with('assignedTo');

        // Check if they've passed ?deleted=true
        if ($request->input('deleted', 'false') == 'true') {
            $movements = $movements->withTrashed();
        }
        
        if (($movements = $movements->get()) && ($movements->count()) > 0) {
             return (new MovementsTransformer)->transformMovements($movements, $movements->count());
        }

        // If there are 0 results, return the "no such movement" response
        return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/movement/message.does_not_exist')), 200);

    }

    /**
     * Returns JSON with information about an movement for detail view.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $movementId
     * @since [v4.0]
     * @return JsonResponse
     */
    public function show(Request $request, $id)
    {
        if ($movement = Movement::with('movementstatus')->with('assignedTo')->withTrashed()
            ->withCount('checkins as checkins_count', 'checkouts as checkouts_count', 'userRequests as user_requests_count')->findOrFail($id)) {
            $this->authorize('view', $movement);

            return (new MovementsTransformer)->transformMovement($movement, $request->input('components') );
        }


    }

    public function licenses(Request $request, $id)
    {
        $this->authorize('view', Movement::class);
        $this->authorize('view', License::class);
        $movement = Movement::where('id', $id)->withTrashed()->firstorfail();
        $licenses = $movement->licenses()->get();

        return (new LicensesTransformer())->transformLicenses($licenses, $licenses->count());
     }


    /**
     * Gets a paginated collection for the select2 menus
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0.16]
     * @see \App\Http\Transformers\SelectlistTransformer
     *
     */
    public function selectlist(Request $request)
    {

        $movements = Movement::select([
            'movements.id',
            'movements.name',
            'movements.movement_tag',
            'movements.model_id',
            'movements.assigned_to',
            'movements.assigned_type',
            'movements.status_id',
            ])->with('model', 'movementstatus', 'assignedTo')->NotArchived();

        if ($request->filled('movementStatusType') && $request->input('movementStatusType') === 'RTD') {
            $movements = $movements->RTD();
        }

        if ($request->filled('search')) {
            $movements = $movements->AssignedSearch($request->input('search'));
        }


        $movements = $movements->paginate(50);

        // Loop through and set some custom properties for the transformer to use.
        // This lets us have more flexibility in special cases like movements, where
        // they may not have a ->name value but we want to display something anyway
        foreach ($movements as $movement) {


            $movement->use_text = $movement->present()->fullName;

            if (($movement->checkedOutToUser()) && ($movement->assigned)) {
                $movement->use_text .= ' → '.$movement->assigned->getFullNameAttribute();
            }


            if ($movement->movementstatus->getStatuslabelType() == 'pending') {
                $movement->use_text .= '('.$movement->movementstatus->getStatuslabelType().')';
            }

            $movement->use_image = ($movement->getImageUrl()) ? $movement->getImageUrl() : null;
        }

        return (new SelectlistTransformer)->transformSelectlist($movements);
    }


    /**
     * Accepts a POST request to create a new movement
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param \App\Http\Requests\ImageUploadRequest $request
     * @since [v4.0]
     * @return JsonResponse
     */
    public function store(ImageUploadRequest $request)
    {
        $this->authorize('create', Movement::class);

        $movement = new Movement();
        $movement->model()->associate(MovementModel::find((int) $request->get('model_id')));

        $movement->name                    = $request->get('name');
        $movement->serial                  = $request->get('serial');
        $movement->company_id              = Company::getIdForCurrentUser($request->get('company_id'));
        $movement->model_id                = $request->get('model_id');
        $movement->order_number            = $request->get('order_number');
        $movement->notes                   = $request->get('notes');
        $movement->movement_tag               = $request->get('movement_tag', Movement::autoincrement_movement());
        $movement->user_id                 = Auth::id();
        $movement->archived                = '0';
        $movement->physical                = '1';
        $movement->depreciate              = '0';
        $movement->status_id               = $request->get('status_id', 0);
        $movement->warranty_months         = $request->get('warranty_months', null);
        $movement->purchase_cost           = $request->get('purchase_cost');
        $movement->movement_eol_date          = $request->get('movement_eol_date', $movement->present()->eol_date());
        $movement->purchase_date           = $request->get('purchase_date', null);
        $movement->assigned_to             = $request->get('assigned_to', null);
        $movement->supplier_id             = $request->get('supplier_id');
        $movement->requestable             = $request->get('requestable', 0);
        $movement->rtd_location_id         = $request->get('rtd_location_id', null);
        $movement->location_id             = $request->get('rtd_location_id', null);


        /**
        * this is here just legacy reasons. Api\movementController
        * used image_source  once to allow encoded image uploads.
        */
        if ($request->has('image_source')) {
            $request->offsetSet('image', $request->offsetGet('image_source'));
        }     

        $movement = $request->handleImages($movement);

        // Update custom fields in the database.
        // Validation for these fields is handled through the movementRequest form request
        $model = MovementModel::find($request->get('model_id'));

        if (($model) && ($model->fieldset)) {
            foreach ($model->fieldset->fields as $field) {

                // Set the field value based on what was sent in the request
                $field_val = $request->input($field->db_column, null);

                // If input value is null, use custom field's default value
                if ($field_val == null) {
                    \Log::debug('Field value for '.$field->db_column.' is null');
                    $field_val = $field->defaultValue($request->get('model_id'));
                    \Log::debug('Use the default fieldset value of '.$field->defaultValue($request->get('model_id')));
                }

                // if the field is set to encrypted, make sure we encrypt the value
                if ($field->field_encrypted == '1') {
                    \Log::debug('This model field is encrypted in this fieldset.');

                    if (Gate::allows('admin')) {

                        // If input value is null, use custom field's default value
                        if (($field_val == null) && ($request->has('model_id') != '')) {
                            $field_val = \Crypt::encrypt($field->defaultValue($request->get('model_id')));
                        } else {
                            $field_val = \Crypt::encrypt($request->input($field->db_column));
                        }
                    }
                }


                $movement->{$field->db_column} = $field_val;
            }
        }

        if ($movement->save()) {
            if ($request->get('assigned_user')) {
                $target = User::find(request('assigned_user'));
            } elseif ($request->get('assigned_movement')) {
                $target = Movement::find(request('assigned_movement'));
            } elseif ($request->get('assigned_location')) {
                $target = Location::find(request('assigned_location'));
            }
            if (isset($target)) {
                $movement->checkOut($target, Auth::user(), date('Y-m-d H:i:s'), '', 'Checked out on movement creation', e($request->get('name')));
            }

            if ($movement->image) {
                $movement->image = $movement->getImageUrl();
            }

            return response()->json(Helper::formatStandardApiResponse('success', $movement, trans('admin/movement/message.create.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $movement->getErrors()), 200);
    }


    /**
     * Accepts a POST request to update an movement
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param \App\Http\Requests\ImageUploadRequest $request
     * @since [v4.0]
     * @return JsonResponse
     */
    public function update(ImageUploadRequest $request, $id)
    {
        $this->authorize('update', Movement::class);

        if ($movement = Movement::find($id)) {
            $movement->fill($request->all());

            ($request->filled('model_id')) ?
                $movement->model()->associate(MovementModel::find($request->get('model_id'))) : null;
            ($request->filled('rtd_location_id')) ?
                $movement->location_id = $request->get('rtd_location_id') : '';
            ($request->filled('company_id')) ?
                $movement->company_id = Company::getIdForCurrentUser($request->get('company_id')) : '';

            ($request->filled('rtd_location_id')) ?
                $movement->location_id = $request->get('rtd_location_id') : null;

            /**
            * this is here just legacy reasons. Api\movementController
            * used image_source  once to allow encoded image uploads.
            */
            if ($request->has('image_source')) {
                $request->offsetSet('image', $request->offsetGet('image_source'));
            }     

            $movement = $request->handleImages($movement); 
            
            // Update custom fields
            if (($model = MovementModel::find($movement->model_id)) && (isset($model->fieldset))) {
                foreach ($model->fieldset->fields as $field) {
                    if ($request->has($field->db_column)) {
                        if ($field->field_encrypted == '1') {
                            if (Gate::allows('admin')) {
                                $movement->{$field->db_column} = \Crypt::encrypt($request->input($field->db_column));
                            }
                        } else {
                            $movement->{$field->db_column} = $request->input($field->db_column);
                        }
                    }
                }
            }


            if ($movement->save()) {
                if (($request->filled('assigned_user')) && ($target = User::find($request->get('assigned_user')))) {
                        $location = $target->location_id;
                } elseif (($request->filled('assigned_movement')) && ($target = Movement::find($request->get('assigned_movement')))) {
                    $location = $target->location_id;

                    Movement::where('assigned_type', \App\Models\Movement::class)->where('assigned_to', $id)
                        ->update(['location_id' => $target->location_id]);
                } elseif (($request->filled('assigned_location')) && ($target = Location::find($request->get('assigned_location')))) {
                    $location = $target->id;
                }

                if (isset($target)) {
                    $movement->checkOut($target, Auth::user(), date('Y-m-d H:i:s'), '', 'Checked out on movement update', e($request->get('name')), $location);
                }

                if ($movement->image) {
                    $movement->image = $movement->getImageUrl();
                }

                return response()->json(Helper::formatStandardApiResponse('success', $movement, trans('admin/movement/message.update.success')));
            }

            return response()->json(Helper::formatStandardApiResponse('error', null, $movement->getErrors()), 200);
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/movement/message.does_not_exist')), 200);
    }


    /**
     * Delete a given movement (mark as deleted).
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $movementId
     * @since [v4.0]
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $this->authorize('delete', Movement::class);

        if ($movement = Movement::find($id)) {
            $this->authorize('delete', $movement);

            DB::table('movements')
                ->where('id', $movement->id)
                ->update(['assigned_to' => null]);

            $movement->delete();

            return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/movement/message.delete.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/movement/message.does_not_exist')), 200);
    }

    

    /**
     * Restore a soft-deleted movement.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $movementId
     * @since [v5.1.18]
     * @return JsonResponse
     */
    public function restore(Request $request, $movementId = null)
    {
        // Get movement information
        $movement = Movement::withTrashed()->find($movementId);
        $this->authorize('delete', $movement);

        if (isset($movement->id)) {

            if ($movement->deleted_at=='') {
               $message = 'movement was not deleted. No data was changed.';

            } else {

                $message = trans('admin/movement/message.restore.success');
                // Restore the movement
                Movement::withTrashed()->where('id', $movementId)->restore();

                $logaction = new Actionlog();
                $logaction->item_type = Movement::class;
                $logaction->item_id = $movement->id;
                $logaction->created_at =  date("Y-m-d H:i:s");
                $logaction->user_id = Auth::user()->id;
                $logaction->logaction('restored');
            }

            return response()->json(Helper::formatStandardApiResponse('success', (new MovementsTransformer)->transformMovement($movement, $request), $message));
        

        }
        return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/movement/message.does_not_exist')), 200);
    }

    /**
     * Checkout an movement by its tag.
     *
     * @author [N. Butler]
     * @param string $tag
     * @since [v6.0.5]
     * @return JsonResponse
     */
    public function checkoutByTag(MovementCheckoutRequest $request, $tag)
    {
        if ($movement = Movement::where('movement_tag', $tag)->first()) {
            return $this->checkout($request, $movement->id);
        }
        return response()->json(Helper::formatStandardApiResponse('error', null, 'Movement not found'), 200);
    }

    /**
     * Checkout an movement
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $movementId
     * @since [v4.0]
     * @return JsonResponse
     */
    public function checkout(MovementCheckoutRequest $request, $movement_id)
    {
        $this->authorize('checkout', Movement::class);
        $movement = Movement::findOrFail($movement_id);

        if (! $movement->availableForCheckout()) {
            return response()->json(Helper::formatStandardApiResponse('error', ['movement'=> e($movement->movement_tag)], trans('admin/movement/message.checkout.not_available')));
        }

        $this->authorize('checkout', $movement);

        $error_payload = [];
        $error_payload['movement'] = [
            'id' => $movement->id,
            'movement_tag' => $movement->movement_tag,
        ];


        // This item is checked out to a location
        if (request('checkout_to_type') == 'location') {
            $target = Location::find(request('assigned_location'));
            $movement->location_id = ($target) ? $target->id : '';
            $error_payload['target_id'] = $request->input('assigned_location');
            $error_payload['target_type'] = 'location';

        } elseif (request('checkout_to_type') == 'movement') {
            $target = Movement::where('id', '!=', $movement_id)->find(request('assigned_movement'));
            // Override with the movement's location_id if it has one
            $movement->location_id = (($target) && (isset($target->location_id))) ? $target->location_id : '';
            $error_payload['target_id'] = $request->input('assigned_movement');
            $error_payload['target_type'] = 'movement';

        } elseif (request('checkout_to_type') == 'user') {
            // Fetch the target and set the movement's new location_id
            $target = User::find(request('assigned_user'));
            $movement->location_id = (($target) && (isset($target->location_id))) ? $target->location_id : '';
            $error_payload['target_id'] = $request->input('assigned_user');
            $error_payload['target_type'] = 'user';
        }

        if ($request->filled('status_id')) {
            $movement->status_id = $request->get('status_id');
        }


        if (! isset($target)) {
            return response()->json(Helper::formatStandardApiResponse('error', $error_payload, 'Checkout target for movement '.e($movement->movement_tag).' is invalid - '.$error_payload['target_type'].' does not exist.'));
        }



        $checkout_at = request('checkout_at', date('Y-m-d H:i:s'));
        $expected_checkin = request('expected_checkin', null);
        $note = request('note', null);
        // Using `->has` preserves the movement name if the name parameter was not included in request.
        $movement_name = request()->has('name') ? request('name') : $movement->name;

        // Set the location ID to the RTD location id if there is one
        // Wait, why are we doing this? This overrides the stuff we set further up, which makes no sense.
        // TODO: Follow up here. WTF. Commented out for now. 


//        if ((isset($target->rtd_location_id)) && ($movement->rtd_location_id!='')) {
//            $movement->location_id = $target->rtd_location_id;
//        }



        if ($movement->checkOut($target, Auth::user(), $checkout_at, $expected_checkin, $note, $movement_name, $movement->location_id)) {
            return response()->json(Helper::formatStandardApiResponse('success', ['movement'=> e($movement->movement_tag)], trans('admin/movement/message.checkout.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', ['movement'=> e($movement->movement_tag)], trans('admin/movement/message.checkout.error')));
    }


    /**
     * Checkin an movement
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $movementId
     * @since [v4.0]
     * @return JsonResponse
     */
    public function checkin(Request $request, $movement_id)
    {
        $this->authorize('checkin', Movement::class);
        $movement = Movement::findOrFail($movement_id);
        $this->authorize('checkin', $movement);


        $target = $movement->assignedTo;
        if (is_null($target)) {
            return response()->json(Helper::formatStandardApiResponse('error', ['movement'=> e($movement->movement_tag)], trans('admin/movement/message.checkin.already_checked_in')));
        }

        $movement->expected_checkin = null;
        $movement->last_checkout = null;
        $movement->assigned_to = null;
        $movement->assignedTo()->disassociate($movement);
        $movement->accepted = null;

        if ($request->has('name')) {
            $movement->name = $request->input('name');
        }

        $movement->location_id = $movement->rtd_location_id;

        if ($request->filled('location_id')) {
            $movement->location_id = $request->input('location_id');
        }

        if ($request->has('status_id')) {
            $movement->status_id = $request->input('status_id');
        }
        
        $checkin_at = $request->filled('checkin_at') ? $request->input('checkin_at').' '. date('H:i:s') : date('Y-m-d H:i:s');


        if ($movement->save()) {
            event(new CheckoutableCheckedIn($movement, $target, Auth::user(), $request->input('note'), $checkin_at));

            return response()->json(Helper::formatStandardApiResponse('success', ['movement'=> e($movement->movement_tag)], trans('admin/movement/message.checkin.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', ['movement'=> e($movement->movement_tag)], trans('admin/movement/message.checkin.error')));
    }

    /**
     * Checkin an movement by movement tag
     *
     * @author [A. Janes] [<ajanes@adagiohealth.org>]
     * @since [v6.0]
     * @return JsonResponse
     */
    public function checkinByTag(Request $request, $tag = null)
    {
        $this->authorize('checkin', Movement::class);
        if(null == $tag && null !== ($request->input('movement_tag'))) {
            $tag = $request->input('movement_tag');
        }
        $movement = Movement::where('movement_tag', $tag)->first();

        if ($movement) {
            return $this->checkin($request, $movement->id);
        }

        return response()->json(Helper::formatStandardApiResponse('error', [
            'movement'=> e($tag)
        ], 'Movement with tag '.e($tag).' not found'));
    }


    /**
     * Mark an movement as audited
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $id
     * @since [v4.0]
     * @return JsonResponse
     */
    public function audit(Request $request)

    {
        $this->authorize('audit', Movement::class);
        $rules = [
            'movement_tag' => 'required',
            'location_id' => 'exists:locations,id|nullable|numeric',
            'next_audit_date' => 'date|nullable',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(Helper::formatStandardApiResponse('error', null, $validator->errors()->all()));
        }

        $settings = Setting::getSettings();
        $dt = Carbon::now()->addMonths($settings->audit_interval)->toDateString();

        $movement = Movement::where('movement_tag', '=', $request->input('movement_tag'))->first();


        if ($movement) {
            // We don't want to log this as a normal update, so let's bypass that
            $movement->unsetEventDispatcher();
            $movement->next_audit_date = $dt;

            if ($request->filled('next_audit_date')) {
                $movement->next_audit_date = $request->input('next_audit_date');
            }

            // Check to see if they checked the box to update the physical location,
            // not just note it in the audit notes
            if ($request->input('update_location') == '1') {
                $movement->location_id = $request->input('location_id');
            }

            $movement->last_audit_date = date('Y-m-d H:i:s');

            if ($movement->save()) {
                $log = $movement->logAudit(request('note'), request('location_id'));

                return response()->json(Helper::formatStandardApiResponse('success', [
                    'movement_tag'=> e($movement->movement_tag),
                    'note'=> e($request->input('note')),
                    'next_audit_date' => Helper::getFormattedDateObject($movement->next_audit_date),
                ], trans('admin/movement/message.audit.success')));
            }
        }

        return response()->json(Helper::formatStandardApiResponse('error', ['movement_tag'=> e($request->input('movement_tag'))], 'Movement with tag '.e($request->input('movement_tag')).' not found'));
    }



    /**
     * Returns JSON listing of all requestable movements
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @return JsonResponse
     */
    public function requestable(Request $request)
    {
        $this->authorize('viewRequestable', Movement::class);

        $movements = Movement::select('movements.*')
            ->with('location', 'movementstatus', 'movementlog', 'company', 'defaultLoc','assignedTo',
                'model.category', 'model.manufacturer', 'model.fieldset', 'supplier')
            ->requestableMovements();

        $offset = request('offset', 0);
        $limit = $request->input('limit', 50);
        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        if ($request->filled('search')) {
            $movements->TextSearch($request->input('search'));
        }

        switch ($request->input('sort')) {
            case 'model':
                $movements->OrderModels($order);
                break;
            case 'model_number':
                $movements->OrderModelNumber($order);
                break;
            case 'category':
                $movements->OrderCategory($order);
                break;
            case 'manufacturer':
                $movements->OrderManufacturer($order);
                break;
            default:
                $movements->orderBy('movements.created_at', $order);
                break;
        }

        $total = $movements->count();
        $movements = $movements->skip($offset)->take($limit)->get();

        return (new MovementsTransformer)->transformRequestedMovements($movements, $total);
    }
}

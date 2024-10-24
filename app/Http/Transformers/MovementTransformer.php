<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\Movement;
use App\Models\Setting;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Collection;


class MovementsTransformer
{
    public function transformMovements(Collection $movements, $total)
    {
        $array = [];
        foreach ($movements as $movement) {
            $array[] = self::transformMovement($movement);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformMovement(Movement $movement)
    {
        // This uses the getSettings() method so we're pulling from the cache versus querying the settings on single movement
        $setting = Setting::getSettings();

        $array = [
            'id' => (int) $movement->id,
            'name' => e($movement->name),
            'movement_tag' => e($movement->movement_tag),
            'serial' => e($movement->serial),
            'model' => ($movement->model) ? [
                'id' => (int) $movement->model->id,
                'name' => e($movement->model->name),
            ] : null,
            'byod' => ($movement->byod ? true : false),

            'model_number' => (($movement->model) && ($movement->model->model_number)) ? e($movement->model->model_number) : null,
            'eol' => (($movement->model) && ($movement->model->eol != '')) ? $movement->model->eol : null,
            'movement_eol_date' => ($movement->movement_eol_date != '') ? Helper::getFormattedDateObject($movement->movement_eol_date, 'date') : null,
            'status_label' => ($movement->movementstatus) ? [
                'id' => (int) $movement->movementstatus->id,
                'name' => e($movement->movementstatus->name),
                'status_type' => e($movement->movementstatus->getStatuslabelType()),
                'status_meta' => e($movement->present()->statusMeta),
            ] : null,
            'category' => (($movement->model) && ($movement->model->category)) ? [
                'id' => (int) $movement->model->category->id,
                'name' => e($movement->model->category->name),
            ] : null,
            'manufacturer' => (($movement->model) && ($movement->model->manufacturer)) ? [
                'id' => (int) $movement->model->manufacturer->id,
                'name' => e($movement->model->manufacturer->name),
            ] : null,
            'supplier' => ($movement->supplier) ? [
                'id' => (int) $movement->supplier->id,
                'name' => e($movement->supplier->name),
            ] : null,
            'notes' => ($movement->notes) ? Helper::parseEscapedMarkedown($movement->notes) : null,
            'order_number' => ($movement->order_number) ? e($movement->order_number) : null,
            'company' => ($movement->company) ? [
                'id' => (int) $movement->company->id,
                'name' => e($movement->company->name),
            ] : null,
            'location' => ($movement->location) ? [
                'id' => (int) $movement->location->id,
                'name' => e($movement->location->name),
            ] : null,
            'rtd_location' => ($movement->defaultLoc) ? [
                'id' => (int) $movement->defaultLoc->id,
                'name' => e($movement->defaultLoc->name),
            ] : null,
            'image' => ($movement->getImageUrl()) ? $movement->getImageUrl() : null,
            'qr' => ($setting->qr_code == '1') ? config('app.url') . '/uploads/barcodes/qr-' . str_slug($movement->movement_tag) . '-' . str_slug($movement->id) . '.png' : null,
            'alt_barcode' => ($setting->alt_barcode_enabled == '1') ? config('app.url') . '/uploads/barcodes/' . str_slug($setting->alt_barcode) . '-' . str_slug($movement->movement_tag) . '.png' : null,
            'assigned_to' => $this->transformAssignedTo($movement),
            'warranty_months' => ($movement->warranty_months > 0) ? e($movement->warranty_months . ' ' . trans('admin/movement/form.months')) : null,
            'warranty_expires' => ($movement->warranty_months > 0) ? Helper::getFormattedDateObject($movement->warranty_expires, 'date') : null,
            'created_at' => Helper::getFormattedDateObject($movement->created_at, 'datetime'),
            'updated_at' => Helper::getFormattedDateObject($movement->updated_at, 'datetime'),
            'last_audit_date' => Helper::getFormattedDateObject($movement->last_audit_date, 'datetime'),
            'next_audit_date' => Helper::getFormattedDateObject($movement->next_audit_date, 'date'),
            'deleted_at' => Helper::getFormattedDateObject($movement->deleted_at, 'datetime'),
            'purchase_date' => Helper::getFormattedDateObject($movement->purchase_date, 'date'),
            'age' => $movement->purchase_date ? $movement->purchase_date->diffForHumans() : '',
            'last_checkout' => Helper::getFormattedDateObject($movement->last_checkout, 'datetime'),
            'expected_checkin' => Helper::getFormattedDateObject($movement->expected_checkin, 'date'),
            'purchase_cost' => Helper::formatCurrencyOutput($movement->purchase_cost),
            'checkin_counter' => (int) $movement->checkin_counter,
            'checkout_counter' => (int) $movement->checkout_counter,
            'requests_counter' => (int) $movement->requests_counter,
            'user_can_checkout' => (bool) $movement->availableForCheckout(),
        ];


        if (($movement->model) && ($movement->model->fieldset) && ($movement->model->fieldset->fields->count() > 0)) {
            $fields_array = [];

            foreach ($movement->model->fieldset->fields as $field) {
                if ($field->isFieldDecryptable($movement->{$field->db_column})) {
                    $decrypted = Helper::gracefulDecrypt($field, $movement->{$field->db_column});
                    $value = (Gate::allows('superadmin')) ? $decrypted : strtoupper(trans('admin/custom_fields/general.encrypted'));

                    if ($field->format == 'DATE') {
                        if (Gate::allows('superadmin')) {
                            $value = Helper::getFormattedDateObject($value, 'date', false);
                        } else {
                            $value = strtoupper(trans('admin/custom_fields/general.encrypted'));
                        }
                    }

                    $fields_array[$field->name] = [
                        'field' => e($field->db_column),
                        'value' => e($value),
                        'field_format' => $field->format,
                        'element' => $field->element,
                    ];
                } else {
                    $value = $movement->{$field->db_column};

                    if (($field->format == 'DATE') && (!is_null($value)) && ($value != '')) {
                        $value = Helper::getFormattedDateObject($value, 'date', false);
                    }

                    $fields_array[$field->name] = [
                        'field' => e($field->db_column),
                        'value' => e($value),
                        'field_format' => $field->format,
                        'element' => $field->element,
                    ];
                }

                $array['custom_fields'] = $fields_array;
            }
        } else {
            $array['custom_fields'] = new \stdClass; // HACK to force generation of empty object instead of empty list
        }

        $permissions_array['available_actions'] = [
            'checkout'      => ($movement->deleted_at == '' && Gate::allows('checkout', Movement::class)) ? true : false,
            'checkin'       => ($movement->deleted_at == '' && Gate::allows('checkin', Movement::class)) ? true : false,
            // 'clone'         => Gate::allows('create', movement::class) ? true : false,
            'restore'       => ($movement->deleted_at != '' && Gate::allows('create', Movement::class)) ? true : false,
            'update'        => ($movement->deleted_at == '' && Gate::allows('update', Movement::class)) ? true : false,
            // 'delete'        => ($movement->deleted_at=='' && $movement->assigned_to =='' && Gate::allows('delete', movement::class)) ? true : false,
        ];


        if (request('components') == 'true') {

            if ($movement->components) {
                $array['components'] = [];

                foreach ($movement->components as $component) {
                    $array['components'][] = [

                        'id' => $component->id,
                        'pivot_id' => $component->pivot->id,
                        'name' => e($component->name),
                        'qty' => $component->pivot->assigned_qty,
                        'price_cost' => $component->purchase_cost,
                        'purchase_total' => $component->purchase_cost * $component->pivot->assigned_qty,
                        'checkout_date' => Helper::getFormattedDateObject($component->pivot->created_at, 'datetime'),

                    ];
                }
            }
        }

        $array += $permissions_array;

        return $array;
    }

    public function transformMovementsDatatable($movements)
    {
        return (new DatatablesTransformer)->transformDatatables($movements);
    }

    public function transformAssignedTo($movement)
    {
        if ($movement->checkedOutToUser()) {
            return $movement->assigned ? [
                'id' => (int) $movement->assigned->id,
                'username' => e($movement->assigned->username),
                'name' => e($movement->assigned->getFullNameAttribute()),
                'first_name' => e($movement->assigned->first_name),
                'last_name' => ($movement->assigned->last_name) ? e($movement->assigned->last_name) : null,
                'email' => ($movement->assigned->email) ? e($movement->assigned->email) : null,
                'employee_number' => ($movement->assigned->employee_num) ? e($movement->assigned->employee_num) : null,
                'type' => 'user',
            ] : null;
        }

        return $movement->assigned ? [
            'id' => $movement->assigned->id,
            'name' => e($movement->assigned->display_name),
            'type' => $movement->assignedType()
        ] : null;
    }


    public function transformRequestedMovements(Collection $movements, $total)
    {
        $array = [];
        foreach ($movements as $movement) {
            $array[] = self::transformRequestedMovement($movement);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformRequestedMovement(Movement $movement)
    {
        $array = [
            'id' => (int) $movement->id,
            'name' => e($movement->name),
            'movement_tag' => e($movement->movement_tag),
            'serial' => e($movement->serial),
            'image' => ($movement->getImageUrl()) ? $movement->getImageUrl() : null,
            'model' => ($movement->model) ? e($movement->model->name) : null,
            'model_number' => (($movement->model) && ($movement->model->model_number)) ? e($movement->model->model_number) : null,
            'expected_checkin' => Helper::getFormattedDateObject($movement->expected_checkin, 'date'),
            'location' => ($movement->location) ? e($movement->location->name) : null,
            'status' => ($movement->movementstatus) ? $movement->present()->statusMeta : null,
            'assigned_to_self' => ($movement->assigned_to == \Auth::user()->id),
        ];

        $permissions_array['available_actions'] = [
            'cancel' => ($movement->isRequestedBy(\Auth::user())) ? true : false,
            'request' => ($movement->isRequestedBy(\Auth::user())) ? false : true,
        ];

        $array += $permissions_array;
        return $array;
    }
}

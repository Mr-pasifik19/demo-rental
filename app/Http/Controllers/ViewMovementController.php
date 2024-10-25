<?php

namespace App\Http\Controllers;

use App\Models\Actionlog;
use App\Models\Movement;
use App\Models\MovementModel;
use App\Models\Company;
use App\Models\Setting;
use App\Models\User;
use App\Models\CustomField;
use App\Notifications\RequestAssetCancelation;
use App\Notifications\RequestAssetNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * This controller handles all actions related to the ability for users
 * to view their own movements in the Snipe-IT movements Management application.
 *
 * @version    v1.0
 */
class ViewMovementController extends Controller
{
    /**
     * Redirect to the profile page.
     *
     * @return Redirect
     */
    public function getIndex()
    {
        $user = User::with(
            'movement',
            'movement.model',
            'movement.model.fieldset.fields',
            'consumables',
            'accessories',
            'licenses',
        )->find(Auth::user()->id);

        $field_array = array();

        // Loop through all the custom fields that are applied to any model the user has assigned
        foreach ($user->movement as $movement) {

            // Make sure the model has a custom fieldset before trying to loop through the associated fields
            if ($movement->model->fieldset) {

                foreach ($movement->model->fieldset->fields as $field) {
                    // check and make sure they're allowed to see the value of the custom field
                    if ($field->display_in_user_view == '1') {
                        $field_array[$field->db_column] = $field->name;
                    }
                    
                }
            }

        }

        // Since some models may re-use the same fieldsets/fields, let's make the array unique so we don't repeat columns
        array_unique($field_array);

        if (isset($user->id)) {
            return view('account/view-movement', compact('user', 'field_array' ))
                ->with('settings', Setting::getSettings());
        }

        // Redirect to the user management page
        return redirect()->route('users.index')
            ->with('error', trans('admin/users/message.user_not_found', $user->id));
    }

    /**
     * Returns view of requestable items for a user.
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getRequestableIndex()
    {
        $movement = Movement::with('model', 'defaultLoc', 'location', 'assignedTo', 'requests')->Movement()->RequestableMovement();
        $models = MovementModel::with('category', 'requests', 'movement')->RequestableModels()->get();

        return view('account/requestable-movements', compact('movements', 'models'));
    }

    public function getRequestItem(Request $request, $itemType, $itemId = null, $cancel_by_admin = false, $requestingUser = null)
    {
        $item = null;
        $fullItemType = 'App\\Models\\'.studly_case($itemType);

        if ($itemType == 'movement_model') {
            $itemType = 'model';
        }
        $item = call_user_func([$fullItemType, 'find'], $itemId);

        $user = Auth::user();

        $logaction = new Actionlog();
        $logaction->item_id = $data['movement_id'] = $item->id;
        $logaction->item_type = $fullItemType;
        $logaction->created_at = $data['requested_date'] = date('Y-m-d H:i:s');

        if ($user->location_id) {
            $logaction->location_id = $user->location_id;
        }
        $logaction->target_id = $data['user_id'] = Auth::user()->id;
        $logaction->target_type = User::class;

        $data['item_quantity'] = $request->has('request-quantity') ? e($request->input('request-quantity')) : 1;
        $data['requested_by'] = $user->present()->fullName();
        $data['item'] = $item;
        $data['item_type'] = $itemType;
        $data['target'] = Auth::user();

        if ($fullItemType == Movement::class) {
            $data['item_url'] = route('movement.show', $item->id);
        } else {
            $data['item_url'] = route("view/${itemType}", $item->id);
        }

        $settings = Setting::getSettings();

        if (($item_request = $item->isRequestedBy($user)) || $cancel_by_admin) {
            $item->cancelRequest($requestingUser);
            $data['item_quantity'] = ($item_request) ? $item_request->qty : 1;
            $logaction->logaction('request_canceled');

            if (($settings->alert_email != '') && ($settings->alerts_enabled == '1') && (! config('app.lock_passwords'))) {
                $settings->notify(new RequestAssetCancelation($data));
            }

            return redirect()->back()->with('success')->with('success', trans('admin/movement/message.requests.canceled'));
        } else {
            $item->request();
            if (($settings->alert_email != '') && ($settings->alerts_enabled == '1') && (! config('app.lock_passwords'))) {
                $logaction->logaction('requested');
                $settings->notify(new RequestAssetNotification($data));
            }

            return redirect()->route('requestable-movements')->with('success')->with('success', trans('admin/movement/message.requests.success'));
        }
    }

    /**
     * Process a specific requested movement
     * @param null $movementId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getRequestMovement($movementId = null)
    {
        $user = Auth::user();

        // Check if the movement exists and is requestable
        if (is_null($movement = Movement::RequestableMovements()->find($movementId))) {
            return redirect()->route('requestable-movements')
                ->with('error', trans('admin/movement/message.does_not_exist_or_not_requestable'));
        }
        if (! Company::isCurrentUserHasAccess($movement)) {
            return redirect()->route('requestable-movements')
                ->with('error', trans('general.insufficient_permissions'));
        }

        $data['item'] = $movement;
        $data['target'] = Auth::user();
        $data['item_quantity'] = 1;
        $settings = Setting::getSettings();

        $logaction = new Actionlog();
        $logaction->item_id = $data['movement_id'] = $movement->id;
        $logaction->item_type = $data['item_type'] = Movement::class;
        $logaction->created_at = $data['requested_date'] = date('Y-m-d H:i:s');

        if ($user->location_id) {
            $logaction->location_id = $user->location_id;
        }
        $logaction->target_id = $data['user_id'] = Auth::user()->id;
        $logaction->target_type = User::class;

        // If it's already requested, cancel the request.
        if ($movement->isRequestedBy(Auth::user())) {
            $movement->cancelRequest();
            $movement->decrement('requests_counter', 1);

            $logaction->logaction('request canceled');
            $settings->notify(new RequestAssetCancelation($data));

            return redirect()->route('requestable-movements')
                ->with('success')->with('success', trans('admin/movement/message.requests.canceled'));
        }

        $logaction->logaction('requested');
        $movement->request();
        $movement->increment('requests_counter', 1);
        $settings->notify(new RequestAssetNotification($data));

        return redirect()->route('requestable-movements')->with('success')->with('success', trans('admin/movement/message.requests.success'));
    }

    public function getRequestedMovements()
    {
        return view('account/requested');
    }
}

<?php

namespace App\Observers;

use App\Models\Actionlog;
use App\Models\Movement;
use App\Models\Setting;
use Auth;

class MovementObserver
{
    /**
     * Listen to the User created event.
     *
     * @param  Movement  $movement
     * @return void
     */
    public function updating(Movement $movement)
    {
        $attributes = $movement->getAttributes();
        $attributesOriginal = $movement->getRawOriginal();
        $same_checkout_counter = false;
        $same_checkin_counter = false;

        if (array_key_exists('checkout_counter', $attributes) && array_key_exists('checkout_counter', $attributesOriginal)){
            $same_checkout_counter = (($attributes['checkout_counter'] == $attributesOriginal['checkout_counter']));
        }

        if (array_key_exists('checkin_counter', $attributes)  && array_key_exists('checkin_counter', $attributesOriginal)){
            $same_checkin_counter = (($attributes['checkin_counter'] == $attributesOriginal['checkin_counter']));
        }

        // If the movement isn't being checked out or audited, log the update.
        // (Those other actions already create log entries.)
	if (($attributes['assigned_to'] == $attributesOriginal['assigned_to']) 
	    && ($same_checkout_counter) && ($same_checkin_counter)
            && ((isset( $attributes['next_audit_date']) ? $attributes['next_audit_date'] : null) == (isset($attributesOriginal['next_audit_date']) ? $attributesOriginal['next_audit_date']: null))
            && ($attributes['last_checkout'] == $attributesOriginal['last_checkout']))
        {
            $changed = [];

            foreach ($movement->getRawOriginal() as $key => $value) {
                if ($movement->getRawOriginal()[$key] != $movement->getAttributes()[$key]) {
                    $changed[$key]['old'] = $movement->getRawOriginal()[$key];
                    $changed[$key]['new'] = $movement->getAttributes()[$key];
                }
	    }

	    if (empty($changed)){
	        return;
	    }

            $logAction = new Actionlog();
            $logAction->item_type = Movement::class;
            $logAction->item_id = $movement->id;
            $logAction->created_at = date('Y-m-d H:i:s');
            $logAction->user_id = Auth::id();
            $logAction->log_meta = json_encode($changed);
            $logAction->logaction('update');
        }
    }

    /**
     * Listen to the movement created event, and increment
     * the next_auto_tag_base value in the settings table when i
     * a new movement is created.
     *
     * @param  Movement  $movement
     * @return void
     */
    public function created(Movement $movement)
    {
        if ($settings = Setting::getSettings()) {
            $settings->increment('next_auto_tag_base');
            $settings->save();
        }

        $logAction = new Actionlog();
        $logAction->item_type = Movement::class;
        $logAction->item_id = $movement->id;
        $logAction->created_at = date('Y-m-d H:i:s');
        $logAction->user_id = Auth::id();
        $logAction->logaction('create');
    }

    /**
     * Listen to the movement deleting event.
     *
     * @param  Movement  $movement
     * @return void
     */
    public function deleting(Movement $movement)
    {
        $logAction = new Actionlog();
        $logAction->item_type = Movement::class;
        $logAction->item_id = $movement->id;
        $logAction->created_at = date('Y-m-d H:i:s');
        $logAction->user_id = Auth::id();
        $logAction->logaction('delete');
    }
}

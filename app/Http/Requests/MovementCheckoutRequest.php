<?php

namespace App\Http\Requests;

class MovementCheckoutRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'assigned_user'         => 'required_without_all:assigned_movement,assigned_location',
            'assigned_movement'        => 'required_without_all:assigned_user,assigned_location',
            'assigned_location'     => 'required_without_all:assigned_user,assigned_movement',
            'status_id'             => 'exists:status_labels,id,deployable,1',
            'checkout_to_type'      => 'required|in:movement,location,user',
        ];

        return $rules;
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeliveryBoyRegisterRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'full_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20|unique:delivery_boys,phone_number',
            'password' => 'required|string|min:6|confirmed',
            'vehicle_type' => 'nullable|string|max:100',
            'max_active_orders' => 'nullable|integer|min:1|max:10'
        ];
    }
}
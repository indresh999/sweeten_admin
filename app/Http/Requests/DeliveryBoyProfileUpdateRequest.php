<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeliveryBoyProfileUpdateRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        $id = auth()->id();

        return [
            'full_name' => 'sometimes|string|max:255',
            'phone_number' => 'sometimes|string|unique:delivery_boys,phone_number,' . $id,
            'picture' => 'nullable|file|mimes:jpg,jpeg,png|max:10240',
            'vehicle_type' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'password' => 'sometimes|nullable|confirmed|min:6'
        ];
    }
}
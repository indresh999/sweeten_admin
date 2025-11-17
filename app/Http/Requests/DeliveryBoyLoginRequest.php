<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeliveryBoyLoginRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'phone_number' => 'required|string',
            'password' => 'required|string',
        ];
    }
}
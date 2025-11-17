<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeliveryBoyDocumentUploadRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'doc_type' => 'required|string|in:license,id_card,vehicle_registration,insurance,other',
            'file' => 'required|file|max:20480|mimes:jpg,jpeg,png,pdf'
        ];
    }
}
<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeliveryBoyDocumentUploadRequest;
use App\Models\DeliveryDocument;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class DeliveryBoyDocumentController extends Controller
{
    public function upload(DeliveryBoyDocumentUploadRequest $request)
    {
        $deliveryBoy = $request->user();

        $path = $request->file('file')->store("public/delivery_documents/{$deliveryBoy->id}");

        $doc = DeliveryDocument::create([
            'delivery_boy_id' => $deliveryBoy->id,
            'doc_type' => $request->doc_type,
            'file_path' => $path
        ]);

        return response()->json([
            'message' => 'Document uploaded. Awaiting approval.',
            'data' => $doc
        ]);
    }

    public function list(Request $request)
    {
        return $request->user()->documents()->get();
    }
}
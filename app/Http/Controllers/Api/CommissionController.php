<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\Item;
use App\Services\CommissionService;

class CommissionController extends Controller
{
    public function getCommission(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:items,id',
            'price'   => 'required|numeric|min:0',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $item       = Item::with(['category', 'subcategory'])->findOrFail($request->item_id);
        $commission = CommissionService::getCommissionDetails($item, (float) $request->price);
        $amount     = CommissionService::calculateCommission((float) $request->price, $commission);

        return response()->json([
            'status' => true,
            'data'   => [
                'commission_type'   => $commission['type'],
                'commission_value'  => $commission['value'],
                'commission_amount' => round($amount, 2),
                'final_price'       => round((float) $request->price + $amount, 2),
                'source'            => $commission['source'],
            ],
        ]);
    }
}

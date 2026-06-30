<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\Deal;
use App\Models\DealItem;

class DealController extends Controller
{
    public function activeDeals(): JsonResponse
    {
        $deals = Deal::with(['dealItems.item' => fn($q) => $q->with('variants')->where('status', 'active')])
            ->where('is_active', 1)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->orderByDesc('id')
            ->get()
            ->map(function ($deal) {
                $deal->time_left_seconds = max(0, now()->diffInSeconds($deal->ends_at, false));
                return $deal;
            });

        return response()->json(['status' => true, 'data' => $deals]);
    }

    public function getDeal(int $id): JsonResponse
    {
        $deal = Deal::with(['dealItems.item.variants'])
            ->where('id', $id)
            ->where('is_active', 1)
            ->firstOrFail();
        $deal->time_left_seconds = max(0, now()->diffInSeconds($deal->ends_at, false));
        return response()->json(['status' => true, 'data' => $deal]);
    }

    public function adminCreate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title'          => 'required|string|max:150',
            'subtitle'       => 'nullable|string|max:255',
            'banner_image'   => 'nullable|string',
            'deal_type'      => 'required|in:flash_sale,bundle,buy_x_get_y,free_delivery',
            'discount_type'  => 'required|in:percent,flat',
            'discount_value' => 'required|numeric|min:0',
            'starts_at'      => 'required|date',
            'ends_at'        => 'required|date|after:starts_at',
            'items'          => 'required|array|min:1',
            'items.*.item_id'=> 'required|exists:items,id',
            'items.*.deal_price' => 'nullable|numeric|min:0',
            'items.*.deal_discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.stock_limit' => 'nullable|integer|min:1',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $deal = Deal::create($validator->safe()->except('items'));
        foreach ($request->items as $itemData) {
            DealItem::create(array_merge($itemData, ['deal_id' => $deal->id]));
        }

        return response()->json(['status' => true, 'data' => $deal->load('dealItems')], 201);
    }

    public function adminUpdate(Request $request, int $id): JsonResponse
    {
        $deal = Deal::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'title'         => 'sometimes|string|max:150',
            'subtitle'      => 'nullable|string',
            'banner_image'  => 'nullable|string',
            'deal_type'     => 'sometimes|in:flash_sale,bundle,buy_x_get_y,free_delivery',
            'discount_type' => 'sometimes|in:percent,flat',
            'discount_value'=> 'sometimes|numeric|min:0',
            'starts_at'     => 'sometimes|date',
            'ends_at'       => 'sometimes|date',
            'is_active'     => 'nullable|boolean',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        $deal->update($validator->validated());
        return response()->json(['status' => true, 'data' => $deal]);
    }

    public function adminDelete(int $id): JsonResponse
    {
        Deal::findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'Deal deleted']);
    }
}

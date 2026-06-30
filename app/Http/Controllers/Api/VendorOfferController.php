<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ItemOffer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VendorOfferController extends Controller
{
    // ── List Offers ───────────────────────────────────────────────────────────
    // GET /shop/offers?status=1&type=percent&per_page=20
    public function index(Request $request): JsonResponse
    {
        $shop    = $request->user();
        $perPage = $request->integer('per_page', 20);

        $offers = ItemOffer::with(['item:id,item_name,price,offer_price', 'category:id,category_name'])
            ->where('shop_id', $shop->shop_id)
            ->when($request->filled('status'), fn($q) => $q->where('status', (bool) $request->status))
            ->when($request->filled('type'),   fn($q) => $q->where('offer_type', $request->type))
            ->when($request->filled('q'),      fn($q) => $q->where('title', 'like', '%' . $request->q . '%'))
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate($perPage);

        $offers->getCollection()->transform(fn($o) => $this->formatOffer($o));

        return response()->json(['status' => true, 'data' => $offers]);
    }

    // ── Active Offers ─────────────────────────────────────────────────────────
    // GET /shop/offers/active  — used by app to display banners / badges
    public function active(Request $request): JsonResponse
    {
        $offers = ItemOffer::with(['item:id,item_name', 'category:id,category_name'])
            ->where('shop_id', $request->user()->shop_id)
            ->active()
            ->orderBy('sort_order')
            ->get()
            ->map(fn($o) => $this->formatOffer($o));

        return response()->json(['status' => true, 'data' => $offers]);
    }

    // ── Create Offer ──────────────────────────────────────────────────────────
    // POST /shop/offers
    // Body (JSON or form):
    //   title, description, offer_type (percent|flat|bogo)
    //   discount_value, max_discount (for percent cap), min_order_value
    //   item_id? (null = whole shop), category_id?
    //   badge_label, valid_from, valid_until, status, sort_order
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->offerRules());

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        // BOGO doesn't use discount_value in the traditional sense
        if ($request->offer_type !== 'bogo' && !$request->filled('discount_value')) {
            return response()->json(['status' => false, 'message' => 'discount_value is required for percent/flat offers.'], 422);
        }

        $offer = ItemOffer::create(array_merge(
            $validator->validated(),
            ['shop_id' => $request->user()->shop_id]
        ));

        return response()->json([
            'status'  => true,
            'message' => 'Offer created.',
            'data'    => $this->formatOffer($offer->load(['item:id,item_name', 'category:id,category_name'])),
        ], 201);
    }

    // ── Get Single Offer ──────────────────────────────────────────────────────
    // GET /shop/offers/{id}
    public function show(Request $request, int $id): JsonResponse
    {
        $offer = ItemOffer::with(['item:id,item_name,price,offer_price', 'category:id,category_name'])
            ->where('shop_id', $request->user()->shop_id)
            ->findOrFail($id);

        return response()->json(['status' => true, 'data' => $this->formatOffer($offer)]);
    }

    // ── Update Offer ──────────────────────────────────────────────────────────
    // PUT /shop/offers/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $offer = ItemOffer::where('shop_id', $request->user()->shop_id)->findOrFail($id);

        $validator = Validator::make($request->all(), $this->offerRules('sometimes'));

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $offer->update($validator->validated());

        return response()->json([
            'status'  => true,
            'message' => 'Offer updated.',
            'data'    => $this->formatOffer($offer->fresh()->load(['item:id,item_name', 'category:id,category_name'])),
        ]);
    }

    // ── Delete Offer ──────────────────────────────────────────────────────────
    // DELETE /shop/offers/{id}
    public function destroy(Request $request, int $id): JsonResponse
    {
        ItemOffer::where('shop_id', $request->user()->shop_id)->findOrFail($id)->delete();

        return response()->json(['status' => true, 'message' => 'Offer deleted.']);
    }

    // ── Toggle Active ─────────────────────────────────────────────────────────
    // POST /shop/offers/{id}/toggle
    public function toggle(Request $request, int $id): JsonResponse
    {
        $offer         = ItemOffer::where('shop_id', $request->user()->shop_id)->findOrFail($id);
        $offer->status = !$offer->status;
        $offer->save();

        return response()->json(['status' => true, 'offer_status' => $offer->status, 'is_active_now' => $offer->isCurrentlyActive()]);
    }

    // ── Private Helpers ───────────────────────────────────────────────────────

    private function offerRules(string $presence = 'required'): array
    {
        return [
            'title'           => "{$presence}|string|max:150",
            'description'     => 'nullable|string|max:500',
            'offer_type'      => "{$presence}|in:percent,flat,bogo",
            'discount_value'  => 'nullable|numeric|min:0',
            'max_discount'    => 'nullable|numeric|min:0',
            'min_order_value' => 'nullable|numeric|min:0',
            'item_id'         => 'nullable|exists:items,id',
            'category_id'     => 'nullable|exists:item_categories,id',
            'badge_label'     => 'nullable|string|max:50',
            'valid_from'      => 'nullable|date',
            'valid_until'     => 'nullable|date|after_or_equal:valid_from',
            'status'          => 'nullable|boolean',
            'sort_order'      => 'nullable|integer|min:0',
        ];
    }

    private function formatOffer(ItemOffer $o): array
    {
        return [
            'id'              => $o->id,
            'title'           => $o->title,
            'description'     => $o->description,
            'offer_type'      => $o->offer_type,
            'discount_value'  => $o->discount_value,
            'max_discount'    => $o->max_discount,
            'min_order_value' => $o->min_order_value,
            'badge_label'     => $o->badge_label,
            'scope'           => $o->item_id ? 'item' : ($o->category_id ? 'category' : 'shop'),
            'item'            => $o->relationLoaded('item') ? $o->item : null,
            'category'        => $o->relationLoaded('category') ? $o->category : null,
            'valid_from'      => $o->valid_from?->toIso8601String(),
            'valid_until'     => $o->valid_until?->toIso8601String(),
            'status'          => $o->status,
            'is_active_now'   => $o->isCurrentlyActive(),
            'sort_order'      => $o->sort_order,
            'created_at'      => $o->created_at,
        ];
    }
}

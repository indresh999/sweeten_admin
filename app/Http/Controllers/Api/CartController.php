<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\CartItem;
use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\AppSetting;

class CartController extends Controller
{
    // ── List Cart ──────────────────────────────────────────
    public function listCart(int $userId): JsonResponse
    {
        $cartItems = CartItem::with([
            'item:id,item_name,images,status,shop_id,is_veg',
            'item.defaultVariant:id,item_id,label,price,offer_price,gst_percent,is_default,status',
        ])
        ->where('user_id', $userId)
        ->get()
        ->map(fn($c) => $this->formatCartItem($c));

        $subtotal       = $cartItems->sum('line_total');
        $gst            = $cartItems->sum('gst_amount');
        $freeAbove      = (float) AppSetting::get('free_delivery_above', 199);
        $deliveryCharge = $subtotal >= $freeAbove ? 0.0 : (float) AppSetting::get('delivery_base_charge', 30);

        return response()->json([
            'status' => true,
            'data'   => [
                'items'                => $cartItems,
                'item_count'           => $cartItems->count(),
                'subtotal'             => round($subtotal, 2),
                'gst'                  => round($gst, 2),
                'delivery_charge'      => $deliveryCharge,
                'free_delivery_above'  => $freeAbove,
                'total'                => round($subtotal + $deliveryCharge, 2),
                'total_with_gst'       => round($subtotal + $gst + $deliveryCharge, 2),
            ],
        ]);
    }

    private function formatCartItem(CartItem $c): array
    {
        $item    = $c->item;
        $variant = $item?->defaultVariant;

        $price  = (float) ($variant?->offer_price ?: ($variant?->price ?: ($c->offer_price ?: $c->price)));
        $gstPct = (float) ($variant?->gst_percent ?? 0);
        $qty    = $c->quantity;

        $imageUrls = [];
        if ($item?->images) {
            $imgs = is_array($item->images) ? $item->images : json_decode($item->images, true);
            $imageUrls = collect($imgs ?? [])->map(fn($p) => asset('storage/' . $p))->toArray();
        }

        return [
            'id'            => $c->id,
            'item_id'       => $c->item_id,
            'variant_id'    => $c->variant_id,
            'owner_id'      => $c->owner_id,
            'item_name'     => $item?->item_name ?? $c->item_name,
            'variant_label' => $variant?->label ?? $c->variant_label,
            'image_url'     => $imageUrls[0] ?? null,
            'is_veg'        => (bool) ($item?->is_veg),
            'unit_price'    => round($price, 2),
            'quantity'      => $qty,
            'gst_percent'   => $gstPct,
            'gst_amount'    => round($price * $gstPct / 100 * $qty, 2),
            'line_total'    => round($price * $qty, 2),
        ];
    }

    // ── Add to Cart ────────────────────────────────────────
    public function addToCart(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id'    => 'required|exists:app_users,id',
            'owner_id'   => 'required|exists:app_owner_shops,shop_id',
            'item_id'    => 'required|exists:items,id',
            'variant_id' => 'nullable|exists:item_variants,id',
            'quantity'   => 'required|integer|min:1|max:50',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $item = Item::findOrFail($request->item_id);

        if ($item->status !== 'active') {
            return response()->json(['status' => false, 'message' => 'This item is currently unavailable.'], 400);
        }
        if ((int) $item->shop_id !== (int) $request->owner_id) {
            return response()->json(['status' => false, 'message' => 'Item does not belong to this shop.'], 400);
        }

        // Cross-shop check
        $existingShopId = CartItem::where('user_id', $request->user_id)->value('owner_id');
        if ($existingShopId && $existingShopId != $request->owner_id) {
            return response()->json([
                'status'  => false,
                'code'    => 'DIFFERENT_SHOP',
                'message' => 'Your cart has items from another shop. Clear your cart to add items from this shop.',
            ], 409);
        }

        $variant = $request->variant_id ? ItemVariant::find($request->variant_id) : null;

        $cartItem = CartItem::where([
            'user_id'    => $request->user_id,
            'owner_id'   => $request->owner_id,
            'item_id'    => $request->item_id,
            'variant_id' => $request->variant_id,
        ])->first();

        if ($cartItem) {
            $cartItem->increment('quantity', $request->quantity);
            $cartItem->refresh();
        } else {
            $cartItem = CartItem::create([
                'user_id'       => $request->user_id,
                'owner_id'      => $request->owner_id,
                'item_id'       => $request->item_id,
                'variant_id'    => $request->variant_id,
                'quantity'      => $request->quantity,
                'item_name'     => $item->item_name,
                'variant_label' => $variant?->label,
                'price'         => $variant ? (float) $variant->price       : (float) ($item->price ?? 0),
                'offer_price'   => $variant ? (float) $variant->offer_price : (float) ($item->offer_price ?? 0),
            ]);
        }

        return response()->json(['status' => true, 'message' => 'Item added to cart.', 'data' => $cartItem], 201);
    }

    // ── Update Cart ────────────────────────────────────────
    public function updateCart(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id'  => 'required|exists:app_users,id',
            'quantity' => 'required|integer|min:1|max:50',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $cartItem = CartItem::where('id', $id)->where('user_id', $request->user_id)->firstOrFail();
        $cartItem->update(['quantity' => $request->quantity]);

        return response()->json(['status' => true, 'message' => 'Cart updated.', 'data' => $cartItem]);
    }

    // ── Remove from Cart ───────────────────────────────────
    public function removeFromCart(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:app_users,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        CartItem::where('id', $id)->where('user_id', $request->user_id)->firstOrFail()->delete();

        return response()->json(['status' => true, 'message' => 'Item removed from cart.']);
    }

    // ── Clear Cart ─────────────────────────────────────────
    public function clearCart(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:app_users,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        CartItem::where('user_id', $request->user_id)->delete();
        return response()->json(['status' => true, 'message' => 'Cart cleared.']);
    }

    // ── Apply Coupon ───────────────────────────────────────
    public function applyCoupon(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id'      => 'required|exists:app_users,id',
            'coupon_code'  => 'required|string|max:50',
            'order_amount' => 'required|numeric|min:0',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $coupon = Coupon::where('code', strtoupper($request->coupon_code))->first();
        if (!$coupon || !$coupon->isValid()) {
            return response()->json(['status' => false, 'message' => 'Invalid or expired coupon code.'], 422);
        }
        if ((float) $request->order_amount < (float) $coupon->min_order_amount) {
            $need = (float) $coupon->min_order_amount - (float) $request->order_amount;
            return response()->json(['status' => false, 'message' => "Add ₹{$need} more to use this coupon."], 422);
        }

        $used = CouponUsage::where('coupon_id', $coupon->id)->where('user_id', $request->user_id)->count();
        if ($used >= ($coupon->usage_per_user ?? 1)) {
            return response()->json(['status' => false, 'message' => 'You have already used this coupon.'], 422);
        }

        $discount = $coupon->calculateDiscount((float) $request->order_amount);

        return response()->json([
            'status'   => true,
            'message'  => "Coupon applied! You saved ₹{$discount}",
            'coupon'   => $coupon->only(['id', 'code', 'title', 'discount_type', 'discount_value', 'max_discount_amount']),
            'discount' => $discount,
        ]);
    }

    // ── Available Coupons ──────────────────────────────────
    public function availableCoupons(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id'      => 'required|exists:app_users,id',
            'order_amount' => 'nullable|numeric|min:0',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $coupons = Coupon::where('is_active', 1)
            ->where('valid_from',  '<=', now())
            ->where('valid_until', '>=', now())
            ->where(fn($q) => $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'))
            ->when($request->order_amount, fn($q) => $q->where('min_order_amount', '<=', $request->order_amount))
            ->orderByDesc('discount_value')
            ->get()
            ->map(function ($c) use ($request) {
                $used = CouponUsage::where('coupon_id', $c->id)->where('user_id', $request->user_id)->count();
                $c->is_applicable = $used < ($c->usage_per_user ?? 1);
                $c->savings_text  = $c->discount_type === 'percent'
                    ? "{$c->discount_value}% OFF" . ($c->max_discount_amount ? " up to ₹{$c->max_discount_amount}" : '')
                    : "₹{$c->discount_value} OFF";
                return $c;
            });

        return response()->json(['status' => true, 'data' => $coupons]);
    }
}

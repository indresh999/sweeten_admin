<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\CartItem;
use App\Models\Item;
use App\Models\ItemVariant;

class CartController extends Controller
{
    // ── List cart — Flutter expects { data: { items: [...], delivery_charge: N } }
    public function listCart(Request $request, $user_id)
    {
        $cartItems = CartItem::with([
            'item' => function ($q) {
                $q->select('id', 'item_name', 'description', 'images', 'shop_id');
            },
            'variant' => function ($q) {
                $q->select('id', 'item_id', 'label', 'price', 'offer_price', 'gst_percent', 'is_default');
            },
        ])->where('user_id', $user_id)->get();

        $items = $cartItems->map(function ($c) {
            $item    = $c->item;
            $variant = $c->variant;
            $images  = is_array($item?->images) ? $item->images
                        : (is_string($item?->images) ? json_decode($item->images, true) ?? [] : []);
            $price   = $variant ? ($variant->offer_price ?? $variant->price) : ($c->offer_price ?? $c->price);

            return [
                'id'           => $c->id,
                'item_id'      => $c->item_id,
                'variant_id'   => $c->variant_id,
                'owner_id'     => $c->owner_id,
                'shop_id'      => $c->owner_id,
                'item_name'    => $item?->item_name ?? '',
                'variant_label'=> $variant?->label ?? '',
                'image_url'    => $images[0] ?? '',
                'quantity'     => $c->quantity,
                'price'        => $price,
                'gst_percent'  => $variant?->gst_percent ?? 0,
                'line_total'   => $price * $c->quantity,
            ];
        });

        $subtotal = $items->sum('line_total');

        return response()->json([
            'status' => true,
            'data'   => [
                'items'           => $items,
                'delivery_charge' => $subtotal >= 499 ? 0 : 30,
                'item_count'      => $cartItems->count(),
                'subtotal'        => $subtotal,
            ],
        ]);
    }

    // ── Add to cart
    public function addToCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'   => 'required|exists:app_users,id',
            'owner_id'  => 'required|exists:app_owner_shops,shop_id',
            'item_id'   => 'required|exists:items,id',
            'variant_id'=> 'nullable|exists:item_variants,id',
            'quantity'  => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        // Check for cross-shop conflict
        $existing = CartItem::where('user_id', $request->user_id)->first();
        if ($existing && $existing->owner_id != $request->owner_id) {
            return response()->json([
                'status'  => false,
                'message' => 'DIFFERENT_SHOP: Clear your cart before adding from a different store.',
                'code'    => 'DIFFERENT_SHOP',
            ], 409);
        }

        $variant = $request->variant_id ? ItemVariant::find($request->variant_id) : null;
        $item    = Item::find($request->item_id);
        $price   = $variant ? ($variant->offer_price ?? $variant->price)
                            : ($item->offer_price   ?? $item->price);

        $cartItem = CartItem::where([
            'user_id'    => $request->user_id,
            'item_id'    => $request->item_id,
            'variant_id' => $request->variant_id,
        ])->first();

        if ($cartItem) {
            $cartItem->quantity += $request->quantity;
            $cartItem->save();
        } else {
            $cartItem = CartItem::create([
                'user_id'    => $request->user_id,
                'owner_id'   => $request->owner_id,
                'item_id'    => $request->item_id,
                'variant_id' => $request->variant_id,
                'quantity'   => $request->quantity,
                'price'      => $price,
                'offer_price'=> $variant ? $variant->offer_price : $item->offer_price,
            ]);
        }

        return response()->json(['status' => true, 'message' => 'Item added to cart', 'data' => $cartItem], 201);
    }

    // ── Update quantity
    public function updateCart(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $cartItem = CartItem::findOrFail($id);
        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        return response()->json(['status' => true, 'message' => 'Cart updated', 'data' => $cartItem]);
    }

    // ── Remove single item
    public function removeFromCart(Request $request, $id)
    {
        $cartItem = CartItem::where('id', $id)
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->firstOrFail();

        $cartItem->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Item removed from cart',
            'remaining_items' => CartItem::where('user_id', $request->user_id ?? $cartItem->user_id)->count(),
        ]);
    }

    // ── Clear full cart  (owner_id optional — clear all if omitted)
    public function clearCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:app_users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $query = CartItem::where('user_id', $request->user_id);
        if ($request->owner_id) {
            $query->where('owner_id', $request->owner_id);
        }
        $query->delete();

        return response()->json(['status' => true, 'message' => 'Cart cleared']);
    }

    // ── Remove by item + variant
    public function removeByItem(Request $request)
    {
        CartItem::where('user_id', $request->user_id)
            ->where('item_id', $request->item_id)
            ->where('variant_id', $request->variant_id)
            ->delete();

        return response()->json(['status' => true, 'message' => 'Item removed']);
    }

    // ── Apply coupon (stub — extend with Coupon model when ready)
    public function applyCoupon(Request $request)
    {
        $request->validate([
            'user_id'      => 'required|exists:app_users,id',
            'coupon_code'  => 'required|string',
            'order_amount' => 'required|numeric',
        ]);

        // Simple flat-discount demo — replace with real Coupon model lookup
        $coupon = \DB::table('coupons')
            ->where('code', strtoupper($request->coupon_code))
            ->where('status', 'active')
            ->where(function ($q) { $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()); })
            ->first();

        if (!$coupon) {
            return response()->json(['status' => false, 'message' => 'Invalid or expired coupon'], 400);
        }

        $discount = $coupon->discount_type === 'percent'
            ? ($request->order_amount * $coupon->discount_value / 100)
            : $coupon->discount_value;

        if ($coupon->max_discount && $discount > $coupon->max_discount) {
            $discount = $coupon->max_discount;
        }

        return response()->json([
            'status'   => true,
            'message'  => 'Coupon applied!',
            'discount' => round($discount, 2),
            'coupon'   => $coupon,
        ]);
    }
}

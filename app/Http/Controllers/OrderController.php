<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Item;
use App\Models\UserAddress;
use App\Models\CartItem;
use App\Models\AppOwnerUser;
use App\Models\CancelReason;

class OrderController extends Controller
{
    // ── Haversine distance ─────────────────────────────────
    private function calculateDistance($lat1, $lng1, $lat2, $lng2): float
    {
        $R    = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a    = sin($dLat / 2) ** 2 +
                cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
                sin($dLng / 2) ** 2;
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function getDeliveryCharge(float $distance): float
    {
        return DB::table('delivery_charges')
            ->where('status', 1)
            ->where('min_distance', '<=', $distance)
            ->where('max_distance', '>=', $distance)
            ->orderByDesc('priority')
            ->value('charge_amount') ?? 30;
    }

    private function getPlatformFees(): object
    {
        return DB::table('platform_fees')->where('status', 1)->orderByDesc('priority')->first()
            ?? (object)['handling_fee' => 0, 'packing_fee' => 0];
    }

    private function addTimeline(int $orderId, string $status, ?string $message = null): void
    {
        DB::table('delivery_timeline')->insert([
            'order_id'   => $orderId,
            'status'     => $status,
            'message'    => $message,
            'created_at' => now(),
        ]);
    }

    // ── Create Order (POST /orders) ────────────────────────
    public function createOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'        => 'required|integer|exists:app_users,id',  // Fixed: app_users not users
            'shop_id'        => 'required|integer|exists:app_owner_shops,shop_id',
            'address_id'     => 'required|integer|exists:user_addresses,id',
            'payment_method' => 'nullable|string|in:cod,online,wallet',
            'coupon_code'    => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // 1. Address snapshot
            $address = UserAddress::where('id', $request->address_id)
                ->where('user_id', $request->user_id)
                ->firstOrFail();

            // 2. Cart items
            $cartItems = CartItem::with(['item', 'variant'])
                ->where('user_id', $request->user_id)
                ->where('owner_id', $request->shop_id)
                ->get();

            if ($cartItems->isEmpty()) {
                throw new \Exception('Cart is empty for this shop');
            }

            // 3. Shop location
            $shop = AppOwnerUser::where('shop_id', $request->shop_id)->firstOrFail();

            // 4. Item total
            $totalAmount = 0;
            foreach ($cartItems as $cart) {
                $price        = $cart->variant
                    ? ($cart->variant->offer_price ?? $cart->variant->price)
                    : ($cart->item->offer_price   ?? $cart->item->price ?? $cart->price);
                $totalAmount += $price * $cart->quantity;
            }

            // 5. Charges
            $distance       = $this->calculateDistance($address->lat, $address->lng, $shop->latitude, $shop->longitude);
            $deliveryCharge = $totalAmount >= 499 ? 0 : $this->getDeliveryCharge($distance);
            $fees           = $this->getPlatformFees();
            $handlingFee    = $fees->handling_fee ?? 0;
            $packingFee     = $fees->packing_fee  ?? 0;

            // 6. GST
            $gstPercent  = DB::table('gst_settings')->where('status', 1)->orderByDesc('priority')->value('gst_percent') ?? 0;
            $taxAmount   = ($totalAmount * $gstPercent) / 100;
            $finalAmount = $totalAmount + $taxAmount + $deliveryCharge + $handlingFee + $packingFee;

            // 7. Coupon discount
            $couponDiscount = 0;
            if ($request->coupon_code) {
                $coupon = DB::table('coupons')
                    ->where('code', strtoupper($request->coupon_code))
                    ->where('status', 'active')
                    ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
                    ->first();
                if ($coupon) {
                    $couponDiscount = $coupon->discount_type === 'percent'
                        ? ($totalAmount * $coupon->discount_value / 100)
                        : $coupon->discount_value;
                    if ($coupon->max_discount && $couponDiscount > $coupon->max_discount) {
                        $couponDiscount = $coupon->max_discount;
                    }
                    $finalAmount = max(0, $finalAmount - $couponDiscount);
                }
            }

            // 8. Create order
            $order = Order::create([
                'user_id'         => $request->user_id,
                'shop_id'         => $request->shop_id,
                'total_amount'    => $totalAmount,
                'gst_percent'     => $gstPercent,
                'tax_amount'      => $taxAmount,
                'delivery_charge' => $deliveryCharge,
                'handling_fee'    => $handlingFee,
                'packing_fee'     => $packingFee,
                'coupon_discount' => $couponDiscount,
                'final_amount'    => $finalAmount,
                'payment_method'  => $request->payment_method ?? 'cod',
                'status'          => 'pending',
                // Address snapshot
                'address_label'   => $address->label,
                'address_line'    => $address->address_line,
                'city'            => $address->city,
                'state'           => $address->state,
                'pincode'         => $address->pincode,
                'lat'             => $address->lat,
                'lng'             => $address->lng,
            ]);

            // 9. Order items snapshot
            foreach ($cartItems as $cart) {
                $item      = $cart->item;
                $variant   = $cart->variant;
                $unitPrice = $variant
                    ? ($variant->offer_price ?? $variant->price)
                    : ($item->offer_price    ?? $item->price);

                $images = is_array($item->images)
                    ? $item->images
                    : (json_decode($item->images, true) ?? []);

                OrderItem::create([
                    'order_id'    => $order->id,
                    'item_id'     => $item->id,
                    'variant_id'  => $variant?->id,
                    'item'        => json_encode([
                        'name'        => $item->item_name,
                        'description' => $item->description,
                        'images'      => $images,
                        'price'       => $item->price,
                        'offer_price' => $item->offer_price,
                        'variant'     => $variant ? ['id' => $variant->id, 'label' => $variant->label] : null,
                    ]),
                    'quantity'    => $cart->quantity,
                    'price'       => $item->price,
                    'offer_price' => $item->offer_price,
                    'item_total'  => $unitPrice * $cart->quantity,
                ]);
            }

            // 10. Clear cart
            CartItem::where('user_id', $request->user_id)
                ->where('owner_id', $request->shop_id)
                ->delete();

            // 11. Timeline
            $this->addTimeline($order->id, 'pending', 'Order placed');

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Order placed successfully',
                'data'    => $order->load('items'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ── List orders (GET /orders?user_id=X&status=Y) ───────
    public function listUserOrders(Request $request)
    {
        $query = Order::with(['items', 'owner:shop_id,restaurant_name,restaurant_address,city'])
            ->orderByDesc('created_at');

        if ($request->user_id) $query->where('user_id', $request->user_id);
        if ($request->shop_id) $query->where('shop_id', $request->shop_id);
        if ($request->status)  $query->where('status',  $request->status);

        if ($request->from_date && $request->to_date) {
            $query->whereBetween('created_at', [$request->from_date.' 00:00:00', $request->to_date.' 23:59:59']);
        }

        $orders = $query->get();

        return response()->json(['status' => true, 'data' => $orders]);
    }

    // ── Single order (GET /orders/{id}) ────────────────────
    public function getOrderById(Request $request, $id)
    {
        $order = Order::with(['items', 'owner:shop_id,restaurant_name,city'])->find($id);

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }

        return response()->json(['status' => true, 'data' => $order]);
    }

    // ── Cancel order (POST /orders/{id}/cancel) ─────────────
    public function cancelOrder(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'cancel_reason_id' => 'required|integer|exists:cancel_reasons,id',
            'cancel_remark'    => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $order = Order::where('id', $id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->first();

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order cannot be cancelled'], 400);
        }

        $order->update([
            'status'           => 'cancelled',
            'cancel_reason_id' => $request->cancel_reason_id,
            'cancel_remark'    => $request->cancel_remark,
        ]);

        $this->addTimeline($order->id, 'cancelled', 'Order cancelled by user');

        return response()->json([
            'status'  => true,
            'message' => 'Order cancelled',
            'data'    => $order->fresh(),
        ]);
    }

    // ── Cancel reasons ─────────────────────────────────────
    public function getCancelReasons()
    {
        $reasons = CancelReason::select('id', 'reason')->orderBy('id')->get();
        return response()->json(['status' => true, 'data' => $reasons]);
    }

    // ── Update order ───────────────────────────────────────
    public function updateOrder(Request $request, $orderId)
    {
        $order = Order::where('id', $orderId)->where('status', 'pending')->firstOrFail();
        $order->items()->delete();

        foreach ($request->items ?? [] as $itemData) {
            $order->items()->create($itemData);
        }

        $totalAmount = collect($request->items ?? [])->sum(
            fn($i) => (($i['offer_price'] ?? null) ?: ($i['price'] ?? 0)) * ($i['quantity'] ?? 1)
        );

        $order->update(['total_amount' => $totalAmount]);

        return response()->json(['status' => true, 'message' => 'Order updated', 'data' => $order->load('items')]);
    }
}

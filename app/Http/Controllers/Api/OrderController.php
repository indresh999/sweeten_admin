<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
use App\Models\UserAddress;
use App\Models\AppOwnerUser;
use App\Models\CancelReason;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\AppUser;
use App\Models\AppSetting;
use App\Models\DeliveryTimeline;
use App\Models\ShopReview;
use App\Services\NotificationService;

class OrderController extends Controller
{
    // ── Helpers ────────────────────────────────────────────
    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r    = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a    = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $r * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function getDeliveryCharge(float $distance, float $orderAmount): float
    {
        $freeAbove = (float) AppSetting::get('free_delivery_above', 199);
        if ($orderAmount >= $freeAbove) return 0.0;

        $charge = DB::table('delivery_charges')
            ->where('status', 1)
            ->where('min_distance', '<=', $distance)
            ->where('max_distance', '>=', $distance)
            ->orderByDesc('priority')
            ->value('charge_amount');

        return (float) ($charge ?? AppSetting::get('delivery_base_charge', 30));
    }

    private function getPlatformFees(float $orderAmount): object
    {
        $fees = DB::table('platform_fees')
            ->where('status', 1)
            ->where(fn($q) => $q->whereNull('min_order_amount')->orWhere('min_order_amount', '<=', $orderAmount))
            ->orderByDesc('priority')
            ->first();
        return $fees ?? (object) ['handling_fee' => 0, 'packing_fee' => 0];
    }

    private function addTimeline(int $orderId, string $status, string $message = null): void
    {
        DeliveryTimeline::create([
            'order_id'   => $orderId,
            'status'     => $status,
            'message'    => $message ?? ucfirst(str_replace('_', ' ', $status)),
            'created_at' => now(),
        ]);
    }

    // ── Create Order ───────────────────────────────────────
    public function createOrder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id'              => 'required|exists:app_users,id',
            'shop_id'              => 'required|exists:app_owner_shops,shop_id',
            'address_id'           => 'required|exists:user_addresses,id',
            'payment_method'       => 'required|in:cod,online,wallet',
            'coupon_code'          => 'nullable|string|max:50',
            'wallet_use'           => 'nullable|boolean',
            'special_instructions' => 'nullable|string|max:500',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // Validate address belongs to user
            $address = UserAddress::where('id', $request->address_id)
                ->where('user_id', $request->user_id)
                ->firstOrFail();

            // Get cart items for this shop
            $cartItems = CartItem::with([
                'item',
                'item.variants' => fn($q) => $q->where('status', 'active'),
            ])
            ->where('user_id', $request->user_id)
            ->where('owner_id', $request->shop_id)
            ->lockForUpdate()
            ->get();

            if ($cartItems->isEmpty()) {
                DB::rollBack();
                return response()->json(['status' => false, 'message' => 'Your cart is empty.'], 422);
            }

            $minOrder = (float) AppSetting::get('min_order_amount', 0);

            // Build order items & calculate subtotal
            $totalAmount    = 0.0;
            $totalGst       = 0.0;
            $orderItemsData = [];

            foreach ($cartItems as $cart) {
                $item = $cart->item;
                if (!$item || $item->status !== 'active') {
                    DB::rollBack();
                    return response()->json(['status' => false, 'message' => "'{$item?->item_name}' is no longer available."], 422);
                }

                // Pick variant by variant_id, fallback to default
                $variant = $cart->variant_id
                    ? $item->variants->firstWhere('id', $cart->variant_id)
                    : ($item->variants->firstWhere('is_default', true) ?? $item->variants->first());

                $mrp       = $variant ? (float) $variant->price       : (float) ($item->price ?? 0);
                $unitPrice = $variant ? (float) ($variant->offer_price ?: $variant->price) : (float) ($item->offer_price ?: $item->price ?? 0);
                $gstPct    = $variant ? (float) $variant->gst_percent : 0.0;
                $lineTotal = $unitPrice * $cart->quantity;
                $lineGst   = $lineTotal * $gstPct / 100;

                $totalAmount += $lineTotal;
                $totalGst    += $lineGst;

                $orderItemsData[] = [
                    'item_id'     => $item->id,
                    'variant_id'  => $variant?->id,
                    'quantity'    => $cart->quantity,
                    'price'       => $mrp,
                    'offer_price' => $unitPrice,
                    'item_total'  => $lineTotal,
                    'gst_amount'  => round($lineGst, 2),
                    'item_snapshot' => [
                        'name'        => $item->item_name,
                        'image'       => is_array($item->images) ? ($item->images[0] ?? null) : null,
                        'category_id' => $item->category_id,
                        'gst_percent' => $gstPct,
                        'is_veg'      => (bool) $item->is_veg,
                        'variant_label'=> $variant?->label,
                    ],
                ];
            }

            if ($totalAmount < $minOrder && $minOrder > 0) {
                DB::rollBack();
                return response()->json(['status' => false, 'message' => "Minimum order amount is ₹{$minOrder}."], 422);
            }

            // Distance & charges
            $shop           = AppOwnerUser::where('shop_id', $request->shop_id)->firstOrFail();
            $distance       = $this->haversine($address->lat ?? 0, $address->lng ?? 0, $shop->latitude ?? 0, $shop->longitude ?? 0);
            $deliveryCharge = $this->getDeliveryCharge($distance, $totalAmount);
            $fees           = $this->getPlatformFees($totalAmount);
            $handlingFee    = (float) ($fees->handling_fee ?? 0);
            $packingFee     = (float) ($fees->packing_fee  ?? 0);

            // Coupon
            $discountAmount = 0.0;
            $couponId       = null;
            $couponCode     = null;

            if ($request->filled('coupon_code')) {
                $coupon = Coupon::where('code', strtoupper($request->coupon_code))->first();
                if (!$coupon || !$coupon->isValid()) {
                    DB::rollBack();
                    return response()->json(['status' => false, 'message' => 'Invalid or expired coupon.'], 422);
                }
                $usedCount = CouponUsage::where('coupon_id', $coupon->id)->where('user_id', $request->user_id)->count();
                if ($usedCount >= $coupon->usage_per_user) {
                    DB::rollBack();
                    return response()->json(['status' => false, 'message' => 'Coupon usage limit reached.'], 422);
                }
                $discountAmount = $coupon->calculateDiscount($totalAmount);
                $couponId       = $coupon->id;
                $couponCode     = $coupon->code;
            }

            // Wallet
            $walletUsed = 0.0;
            if ($request->boolean('wallet_use')) {
                $wallet      = AppUser::find($request->user_id)?->getOrCreateWallet();
                $payable     = $totalAmount + $totalGst + $deliveryCharge + $handlingFee + $packingFee - $discountAmount;
                $walletUsed  = min((float) ($wallet?->balance ?? 0), $payable);
            }

            $finalAmount = max(0.0, $totalAmount + $totalGst + $deliveryCharge + $handlingFee + $packingFee - $discountAmount - $walletUsed);

            // Create order
            $order = Order::create([
                'user_id'              => $request->user_id,
                'shop_id'              => $request->shop_id,
                'total_amount'         => round($totalAmount, 2),
                'gst_amount'           => round($totalGst, 2),
                'delivery_charge'      => round($deliveryCharge, 2),
                'handling_fee'         => round($handlingFee, 2),
                'packing_fee'          => round($packingFee, 2),
                'discount_amount'      => round($discountAmount, 2),
                'wallet_used'          => round($walletUsed, 2),
                'final_amount'         => round($finalAmount, 2),
                'coupon_id'            => $couponId,
                'coupon_code'          => $couponCode,
                'payment_method'       => $request->payment_method,
                'payment_status'       => 'pending',
                'status'               => 'pending',
                'special_instructions' => $request->special_instructions,
                'address_label'        => $address->label,
                'address_line'         => $address->address_line,
                'city'                 => $address->city,
                'state'                => $address->state,
                'pincode'              => $address->pincode,
                'lat'                  => $address->lat,
                'lng'                  => $address->lng,
                'expected_delivery_at' => now()->addMinutes(
                    (int) AppSetting::get('default_delivery_minutes', 45)
                ),
            ]);

            // Create order items
            foreach ($orderItemsData as $d) {
                OrderItem::create([
                    'order_id'    => $order->id,
                    'item_id'     => $d['item_id'],
                    'variant_id'  => $d['variant_id'],
                    'quantity'    => $d['quantity'],
                    'price'       => $d['price'],
                    'offer_price' => $d['offer_price'],
                    'item_total'  => $d['item_total'],
                    'gst_amount'  => $d['gst_amount'],
                    'item'        => $d['item_snapshot'],
                ]);
            }

            // Record coupon usage
            if ($couponId) {
                CouponUsage::create([
                    'coupon_id'      => $couponId,
                    'user_id'        => $request->user_id,
                    'order_id'       => $order->id,
                    'discount_given' => $discountAmount,
                ]);
                Coupon::where('id', $couponId)->increment('used_count');
            }

            // Debit wallet
            if ($walletUsed > 0) {
                AppUser::find($request->user_id)?->getOrCreateWallet()
                    ?->debit($walletUsed, 'Payment for Order #' . $order->id, 'order', $order->id);
            }

            // Clear cart
            CartItem::where('user_id', $request->user_id)->where('owner_id', $request->shop_id)->delete();

            // Timeline
            $this->addTimeline($order->id, 'placed', 'Order placed successfully');

            DB::commit();

            // Notify user
            NotificationService::orderPlaced($request->user_id, $order->id);

            Log::info('[Order] Created #' . $order->id . ' by user ' . $request->user_id . ' amount ₹' . $finalAmount);

            return response()->json([
                'status'  => true,
                'message' => 'Order placed successfully!',
                'data'    => $order->load(['items']),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[Order] createOrder failed: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Could not place order. Please try again.'], 500);
        }
    }

    // ── List User Orders ───────────────────────────────────
    public function listUserOrders(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id'   => 'required|exists:app_users,id',
            'status'    => 'nullable|string',
            'from_date' => 'nullable|date_format:Y-m-d',
            'to_date'   => 'nullable|date_format:Y-m-d',
            'per_page'  => 'nullable|integer|min:1|max:50',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $query = Order::with(['items', 'owner:shop_id,restaurant_name,city', 'timeline'])
            ->where('user_id', $request->user_id)
            ->orderByDesc('created_at');

        if ($request->filled('status'))    $query->where('status', $request->status);
        if ($request->filled('from_date')) $query->whereDate('created_at', '>=', $request->from_date);
        if ($request->filled('to_date'))   $query->whereDate('created_at', '<=', $request->to_date);

        return response()->json(['status' => true, 'data' => $query->paginate($request->get('per_page', 10))]);
    }

    // ── Get Single Order ───────────────────────────────────
    public function getOrderById(int $id): JsonResponse
    {
        $order = Order::with([
            'items', 'owner:shop_id,restaurant_name,restaurant_address,latitude,longitude',
            'user:id,full_name,phone_number',
            'timeline', 'assignment.boy:id,full_name,phone_number,vehicle_type',
            'cancelReason', 'coupon:id,code,title,discount_type,discount_value',
        ])->find($id);

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found.'], 404);
        }

        return response()->json(['status' => true, 'data' => $order]);
    }

    // ── Cancel Order ───────────────────────────────────────
    public function cancelOrder(Request $request, int $orderId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cancel_reason_id' => 'required|integer|exists:cancel_reasons,id',
            'cancel_remark'    => 'nullable|string|max:500',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $order = Order::where('id', $orderId)
            ->whereNotIn('status', ['cancelled', 'delivered', 'out_for_delivery'])
            ->firstOrFail();

        DB::beginTransaction();
        try {
            $order->update([
                'status'            => 'cancelled',
                'cancel_reason_id'  => $request->cancel_reason_id,
                'cancel_remark'     => $request->cancel_remark,
            ]);

            // Refund wallet if used
            if ($order->wallet_used > 0) {
                AppUser::find($order->user_id)?->getOrCreateWallet()
                    ?->credit($order->wallet_used, 'Refund for Order #' . $order->id, 'order_refund', $order->id);
            }

            $this->addTimeline($order->id, 'cancelled', 'Order cancelled by customer');
            NotificationService::orderCancelled($order->user_id, $order->id);

            DB::commit();
            return response()->json(['status' => true, 'message' => 'Order cancelled.', 'data' => $order->load('cancelReason')]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[Order] cancelOrder failed: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Could not cancel order.'], 500);
        }
    }

    // ── Rate Order ─────────────────────────────────────────
    public function rateOrder(Request $request, int $orderId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id'         => 'required|exists:app_users,id',
            'shop_rating'     => 'required|integer|min:1|max:5',
            'delivery_rating' => 'nullable|integer|min:1|max:5',
            'comment'         => 'nullable|string|max:500',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $order = Order::where('id', $orderId)
            ->where('user_id', $request->user_id)
            ->where('status', 'delivered')
            ->firstOrFail();

        if ($order->rated_at) {
            return response()->json(['status' => false, 'message' => 'Order already rated.'], 422);
        }

        DB::beginTransaction();
        try {
            $order->update([
                'shop_rating'     => $request->shop_rating,
                'delivery_rating' => $request->delivery_rating,
                'rated_at'        => now(),
            ]);

            ShopReview::updateOrCreate(
                ['order_id' => $orderId, 'user_id' => $request->user_id],
                ['shop_id' => $order->shop_id, 'rating' => $request->shop_rating, 'comment' => $request->comment]
            );

            DB::commit();
            return response()->json(['status' => true, 'message' => 'Thank you for your feedback!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Could not save rating.'], 500);
        }
    }

    // ── Timeline ───────────────────────────────────────────
    public function getOrderTimeline(int $orderId): JsonResponse
    {
        $timeline = DeliveryTimeline::where('order_id', $orderId)->orderBy('created_at')->get();
        return response()->json(['status' => true, 'data' => $timeline]);
    }

    // ── Cancel Reasons ─────────────────────────────────────
    public function getCancelReasons(): JsonResponse
    {
        $reasons = CancelReason::select('id', 'reason')->orderBy('id')->get();
        return response()->json(['status' => true, 'data' => $reasons]);
    }

    // ── Reorder ────────────────────────────────────────────
    public function reorder(Request $request, int $orderId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:app_users,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $order = Order::with('items')->where('id', $orderId)->where('user_id', $request->user_id)->firstOrFail();

        CartItem::where('user_id', $request->user_id)->delete();

        foreach ($order->items as $oi) {
            CartItem::updateOrCreate(
                [
                    'user_id'    => $request->user_id,
                    'owner_id'   => $order->shop_id,
                    'item_id'    => $oi->item_id,
                    'variant_id' => $oi->variant_id,
                ],
                [
                    'quantity'    => $oi->quantity,
                    'price'       => $oi->price,
                    'offer_price' => $oi->offer_price,
                    'item_name'   => $oi->item['name'] ?? '',
                ]
            );
        }

        return response()->json(['status' => true, 'message' => 'Items from this order added to your cart.']);
    }
}

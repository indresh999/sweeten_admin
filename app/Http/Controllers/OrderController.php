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

    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat/2) * sin($dLat/2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng/2) * sin($dLng/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $earthRadius * $c;
    }


    public function getDeliveryCharge($distance)
    {
        return \DB::table('delivery_charges')
            ->where('status', 1)
            ->where('min_distance', '<=', $distance)
            ->where('max_distance', '>=', $distance)
            ->orderBy('priority', 'desc')
            ->value('charge_amount') ?? 0;
    }

   public function getGST($orderAmount = null)
    {
        $query = \DB::table('gst_settings')
            ->where('status', 1)
            ->orderBy('priority', 'desc');

        if ($orderAmount) {
            $query->where(function ($q) use ($orderAmount) {
                $q->whereNull('min_order_amount')
                ->orWhere('min_order_amount', '<=', $orderAmount);
            });
        }

        return $query->value('gst_percent') ?? 0;
    }

    public function getPlatformFees($orderAmount = null)
    {
        $query = \DB::table('platform_fees')
            ->where('status', 1)
            ->orderBy('priority', 'desc');

        if ($orderAmount) {
            $query->where(function ($q) use ($orderAmount) {
                $q->whereNull('min_order_amount')
                ->orWhere('min_order_amount', '<=', $orderAmount);
            });
        }

        return $query->first() ?? (object)[
            'handling_fee' => 0,
            'packing_fee'  => 0,
        ];
    }
    protected function validateOrderData(Request $request, $isUpdate = false)
    {
        $rules = [
            'user_id' => 'required|integer|exists:app_users,id',
            'shop_id' => 'required|integer|exists:app_owner_shops,shop_id',
            'address_id' => 'required|integer|exists:user_addresses,id',

            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.offer_price' => 'nullable|numeric|min:0'
        ];

        if ($isUpdate) {
            unset($rules['user_id'], $rules['shop_id'], $rules['address_id']);
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            abort(response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422));
        }

        return $validator->validated();
    }
 
    private function addTimeline($order_id, $status, $message = null)
    {
        \DB::table('delivery_timeline')->insert([
            'order_id' => $order_id,
            'status' => $status,
            'message' => $message,
            'created_at' => now()
        ]);
    }
    public function createOrder(Request $request)
    {
        $request->validate([
            'user_id'    => 'required|exists:users,id',
            'shop_id'    => 'required',
            'address_id'=> 'required|exists:user_addresses,id',
        ]);

        DB::beginTransaction();

        try {

            /* ----------------------------------------------------
             | 1. Fetch Address (Snapshot)
             ---------------------------------------------------- */
            $address = UserAddress::where('id', $request->address_id)
                ->where('user_id', $request->user_id)
                ->firstOrFail();

            /* ----------------------------------------------------
             | 2. Fetch Cart Items
             ---------------------------------------------------- */
            $cartItems = CartItem::with('item')
                ->where('user_id', $request->user_id)
                ->where('owner_id', $request->shop_id)
                ->get();

            if ($cartItems->isEmpty()) {
                throw new \Exception('Cart is empty');
            }

            /* ----------------------------------------------------
             | 3. Fetch Shop Location
             ---------------------------------------------------- */
            $shop = AppOwnerUser::where('shop_id', $request->shop_id)->firstOrFail();

            /* ----------------------------------------------------
             | 4. Calculate Item Total
             ---------------------------------------------------- */
            $totalAmount = 0;

            foreach ($cartItems as $cart) {
                $price = $cart->item->offer_price ?? $cart->item->price;
                $totalAmount += ($price * $cart->quantity);
            }

            /* ----------------------------------------------------
             | 5. Distance & Charges
             ---------------------------------------------------- */
            $distance = $this->calculateDistance(
                $address->lat,
                $address->lng,
                $shop->latitude,
                $shop->longitude
            );

            $deliveryCharge = $this->getDeliveryCharge($distance);
            $fees = $this->getPlatformFees();
            $handlingFee = $fees->handling_fee ?? 0;
            $packingFee  = $fees->packing_fee ?? 0;

            /* ----------------------------------------------------
             | 6. GST
             ---------------------------------------------------- */
            $gstPercent = $this->getGST();
            $taxAmount = ($totalAmount * $gstPercent) / 100;

            $finalAmount = $totalAmount + $taxAmount + $deliveryCharge + $handlingFee + $packingFee;

            /* ----------------------------------------------------
             | 7. Create Order
             ---------------------------------------------------- */
            $order = Order::create([
                'user_id'         => $request->user_id,
                'shop_id'         => $request->shop_id,
                'total_amount'    => $totalAmount,
                'gst_percent'     => $gstPercent,
                'tax_amount'      => $taxAmount,
                'delivery_charge' => $deliveryCharge,
                'handling_fee'    => $handlingFee,
                'packing_fee'     => $packingFee,
                'final_amount'    => $finalAmount,
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

            /* ----------------------------------------------------
             | 8. Create Order Items (Snapshot)
             ---------------------------------------------------- */
            foreach ($cartItems as $cart) {

                $item = $cart->item;
                $unitPrice = $item->offer_price ?? $item->price;

                $itemSnapshot = [
                    'item_id'         => $item->id,
                    'name'            => $item->item_name,
                    'description'     => $item->description,
                    'image'           => $item->image,
                    'price'           => $item->price,
                    'offer_price'     => $item->offer_price,
                    'category_id'     => $item->category_id,
                    'subcategory_id'  => $item->subcategory_id,
                    'gst_percent'     => $item->gst_percent,
                ];

                OrderItem::create([
                    'order_id'    => $order->id,
                    'item_id'     => $item->id,
                    'item'        => $itemSnapshot, // JSON snapshot
                    'quantity'    => $cart->quantity,
                    'price'       => $item->price,
                    'offer_price' => $item->offer_price,
                    'item_total'  => $unitPrice * $cart->quantity,
                ]);
            }

            /* ----------------------------------------------------
             | 9. Clear Cart
             ---------------------------------------------------- */
            CartItem::where('user_id', $request->user_id)
                ->where('owner_id', $request->shop_id)
                ->delete();

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Order created successfully',
                'data'    => $order->load('items')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'error'  => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update / Modify an existing order
     */
    public function updateOrder(Request $request, $orderId)
    {
        $validated = $this->validateOrderData($request, true);

        DB::beginTransaction();

        try {
            $order = Order::where('id', $orderId)->where('status', 'pending')->firstOrFail();

            // Delete old items
            $order->items()->delete();

            // Recreate new items
            foreach ($validated['items'] as $itemData) {
                $order->items()->create($itemData);
            }

            // Recalculate total
            $totalAmount = collect($validated['items'])->sum(function ($item) {
                return ($item['offer_price'] ?? $item['price']) * $item['quantity'];
            });

            $order->update(['total_amount' => $totalAmount]);

            DB::commit();

            return response()->json([
                'message' => 'Order updated successfully.',
                'data' => $order->load('items.item', 'owner')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Cancel an order
     */
   public function cancelOrder(Request $request, $orderId)
{
    $validated = $request->validate([
        'cancel_reason_id' => 'required|integer|exists:cancel_reasons,id',
        'cancel_remark' => 'nullable|string|max:500'
    ]);

    try {
        $order = Order::where('id', $orderId)
            ->where('status', '!=', 'cancelled')
            ->firstOrFail();

        $order->update([
            'status' => 'cancelled',
            'cancel_reason_id' => $validated['cancel_reason_id'],
            'cancel_remark' => $validated['cancel_remark'] ?? null
        ]);

        return response()->json([
            'message' => 'Order cancelled successfully.',
            'data' => $order->load('cancelReason')
        ], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Order not found or already cancelled.'], 404);
    }
}

    /**
     * List user orders
     */
    public function listUserOrders(Request $request)
    {
        $userId   = $request->user_id;
        $shopId   = $request->shop_id; 
        $status   = $request->status;
        $fromDate = $request->from_date;  // YYYY-MM-DD
        $toDate   = $request->to_date;    // YYYY-MM-DD

        $query = Order::with(['items.item', 'owner'])
            ->orderBy('created_at', 'desc');

        // Customer orders
        if ($userId) {
            $query->where('user_id', $userId);
        }

        // Shop owner orders
        if ($shopId) {
            $query->where('shop_id', $shopId);
        }

        // Filter by status
        if (!empty($status)) {
            $query->where('status', $status);
        }

        // Filter by date range
        if ($fromDate && $toDate) {
            $query->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59']);
        } 
        else if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        } 
        else if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $orders = $query->get();

        return response()->json([
            'data' => $orders
        ], 200);
    }

    public function getCancelReasons()
    {
        $reasons = CancelReason::
          select('id', 'reason')
            ->orderBy('id')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $reasons
        ], 200);
    }

    public function getOrderById(Request $request, $id)
    {
        $order = Order::with(['items.item', 'owner'])
            ->where('id', $id)
            ->first();

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $order
        ], 200);
    }
}
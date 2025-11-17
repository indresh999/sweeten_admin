<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Item;

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


    private function getDeliveryCharge($distance)
    {
        $slab = \DB::table('delivery_charges')
            ->where('min_km', '<=', $distance)
            ->where('max_km', '>=', $distance)
            ->first();

        return $slab ? $slab->charge : 0;
    }

    private function getGST()
    {
        return \DB::table('gst_settings')->first()->gst_percent ?? 0;
    }

    private function getPlatformFees()
    {
        return \DB::table('platform_fees')->first();
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
        $validated = $this->validateOrderData($request);

        DB::beginTransaction();

        try {
            // -----------------------------------------------------
            // 1. Fetch User Address (Snapshot copy)
            // -----------------------------------------------------
            $address = \App\Models\UserAddress::where('id', $validated['address_id'])
                ->where('user_id', $validated['user_id'])
                ->firstOrFail();

            // -----------------------------------------------------
            // 2. Total Amount (Items only)
            // -----------------------------------------------------
            $totalAmount = collect($validated['items'])->sum(function ($item) {
                return ($item['offer_price'] ?? $item['price']) * $item['quantity'];
            });

            // -----------------------------------------------------
            // 3. Fetch Shop Location
            // -----------------------------------------------------
            $shop = \App\Models\AppOwnerUser::where('shop_id', $validated['shop_id'])->first();

            if (!$shop) {
                throw new \Exception("Shop not found.");
            }

            // -----------------------------------------------------
            // 4. Calculate Distance (Haversine)
            // -----------------------------------------------------
            $distance = $this->calculateDistance(
                $address->lat,
                $address->lng,
                $shop->latitude,
                $shop->longitude
            );

            // -----------------------------------------------------
            // 5. Delivery Charge Based on Slab
            // -----------------------------------------------------
            $deliveryCharge = $this->getDeliveryCharge($distance);

            // -----------------------------------------------------
            // 6. Platform Fees (Handling + Packing)
            // -----------------------------------------------------
            $fees = $this->getPlatformFees();
            $handlingFee = $fees->handling_fee ?? 0;
            $packingFee  = $fees->packing_fee ?? 0;

            // -----------------------------------------------------
            // 7. GST
            // -----------------------------------------------------
            $gstPercent = $this->getGST();
            $taxAmount = ($totalAmount * $gstPercent) / 100;

            // -----------------------------------------------------
            // 8. Final Amount (items + tax + delivery + platform fees)
            // -----------------------------------------------------
            $finalAmount = $totalAmount + $taxAmount + $deliveryCharge + $handlingFee + $packingFee;

            // -----------------------------------------------------
            // 9. Create Order
            // -----------------------------------------------------
            $order = Order::create([
                'user_id'        => $validated['user_id'],
                'shop_id'        => $validated['shop_id'],
                'total_amount'   => $totalAmount,

                'gst_percent'    => $gstPercent,
                'tax_amount'     => $taxAmount,
                'delivery_charge'=> $deliveryCharge,
                'handling_fee'   => $handlingFee,
                'packing_fee'    => $packingFee,
                'final_amount'   => $finalAmount,

                'status'         => 'pending',

                // Address Snapshot
                'address_label'  => $address->label,
                'address_line'   => $address->address_line,
                'city'           => $address->city,
                'state'          => $address->state,
                'pincode'        => $address->pincode,
                'lat'            => $address->lat,
                'lng'            => $address->lng
            ]);

            // -----------------------------------------------------
            // 10. Add Order Items
            // -----------------------------------------------------
            foreach ($validated['items'] as $itemData) {
                $order->items()->create([
                    'item_id'     => $itemData['item_id'],
                    'quantity'    => $itemData['quantity'],
                    'price'       => $itemData['price'],
                    'offer_price' => $itemData['offer_price'] ?? null,
                    'item_total'  => ($itemData['offer_price'] ?? $itemData['price']) * $itemData['quantity']
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Order created successfully.',
                'data' => $order->load('items.item', 'owner')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
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
}
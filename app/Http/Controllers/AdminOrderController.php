<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Item;
use App\Models\DeliveryAssignment;
use App\Models\UserAddress;
use App\Models\AppOwnerUser;

class AdminOrderController extends Controller
{
    /* =====================================================
     | UTILITIES
     ===================================================== */

    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) * sin($dLng / 2);

        return $earthRadius * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    private function getDeliveryCharge($distance)
    {
        $slab = DB::table('delivery_charges')
            ->where('min_km', '<=', $distance)
            ->where('max_km', '>=', $distance)
            ->first();

        return $slab ? $slab->charge : 0;
    }

    private function getGST()
    {
        return DB::table('gst_settings')->first()->gst_percent ?? 0;
    }

    private function getPlatformFees()
    {
        return DB::table('platform_fees')->first();
    }

    /* =====================================================
     | VALIDATION
     ===================================================== */

    protected function validateOrderData(Request $request, $isUpdate = false)
    {
        $rules = [
            'user_id'    => 'required|integer|exists:app_users,id',
            'shop_id'    => 'required|integer|exists:app_owner_shops,shop_id',
            'address_id' => 'required|integer|exists:user_addresses,id',

            'items'               => 'required|array|min:1',
            'items.*.item_id'     => 'required|integer|exists:items,id',
            'items.*.quantity'    => 'required|integer|min:1',
            'items.*.price'       => 'required|numeric|min:0',
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

    /* =====================================================
     | CREATE ORDER
     ===================================================== */

    public function createOrder(Request $request)
    {
        $validated = $this->validateOrderData($request);

        DB::beginTransaction();

        try {
            // 1️⃣ Address snapshot
            $address = UserAddress::where('id', $validated['address_id'])
                ->where('user_id', $validated['user_id'])
                ->firstOrFail();

            // 2️⃣ Items total
            $totalAmount = collect($validated['items'])->sum(fn ($i) =>
                ($i['offer_price'] ?? $i['price']) * $i['quantity']
            );

            // 3️⃣ Shop
            $shop = AppOwnerUser::where('shop_id', $validated['shop_id'])->firstOrFail();

            // 4️⃣ Distance
            $distance = $this->calculateDistance(
                $address->lat,
                $address->lng,
                $shop->latitude,
                $shop->longitude
            );

            // 5️⃣ Charges
            $deliveryCharge = $this->getDeliveryCharge($distance);
            $fees = $this->getPlatformFees();

            $handlingFee = $fees->handling_fee ?? 0;
            $packingFee  = $fees->packing_fee ?? 0;

            // 6️⃣ GST
            $gstPercent = $this->getGST();
            $taxAmount = ($totalAmount * $gstPercent) / 100;

            // 7️⃣ Final
            $finalAmount = $totalAmount + $taxAmount + $deliveryCharge + $handlingFee + $packingFee;

            // 8️⃣ Create order
            $order = Order::create([
                'user_id'          => $validated['user_id'],
                'shop_id'          => $validated['shop_id'],
                'total_amount'     => $totalAmount,
                'gst_percent'      => $gstPercent,
                'tax_amount'       => $taxAmount,
                'delivery_charge'  => $deliveryCharge,
                'handling_fee'     => $handlingFee,
                'packing_fee'      => $packingFee,
                'final_amount'     => $finalAmount,
                'status'           => 'pending',

                // snapshot
                'address_label' => $address->label,
                'address_line'  => $address->address_line,
                'city'          => $address->city,
                'state'         => $address->state,
                'pincode'       => $address->pincode,
                'lat'           => $address->lat,
                'lng'           => $address->lng,
            ]);

            // 9️⃣ Order items
            foreach ($validated['items'] as $item) {
                $order->items()->create([
                    'item_id'     => $item['item_id'],
                    'quantity'    => $item['quantity'],
                    'price'       => $item['price'],
                    'offer_price' => $item['offer_price'] ?? null,
                    'item_total'  => ($item['offer_price'] ?? $item['price']) * $item['quantity'],
                ]);
            }

            DB::commit();

            // 🔥 ORDER TIMELINE
            addOrderTimeline(
                $order->id,
                'order_placed',
                'Order placed successfully'
            );

            return response()->json([
                'message' => 'Order created successfully',
                'data'    => $order->load('items.item', 'owner')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /* =====================================================
     | CANCEL ORDER
     ===================================================== */

    public function cancelOrder(Request $request, $orderId)
    {
        $validated = $request->validate([
            'cancel_reason_id' => 'required|integer|exists:cancel_reasons,id',
            'cancel_remark'    => 'nullable|string|max:500'
        ]);

        $order = Order::where('id', $orderId)
            ->where('status', '!=', 'cancelled')
            ->firstOrFail();

        $order->update([
            'status'            => 'cancelled',
            'cancel_reason_id'  => $validated['cancel_reason_id'],
            'cancel_remark'     => $validated['cancel_remark'] ?? null
        ]);

        // 🔥 TIMELINE
        addOrderTimeline(
            $order->id,
            'cancelled',
            'Order cancelled by admin',
            $validated
        );

        return response()->json([
            'message' => 'Order cancelled successfully'
        ]);
    }

    /* =====================================================
     | ADMIN PAGES
     ===================================================== */

    public function index()
    {
        $orders = Order::with([
            'items.item',
            'items.variant',
            'owner',
            'user'
        ])->latest()->paginate(10);

        return view('orders.orders', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::with([
            'items.item',
            'items.variant',
            'owner',
            'user',
            'cancelReason',
            'timeline',
            'deliveryAssignment.boy'
        ])->findOrFail($id);

        return view('orders.order_details', compact('order'));
    }
}
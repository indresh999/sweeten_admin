<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Order;
use App\Models\DeliveryBoy;
use App\Models\DeliveryAssignment;
use App\Models\DeliveryTimeline;
use App\Models\DeliveryEarning;
use App\Models\AppSetting;

class DeliveryController extends Controller
{
    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat/2)**2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng/2)**2;
        return $r * 2 * atan2(sqrt($a), sqrt(1-$a));
    }

    private function addTimeline(int $orderId, string $status, ?string $message = null): void
    {
        DeliveryTimeline::create(['order_id' => $orderId, 'status' => $status, 'message' => $message ?? ucfirst(str_replace('_',' ',$status)), 'created_at' => now()]);
    }

    private function findNearestBoy(Order $order, ?int $excludeId = null): ?DeliveryBoy
    {
        return DeliveryBoy::selectRaw('*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
                [$order->lat, $order->lng, $order->lat])
            ->where('status', 'online')
            ->where('is_verified', 1)
            ->whereColumn('current_active_orders', '<', 'max_active_orders')
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->orderBy('distance')
            ->first();
    }

    public function autoAssign(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id'   => 'required|exists:orders,id',
            'max_radius' => 'nullable|numeric|min:1',
            'max_boys'   => 'nullable|integer|min:1',
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);

        $order = Order::findOrFail($request->order_id);
        $boy = $this->findNearestBoy($order);

        if (!$boy) return response()->json(['status' => false, 'message' => 'No available delivery partner found'], 404);

        $assignment = DeliveryAssignment::updateOrCreate(
            ['order_id' => $order->id],
            ['delivery_boy_id' => $boy->id, 'status' => 'assigned', 'expected_delivery' => now()->addMinutes(45)]
        );
        $boy->increment('current_active_orders');
        $this->addTimeline($order->id, 'assigned', "Delivery partner {$boy->full_name} assigned");

        return response()->json(['status' => true, 'message' => 'Delivery partner assigned', 'assignment' => $assignment->load('boy')], 201);
    }

    public function manualAssign(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id'         => 'required|exists:orders,id',
            'delivery_boy_id'  => 'required|exists:delivery_boys,id',
            'expected_minutes' => 'nullable|integer|min:5|max:180',
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);

        $order = Order::findOrFail($request->order_id);
        $boy   = DeliveryBoy::findOrFail($request->delivery_boy_id);

        $assignment = DeliveryAssignment::updateOrCreate(
            ['order_id' => $order->id],
            ['delivery_boy_id' => $boy->id, 'status' => 'assigned', 'expected_delivery' => now()->addMinutes($request->expected_minutes ?? 45)]
        );
        $boy->increment('current_active_orders');
        $this->addTimeline($order->id, 'assigned', "Manually assigned to {$boy->full_name}");

        return response()->json(['status' => true, 'message' => 'Assigned', 'assignment' => $assignment->load('boy')], 201);
    }

    public function accept(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id'        => 'required|exists:orders,id',
            'delivery_boy_id' => 'required|exists:delivery_boys,id',
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);

        $assignment = DeliveryAssignment::where('order_id', $request->order_id)->where('delivery_boy_id', $request->delivery_boy_id)->firstOrFail();
        $assignment->update(['status' => 'accepted', 'accepted_at' => now()]);
        Order::where('id', $request->order_id)->update(['status' => 'confirmed']);
        $this->addTimeline($request->order_id, 'confirmed', 'Delivery partner accepted and is on the way to pick up');

        return response()->json(['status' => true, 'message' => 'Order accepted']);
    }

    public function reject(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id'        => 'required|exists:orders,id',
            'delivery_boy_id' => 'required|exists:delivery_boys,id',
            'reason'          => 'nullable|string|max:255',
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);

        $assignment = DeliveryAssignment::where('order_id', $request->order_id)->firstOrFail();
        $rejectedBoyId = $assignment->delivery_boy_id;
        $assignment->update(['status' => 'rejected', 'rejected_at' => now()]);
        DeliveryBoy::where('id', $rejectedBoyId)->decrement('current_active_orders');
        $this->addTimeline($request->order_id, 'reassigning', 'Finding another delivery partner');

        $order = Order::findOrFail($request->order_id);
        $newBoy = $this->findNearestBoy($order, $rejectedBoyId);
        if ($newBoy) {
            $assignment->update(['delivery_boy_id' => $newBoy->id, 'status' => 'assigned', 'rejected_at' => null]);
            $newBoy->increment('current_active_orders');
            $this->addTimeline($request->order_id, 'assigned', "Reassigned to {$newBoy->full_name}");
            return response()->json(['status' => true, 'message' => 'Rejected and reassigned', 'reassigned_to' => $newBoy->full_name]);
        }

        return response()->json(['status' => true, 'message' => 'Rejected. No reassignment partner available at this time.']);
    }

    public function picked(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id'        => 'required|exists:orders,id',
            'delivery_boy_id' => 'required|exists:delivery_boys,id',
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);

        $assignment = DeliveryAssignment::where('order_id', $request->order_id)->where('delivery_boy_id', $request->delivery_boy_id)->firstOrFail();
        $assignment->update(['status' => 'picked', 'picked_at' => now()]);
        Order::where('id', $request->order_id)->update(['status' => 'out_for_delivery']);
        $this->addTimeline($request->order_id, 'out_for_delivery', 'Order picked up and out for delivery');

        return response()->json(['status' => true, 'message' => 'Order picked up']);
    }

    public function delivered(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id'        => 'required|exists:orders,id',
            'delivery_boy_id' => 'required|exists:delivery_boys,id',
            'notes'           => 'nullable|string|max:500',
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);

        DB::beginTransaction();
        try {
            $assignment = DeliveryAssignment::where('order_id', $request->order_id)->where('delivery_boy_id', $request->delivery_boy_id)->firstOrFail();
            $assignment->update(['status' => 'delivered', 'delivered_at' => now()]);
            Order::where('id', $request->order_id)->update(['status' => 'delivered', 'payment_status' => 'paid']);
            DeliveryBoy::where('id', $request->delivery_boy_id)->decrement('current_active_orders');

            $baseEarning = (float) AppSetting::get('delivery_earn_per_order', 30);
            DeliveryEarning::create([
                'delivery_boy_id' => $request->delivery_boy_id,
                'order_id'        => $request->order_id,
                'base_earning'    => $baseEarning,
                'net_earning'     => $baseEarning,
            ]);

            $this->addTimeline($request->order_id, 'delivered', $request->notes ?? 'Order delivered successfully');
            DB::commit();
            return response()->json(['status' => true, 'message' => 'Order delivered']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function timeline(int $orderId): JsonResponse
    {
        $timeline = DeliveryTimeline::where('order_id', $orderId)->orderBy('created_at')->get();
        return response()->json(['status' => true, 'data' => $timeline]);
    }

    public function orderTracking(int $orderId): JsonResponse
    {
        $order = Order::with(['owner:shop_id,restaurant_name,restaurant_address,latitude,longitude', 'items', 'assignment.boy:id,full_name,phone_number,vehicle_type,picture,latitude,longitude'])->findOrFail($orderId);
        $timeline = DeliveryTimeline::where('order_id', $orderId)->orderBy('created_at')->get();
        return response()->json([
            'status' => true,
            'data'   => [
                'order_id'          => $order->id,
                'order_status'      => $order->status,
                'payment_method'    => $order->payment_method,
                'expected_delivery' => $order->expected_delivery_at,
                'shop'              => $order->owner,
                'delivery_partner'  => optional($order->assignment)->boy,
                'timeline'          => $timeline,
                'items_count'       => $order->items->count(),
                'final_amount'      => $order->final_amount,
            ],
        ]);
    }

    public function getOrderStatus(int $orderId): JsonResponse
    {
        $order = Order::select('id','status','payment_status','expected_delivery_at','updated_at')->findOrFail($orderId);
        return response()->json(['status' => true, 'data' => $order]);
    }

    public function deliveryBoyOrders(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'delivery_boy_id' => 'required|exists:delivery_boys,id',
            'status'          => 'nullable|string',
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);

        $assignments = DeliveryAssignment::with(['order.items', 'order.owner:shop_id,restaurant_name,restaurant_address'])
            ->where('delivery_boy_id', $request->delivery_boy_id)
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderByDesc('id')
            ->paginate(10);

        return response()->json(['status' => true, 'data' => $assignments]);
    }

    public function deliveryBoyEarnings(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'delivery_boy_id' => 'required|exists:delivery_boys,id',
            'period'          => 'nullable|in:today,week,month',
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);

        $query = DeliveryEarning::where('delivery_boy_id', $request->delivery_boy_id);
        switch ($request->get('period', 'today')) {
            case 'today':  $query->whereDate('created_at', today()); break;
            case 'week':   $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]); break;
            case 'month':  $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year); break;
        }

        $earnings = $query->get();
        return response()->json([
            'status' => true,
            'data'   => [
                'total_earned'    => $earnings->sum('net_earning'),
                'total_orders'    => $earnings->count(),
                'pending_payout'  => $earnings->where('is_paid', false)->sum('net_earning'),
                'transactions'    => $earnings,
            ],
        ]);
    }
}

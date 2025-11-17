<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\DeliveryBoy;
use App\Models\DeliveryAssignment;
use App\Models\DeliveryTimeline;
use Carbon\Carbon;
use DB;

class DeliveryController extends Controller
{
    /* ---------------------------------------------------------
       HELPER: distance (Haversine)
    --------------------------------------------------------- */
    private function distance($lat1, $lon1, $lat2, $lon2)
    {
        $earth = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);

        return $earth * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    /* ---------------------------------------------------------
       AUTO-ASSIGN DELIVERY BOY
    --------------------------------------------------------- */
    public function autoAssign(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'max_radius' => 'required|numeric',
            'max_boys' => 'required|numeric'
        ]);

        $order = Order::find($request->order_id);

        // Order must have snapshot lat/lng
        $orderLat = $order->lat;
        $orderLng = $order->lng;

        // Fetch all ONLINE delivery boys
        $boys = DeliveryBoy::where('status', 'online')->get();

        // Calculate distances
        $filtered = $boys->map(function ($b) use ($orderLat, $orderLng) {
            $b->distance = $this->distance($orderLat, $orderLng, $b->latitude, $b->longitude);
            return $b;
        })
        ->filter(function ($b) use ($request) {
            return $b->distance <= $request->max_radius &&
                   $b->current_active_orders < $b->max_active_orders;
        })
        ->sortBy('distance')
        ->take($request->max_boys)
        ->values();

        if ($filtered->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No available delivery boy found'
            ], 404);
        }

        $boy = $filtered->first();

        // Create or update assignment
        $assignment = DeliveryAssignment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'delivery_boy_id' => $boy->id,
                'status' => 'assigned',
                'expected_delivery' => now()->addMinutes(45)
            ]
        );

        // Increase active orders
        $boy->increment('current_active_orders');

        // Add timeline
        DeliveryTimeline::create([
            'order_id' => $order->id,
            'status' => 'assigned',
            'message' => 'Delivery auto-assigned to ' . $boy->full_name
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Delivery boy auto-assigned',
            'assignment' => $assignment
        ], 201);
    }

    /* ---------------------------------------------------------
       MANUAL ASSIGN
    --------------------------------------------------------- */
    public function manualAssign(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'delivery_boy_id' => 'required|exists:delivery_boys,id',
            'expected_minutes' => 'nullable|numeric'
        ]);

        $order = Order::find($request->order_id);
        $boy = DeliveryBoy::find($request->delivery_boy_id);

        $assignment = DeliveryAssignment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'delivery_boy_id' => $boy->id,
                'status' => 'assigned',
                'expected_delivery' => now()->addMinutes($request->expected_minutes ?? 45)
            ]
        );

        // increment active orders
        $boy->increment('current_active_orders');

        // timeline
        DeliveryTimeline::create([
            'order_id' => $order->id,
            'status' => 'assigned',
            'message' => 'Manually assigned to ' . $boy->full_name
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Delivery boy manually assigned',
            'assignment' => $assignment
        ], 201);
    }

    /* ---------------------------------------------------------
       ACCEPT ASSIGNMENT
    --------------------------------------------------------- */
    public function accept(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'delivery_boy_id' => 'required|exists:delivery_boys,id'
        ]);

        $assignment = DeliveryAssignment::where('order_id', $request->order_id)->firstOrFail();

        $assignment->update([
            'status' => 'accepted',
            'accepted_at' => now()
        ]);

        DeliveryTimeline::create([
            'order_id' => $request->order_id,
            'status' => 'accepted',
            'message' => 'Delivery boy accepted assignment'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Assignment accepted'
        ], 200);
    }

    /* ---------------------------------------------------------
       REJECT ASSIGNMENT → Auto Reassign
    --------------------------------------------------------- */
public function reject(Request $request)
{
    $request->validate([
        'order_id' => 'required|exists:orders,id',
        'delivery_boy_id' => 'required|exists:delivery_boys,id',
        'reason' => 'nullable|string'
    ]);

    $assignment = DeliveryAssignment::where('order_id', $request->order_id)->firstOrFail();

    // 1️⃣ Mark as rejected
    $assignment->update([
        'status'      => 'rejected',
        'rejected_at' => now()
    ]);

    // 2️⃣ decrease active orders
    DeliveryBoy::where('id', $assignment->delivery_boy_id)
        ->decrement('current_active_orders');

    // 3️⃣ Add timeline
    DeliveryTimeline::create([
        'order_id' => $request->order_id,
        'status'   => 'rejected',
        'message'  => 'Delivery boy rejected order'
    ]);

    // ⭐ 4️⃣ DO NOT OVERWRITE STATUS NOW
    //     Attempt reassign but DO NOT overwrite current assignment
    $reassign = $this->autoAssignInternalWithoutOverwrite($request->order_id);

    return response()->json([
        'status'        => true,
        'message'       => 'Order rejected & reassignment processed',
        'reassignment'  => $reassign
    ], 200);
}

    /* ---------------------------------------------------------
       PICKED UP
    --------------------------------------------------------- */
    public function picked(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'delivery_boy_id' => 'required|exists:delivery_boys,id'
        ]);

        $assignment = DeliveryAssignment::where('order_id', $request->order_id)->firstOrFail();

        $assignment->update([
            'status' => 'picked',
            'picked_at' => now()
        ]);

        Order::where('id', $request->order_id)->update([
            'status' => 'out_for_delivery'
        ]);

        DeliveryTimeline::create([
            'order_id' => $request->order_id,
            'status' => 'picked',
            'message' => 'Order picked by delivery boy'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Order picked'
        ], 200);
    }

    /* ---------------------------------------------------------
       DELIVERED
    --------------------------------------------------------- */
    public function delivered(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'delivery_boy_id' => 'required|exists:delivery_boys,id',
            'notes' => 'nullable|string'
        ]);

        $assignment = DeliveryAssignment::where('order_id', $request->order_id)->firstOrFail();

        $assignment->update([
            'status' => 'delivered',
            'delivered_at' => now()
        ]);

        Order::where('id', $request->order_id)->update([
            'status' => 'delivered'
        ]);

        $boy = DeliveryBoy::find($request->delivery_boy_id);
        $boy->decrement('current_active_orders');

        DeliveryTimeline::create([
            'order_id' => $request->order_id,
            'status' => 'delivered',
            'message' => $request->notes ?? 'Delivered'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Order delivered'
        ], 200);
    }

    /* ---------------------------------------------------------
       GET TIMELINE
    --------------------------------------------------------- */
    public function timeline($orderId)
    {
        $timeline = DeliveryTimeline::where('order_id', $orderId)
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $timeline
        ], 200);
    }
  private function autoAssignInternal($orderId)
{
    $order = Order::findOrFail($orderId);

    // Use ORDER snapshot location (user address)
    $lat = $order->lat;
    $lng = $order->lng;

    $boys = DeliveryBoy::select('*')
        ->selectRaw("
            (6371 * acos(
                cos(radians(?)) *
                cos(radians(latitude)) *
                cos(radians(longitude) - radians(?)) +
                sin(radians(?)) *
                sin(radians(latitude))
            )) AS distance
        ", [$lat, $lng, $lat])
        ->where('status', 'online')
        ->whereColumn('current_active_orders', '<', 'max_active_orders')
        ->orderBy('distance')
        ->limit(1)
        ->get();

    if ($boys->isEmpty()) {
        return ['assigned' => false, 'message' => 'No delivery boys available'];
    }

    $boy = $boys->first();

    DeliveryAssignment::updateOrCreate(
        ['order_id' => $orderId],
        [
            'delivery_boy_id' => $boy->id,
            'status' => 'assigned',
            'expected_delivery' => now()->addMinutes(30)
        ]
    );

    $boy->increment('current_active_orders');

    $this->addTimeline($orderId, 'assigned', 'Delivery boy reassigned');

    return ['assigned' => true, 'delivery_boy_id' => $boy->id];
}

private function addTimeline($orderId, $status, $message)
{
    DeliveryTimeline::create([
        'order_id' => $orderId,
        'status'   => $status,
        'message'  => $message,
    ]);
}

private function autoAssignInternalWithoutOverwrite($orderId)
{
    $order = Order::findOrFail($orderId);

    // get old assignment boy (who rejected)
    $currentAssignment = DeliveryAssignment::where('order_id', $orderId)->first();
    $rejectedBoyId = $currentAssignment->delivery_boy_id;

    $lat = $order->lat;
    $lng = $order->lng;

    $boy = DeliveryBoy::select('*')
        ->selectRaw("
            (6371 * acos(
                cos(radians(?)) *
                cos(radians(latitude)) *
                cos(radians(longitude) - radians(?)) +
                sin(radians(?)) *
                sin(radians(latitude))
            )) AS distance
        ", [$lat, $lng, $lat])
        ->where('status', 'online')
        ->where('id', '!=', $rejectedBoyId)     // ⭐ EXCLUDE REJECTING BOY
        ->whereColumn('current_active_orders','<','max_active_orders')
        ->orderBy('distance')
        ->first();

    if (!$boy) {
        return ['assigned' => false, 'message' => 'No delivery boy available'];
    }

    // switch boy but DO NOT touch status
    DeliveryAssignment::where('order_id', $orderId)
        ->update([
            'delivery_boy_id' => $boy->id,
        ]);

    $boy->increment('current_active_orders');

    DeliveryTimeline::create([
        'order_id' => $orderId,
        'status' => 'assigned',
        'message' => 'Delivery boy reassigned (background)'
    ]);

    return ['assigned' => true, 'delivery_boy_id' => $boy->id];
}
}
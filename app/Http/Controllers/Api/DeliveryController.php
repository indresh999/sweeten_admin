<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\DeliveryBoy;
use App\Models\DeliveryAssignment;
use App\Models\DeliveryTimeline;
use App\Models\DeliveryEarning;
use App\Models\DeliveryRejectReason;
use App\Models\AppSetting;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Mail;
use App\Mail\DeliveryOrderAssignedMail;
use App\Mail\DeliveryOrderDeliveredMail;

class DeliveryController extends Controller
{
    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r    = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a    = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $r * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function addTimeline(int $orderId, string $status, ?string $message = null): void
    {
        DeliveryTimeline::create([
            'order_id'   => $orderId,
            'status'     => $status,
            'message'    => $message ?? ucfirst(str_replace('_', ' ', $status)),
            'created_at' => now(),
        ]);
    }

    /**
     * Find nearest online, verified delivery boy with capacity.
     * Excludes IDs in $excludeIds array.
     */
    private function findNearestBoy(Order $order, array $excludeIds = []): ?DeliveryBoy
    {
        $maxRadius = (float) AppSetting::get('delivery_max_boy_radius_km', 15);

        return DeliveryBoy::selectRaw(
            '*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
            [$order->lat, $order->lng, $order->lat]
        )
        ->where('status', 'online')
        ->where('is_verified', 1)
        ->whereColumn('current_active_orders', '<', 'max_active_orders')
        ->when(!empty($excludeIds), fn($q) => $q->whereNotIn('id', $excludeIds))
        ->having('distance', '<=', $maxRadius)
        ->orderBy('distance')
        ->first();
    }

    /**
     * Find next available boy and assign (cascade on reject).
     * Returns the new boy if assigned, null otherwise.
     */
    private function cascadeAssign(
        Order $order,
        DeliveryAssignment $currentAssignment,
        int $excludeBoyId
    ): ?DeliveryBoy {
        // Collect all previously attempted boy IDs for this order
        $attemptedIds = DeliveryAssignment::where('order_id', $order->id)
            ->whereNotNull('delivery_boy_id')
            ->pluck('delivery_boy_id')
            ->unique()
            ->values()
            ->toArray();

        // Always exclude the current rejecting boy + all previously tried
        $excludeIds = array_unique(array_merge($attemptedIds, [$excludeBoyId]));

        $newBoy = $this->findNearestBoy($order, $excludeIds);

        if ($newBoy) {
            $currentAssignment->update([
                'delivery_boy_id'   => $newBoy->id,
                'status'            => 'assigned',
                'assigned_at'       => now(),
                'rejected_at'       => null,
                'reject_reason_id'  => null,
                'reject_remark'     => null,
                'attempts'          => $currentAssignment->attempts + 1,
            ]);
            $newBoy->increment('current_active_orders');

            $attemptNum = $currentAssignment->attempts;
            $this->addTimeline(
                $order->id,
                'assigned',
                "Reassigned to {$newBoy->full_name} (attempt #{$attemptNum})"
            );

            // Notify the new boy
            NotificationService::send(
                $newBoy->id,
                'New Order Assignment! 🛵',
                "You have a new delivery order (Order #{$order->id}). Tap to accept or reject.",
                'delivery_order',
                'order',
                $order->id,
                'delivery_boy'
            );

            // Email notification (best-effort)
            try {
                $shopName = $order->owner->restaurant_name ?? 'Shop';
                Mail::to($newBoy->email)->send(new DeliveryOrderAssignedMail(
                    $newBoy->full_name,
                    $order->id,
                    $shopName,
                    $order->address_line ?? '',
                    (float) $order->final_amount
                ));
            } catch (\Exception $e) {
                Log::error('[Delivery] Reassignment email failed: ' . $e->getMessage());
            }

            return $newBoy;
        }

        return null;
    }

    // ── Get Reject Reasons ────────────────────────────────
    public function getRejectReasons(): JsonResponse
    {
        $reasons = DeliveryRejectReason::active()
            ->orderBy('sort_order')
            ->get(['id', 'reason']);

        return response()->json(['status' => true, 'data' => $reasons]);
    }

    // ── Auto Assign ────────────────────────────────────────
    public function autoAssign(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $order = Order::findOrFail($request->order_id);
        $boy   = $this->findNearestBoy($order);

        if (!$boy) {
            return response()->json(['status' => false, 'message' => 'No available delivery partner found nearby.'], 404);
        }

        $assignment = DeliveryAssignment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'delivery_boy_id'   => $boy->id,
                'status'            => 'assigned',
                'assigned_at'       => now(),
                'attempts'          => 1,
                'expected_delivery' => now()->addMinutes(45),
            ]
        );
        $boy->increment('current_active_orders');
        $this->addTimeline($order->id, 'assigned', "Delivery partner {$boy->full_name} assigned");

        // Notify via DB notification
        NotificationService::send(
            $boy->id,
            'New Order Assignment! 🛵',
            "You have a new delivery order (Order #{$order->id}). Tap to accept or reject.",
            'delivery_order',
            'order',
            $order->id,
            'delivery_boy'
        );

        // Email notification
        try {
            $shopName = $order->owner->restaurant_name ?? 'Shop';
            Mail::to($boy->email)->send(new DeliveryOrderAssignedMail(
                $boy->full_name,
                $order->id,
                $shopName,
                $order->address_line ?? '',
                (float) $order->final_amount
            ));
        } catch (\Exception $e) {
            Log::error('[Delivery] Order assigned email failed: ' . $e->getMessage());
        }

        // Notify vendor
        NotificationService::send(
            $order->shop_id ?? 0,
            'Delivery Partner Assigned',
            "Delivery partner {$boy->full_name} has been assigned to Order #{$order->id}.",
            'order',
            'order',
            $order->id,
            'shop_owner'
        );

        return response()->json([
            'status'     => true,
            'message'    => 'Delivery partner assigned.',
            'assignment' => $assignment->load('boy'),
        ], 201);
    }

    // ── Manual Assign ──────────────────────────────────────
    public function manualAssign(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id'         => 'required|exists:orders,id',
            'delivery_boy_id'  => 'required|exists:delivery_boys,id',
            'expected_minutes' => 'nullable|integer|min:5|max:180',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $order = Order::findOrFail($request->order_id);
        $boy   = DeliveryBoy::findOrFail($request->delivery_boy_id);

        $assignment = DeliveryAssignment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'delivery_boy_id'   => $boy->id,
                'status'            => 'assigned',
                'assigned_at'       => now(),
                'attempts'          => 1,
                'expected_delivery' => now()->addMinutes($request->expected_minutes ?? 45),
            ]
        );
        $boy->increment('current_active_orders');
        $this->addTimeline($order->id, 'assigned', "Manually assigned to {$boy->full_name}");

        // Notify boy
        NotificationService::send(
            $boy->id,
            'New Order Assignment! 🛵',
            "You have a new delivery order (Order #{$order->id}). Tap to accept or reject.",
            'delivery_order',
            'order',
            $order->id,
            'delivery_boy'
        );

        return response()->json([
            'status'     => true,
            'message'    => 'Assigned successfully.',
            'assignment' => $assignment->load('boy'),
        ], 201);
    }

    // ── Accept ─────────────────────────────────────────────
    public function accept(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $boyId = $request->user()->id;
        $boy = DeliveryBoy::find($boyId);

        if ($boy && $boy->has_pending_submission) {
            return response()->json([
                'status'  => false,
                'message' => 'Cannot accept orders. You have a pending payment submission.',
            ], 400);
        }

        $assignment = DeliveryAssignment::where('order_id', $request->order_id)
            ->where('delivery_boy_id', $boyId)
            ->where('status', 'assigned')
            ->firstOrFail();

        $assignment->update(['status' => 'accepted', 'accepted_at' => now()]);
        $order = Order::where('id', $request->order_id)->firstOrFail();
        $order->update(['status' => 'confirmed']);
        $this->addTimeline($request->order_id, 'confirmed', 'Order confirmed — delivery partner is heading to the store');
        NotificationService::orderConfirmed($order->user_id, $order->id);

        return response()->json(['status' => true, 'message' => 'Order accepted.']);
    }

    // ── Reject ─────────────────────────────────────────────
    public function reject(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id'          => 'required|exists:orders,id',
            'reject_reason_id'  => 'required|exists:delivery_reject_reasons,id',
            'reject_remark'     => 'nullable|string|max:500',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $rejectedBoyId = $request->user()->id;

        $assignment = DeliveryAssignment::where('order_id', $request->order_id)
            ->where('delivery_boy_id', $rejectedBoyId)
            ->where('status', 'assigned')
            ->firstOrFail();

        $order = Order::findOrFail($request->order_id);

        // Record rejection
        $assignment->update([
            'status'            => 'rejected',
            'rejected_at'       => now(),
            'reject_reason_id'  => $request->reject_reason_id,
            'reject_remark'     => $request->reject_remark,
        ]);
        DeliveryBoy::where('id', $rejectedBoyId)->decrement('current_active_orders');

        $this->addTimeline(
            $order->id,
            'reassigning',
            "Delivery partner rejected. Finding another partner..."
        );

        // Try to cascade to next available boy
        $newBoy = $this->cascadeAssign($order, $assignment, $rejectedBoyId);

        if ($newBoy) {
            return response()->json([
                'status'       => true,
                'message'      => 'Rejected and reassigned to ' . $newBoy->full_name . '.',
                'reassigned'   => true,
                'reassigned_to' => $newBoy->full_name,
            ]);
        }

        // No more boys available — mark as pending reassignment
        $this->addTimeline($order->id, 'waiting', 'No delivery partners available. Waiting for new partners to come online.');

        return response()->json([
            'status'     => true,
            'message'    => 'Rejected. No delivery partners available right now. Order will be reassigned when a partner becomes available.',
            'reassigned' => false,
        ]);
    }

    // ── Picked Up ──────────────────────────────────────────
    public function picked(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $boyId = $request->user()->id;
        $assignment = DeliveryAssignment::where('order_id', $request->order_id)
            ->where('delivery_boy_id', $boyId)
            ->firstOrFail();
        $assignment->update(['status' => 'picked', 'picked_at' => now()]);

        $order = Order::where('id', $request->order_id)->firstOrFail();
        $order->update(['status' => 'out_for_delivery']);
        $this->addTimeline($request->order_id, 'out_for_delivery', 'Order picked up and out for delivery');
        NotificationService::orderOutForDelivery($order->user_id, $order->id);

        return response()->json(['status' => true, 'message' => 'Order picked up.']);
    }

    // ── Delivered ──────────────────────────────────────────
    public function delivered(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'notes'    => 'nullable|string|max:500',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $boyId = $request->user()->id;

        DB::beginTransaction();
        try {
            $assignment = DeliveryAssignment::where('order_id', $request->order_id)
                ->where('delivery_boy_id', $boyId)
                ->firstOrFail();
            $assignment->update(['status' => 'delivered', 'delivered_at' => now()]);

            $order = Order::where('id', $request->order_id)->firstOrFail();
            $order->update(['status' => 'delivered']);
            DeliveryBoy::where('id', $boyId)->decrement('current_active_orders');

            if ($order->payment_method === 'cod') {
                $order->update(['payment_status' => 'paid']);
                DeliveryBoy::where('id', $boyId)->increment('wallet_collected', (float) $order->final_amount);
            }

            $baseEarning = (float) AppSetting::get('delivery_earn_per_order', 30);
            DeliveryEarning::create([
                'delivery_boy_id' => $boyId,
                'order_id'        => $request->order_id,
                'base_earning'    => $baseEarning,
                'net_earning'     => $baseEarning,
                'is_paid'         => false,
            ]);

            $this->addTimeline($request->order_id, 'delivered', $request->notes ?? 'Order delivered successfully');
            NotificationService::orderDelivered($order->user_id, $order->id);

            try {
                $boy = DeliveryBoy::find($boyId);
                Mail::to($boy->email)->send(new DeliveryOrderDeliveredMail(
                    $boy->full_name,
                    $order->id,
                    $baseEarning
                ));
            } catch (\Exception $e) {
                Log::error('[Delivery] Order delivered email failed: ' . $e->getMessage());
            }

            DB::commit();
            Log::info('[Delivery] Order #' . $request->order_id . ' delivered by boy #' . $boyId);
            return response()->json(['status' => true, 'message' => 'Order marked as delivered.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[Delivery] delivered failed: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to mark as delivered.'], 500);
        }
    }

    // ── Timeline ───────────────────────────────────────────
    public function timeline(int $orderId): JsonResponse
    {
        $timeline = DeliveryTimeline::where('order_id', $orderId)->orderBy('created_at')->get();
        return response()->json(['status' => true, 'data' => $timeline]);
    }

    // ── Order Tracking (Customer view) ─────────────────────
    public function orderTracking(int $orderId): JsonResponse
    {
        $order = Order::with([
            'owner:shop_id,restaurant_name,restaurant_address,latitude,longitude',
            'items',
            'assignment.boy:id,full_name,phone_number,vehicle_type,latitude,longitude',
        ])->findOrFail($orderId);

        $timeline = DeliveryTimeline::where('order_id', $orderId)->orderBy('created_at')->get();

        return response()->json([
            'status' => true,
            'data'   => [
                'order_id'          => $order->id,
                'order_status'      => $order->status,
                'payment_method'    => $order->payment_method,
                'payment_status'    => $order->payment_status,
                'expected_delivery' => $order->expected_delivery_at,
                'shop'              => $order->owner,
                'delivery_partner'  => optional($order->assignment)->boy,
                'timeline'          => $timeline,
                'items_count'       => $order->items->count(),
                'final_amount'      => $order->final_amount,
                'address'           => [
                    'label'        => $order->address_label,
                    'address_line' => $order->address_line,
                    'city'         => $order->city,
                    'state'        => $order->state,
                    'pincode'      => $order->pincode,
                ],
            ],
        ]);
    }

    // ── Order Status (lightweight poll) ────────────────────
    public function getOrderStatus(int $orderId): JsonResponse
    {
        $order = Order::select('id', 'status', 'payment_status', 'expected_delivery_at', 'updated_at')
            ->findOrFail($orderId);
        return response()->json(['status' => true, 'data' => $order]);
    }

    // ── Pending Assignment (poll for new order) ────────────
    public function pendingAssignment(Request $request): JsonResponse
    {
        $boyId = $request->user()->id;
        $boy = DeliveryBoy::find($boyId);

        if ($boy && $boy->has_pending_submission) {
            return response()->json([
                'status'       => true,
                'data'         => null,
                'blocked'      => true,
                'block_reason' => 'You have a pending payment submission. Please wait for admin approval before accepting new orders.',
            ]);
        }

        $assignment = DeliveryAssignment::with([
            'order:id,status,final_amount,address_line,city,lat,lng',
            'order.owner:shop_id,restaurant_name,restaurant_address,latitude,longitude',
            'order.items:id,order_id,item_name,qty,price',
        ])
        ->where('delivery_boy_id', $boyId)
        ->where('status', 'assigned')
        ->latest()
        ->first();

        if (!$assignment) {
            return response()->json(['status' => true, 'data' => null]);
        }

        return response()->json(['status' => true, 'data' => $assignment]);
    }

    // ── Delivery Boy's Orders ──────────────────────────────
    public function deliveryBoyOrders(Request $request): JsonResponse
    {
        $boyId = $request->user()->id;

        $assignments = DeliveryAssignment::with([
            'order:id,status,final_amount,address_line,city',
            'order.owner:shop_id,restaurant_name,restaurant_address',
        ])
        ->where('delivery_boy_id', $boyId)
        ->when($request->status, fn($q) => $q->where('status', $request->status))
        ->orderByDesc('id')
        ->paginate(15);

        return response()->json(['status' => true, 'data' => $assignments]);
    }

    // ── Delivery Boy Earnings ──────────────────────────────
    public function deliveryBoyEarnings(Request $request): JsonResponse
    {
        $boyId  = $request->user()->id;
        $period = $request->get('period', 'today');

        $query = DeliveryEarning::where('delivery_boy_id', $boyId);

        match ($period) {
            'week'  => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'month' => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
            default => $query->whereDate('created_at', today()),
        };

        $earnings = $query->orderByDesc('created_at')->get();
        $allTime  = DeliveryEarning::where('delivery_boy_id', $boyId);

        return response()->json([
            'status' => true,
            'data'   => [
                'period'          => $period,
                'total_earned'    => $earnings->sum('net_earning'),
                'total_orders'    => $earnings->count(),
                'pending_payout'  => $earnings->where('is_paid', false)->sum('net_earning'),
                'all_time_earned' => (clone $allTime)->sum('net_earning'),
                'all_time_orders' => (clone $allTime)->count(),
                'transactions'    => $earnings,
            ],
        ]);
    }
}

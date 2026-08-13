<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\DeliveryAssignment;
use App\Models\DeliveryTimeline;
use App\Models\CancelReason;
use App\Services\NotificationService;

class CancelUnassignedOrders extends Command
{
    protected $signature = 'orders:cancel-unassigned {--dry-run}';
    protected $description = 'Auto-cancel orders not accepted by any delivery boy within the configured timeout';

    public function handle(): int
    {
        $timeoutMinutes = 180; // default 3 hours
        try {
            $setting = DB::table('app_settings')->where('key', 'delivery_order_timeout_minutes')->value('value');
            if ($setting) $timeoutMinutes = (int) $setting;
        } catch (\Exception $e) {
            // Table may not exist — use default
        }

        $cutoff = now()->subMinutes($timeoutMinutes);

        // Find assignments that are still 'assigned' and were assigned before the cutoff
        $staleAssignments = DeliveryAssignment::where('status', 'assigned')
            ->whereNotNull('assigned_at')
            ->where('assigned_at', '<=', $cutoff)
            ->with('order')
            ->get();

        if ($staleAssignments->isEmpty()) {
            $this->info('No unassigned orders past timeout.');
            return self::SUCCESS;
        }

        $cancelReasonId = CancelReason::where('reason', 'like', '%delivery%unavailable%')
            ->orWhere('reason', 'like', '%no delivery%')
            ->value('id') ?? CancelReason::first()->id ?? 1;

        foreach ($staleAssignments as $assignment) {
            $order = $assignment->order;
            if (!$order || in_array($order->status, ['cancelled', 'delivered'])) {
                continue;
            }

            if ($this->option('dry-run')) {
                $this->info("[DRY RUN] Would cancel Order #{$order->id} (assigned at {$assignment->assigned_at})");
                continue;
            }

            DB::beginTransaction();
            try {
                // Mark assignment as cancelled
                $assignment->update(['status' => 'cancelled']);

                // Decrement delivery boy's active orders
                if ($assignment->delivery_boy_id) {
                    DB::table('delivery_boys')
                        ->where('id', $assignment->delivery_boy_id)
                        ->decrement('current_active_orders');
                }

                // Cancel the order
                $order->update([
                    'status'           => 'cancelled',
                    'cancel_reason_id' => $cancelReasonId,
                    'cancel_remark'    => "No delivery partner accepted within {$timeoutMinutes} minutes. Order auto-cancelled.",
                ]);

                // Refund wallet if used
                if ($order->wallet_used > 0) {
                    $user = $order->user;
                    if ($user) {
                        $wallet = $user->getOrCreateWallet();
                        if ($wallet) {
                            $wallet->credit(
                                $order->wallet_used,
                                "Refund for Order #{$order->id} - No delivery partner",
                                'order_refund',
                                $order->id
                            );
                        }
                    }
                }

                // Timeline
                DeliveryTimeline::create([
                    'order_id'   => $order->id,
                    'status'     => 'cancelled',
                    'message'    => "No delivery partner accepted within {$timeoutMinutes} minutes. Order auto-cancelled.",
                    'created_at' => now(),
                ]);

                // Notify customer
                NotificationService::orderCancelled($order->user_id, $order->id);

                DB::commit();
                $this->info("Cancelled Order #{$order->id}");
                Log::info("[Order] Auto-cancelled Order #{$order->id} - no delivery partner within {$timeoutMinutes} min");
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Failed to cancel Order #{$order->id}: {$e->getMessage()}");
                Log::error("[Order] Auto-cancel failed for Order #{$order->id}: {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}

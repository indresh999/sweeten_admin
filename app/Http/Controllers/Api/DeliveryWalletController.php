<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Models\DeliveryBoy;
use App\Models\DeliveryCashSubmission;
use App\Models\DeliveryAssignment;
use App\Models\Order;
use App\Mail\DeliveryCashSubmissionMail;
use App\Mail\DeliveryCashApprovedMail;
use App\Mail\DeliveryCashRejectedMail;

class DeliveryWalletController extends Controller
{
    // ── Get Wallet Info ──────────────────────────────────────────────
    public function walletInfo(Request $request): JsonResponse
    {
        $boy = $request->user();

        // Check if there's a pending submission from yesterday or earlier
        $this->checkPendingSubmissions($boy);

        $pendingSubmission = DeliveryCashSubmission::where('delivery_boy_id', $boy->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        $todaySubmission = DeliveryCashSubmission::where('delivery_boy_id', $boy->id)
            ->where('submission_date', today()->toDateString())
            ->first();

        $totalSubmitted = DeliveryCashSubmission::where('delivery_boy_id', $boy->id)
            ->where('status', 'approved')
            ->sum('amount');

        return response()->json([
            'status' => true,
            'data'   => [
                'wallet_limit'          => (float) $boy->wallet_limit,
                'wallet_collected'      => (float) $boy->wallet_collected,
                'wallet_remaining'      => max(0, (float) $boy->wallet_limit - (float) $boy->wallet_collected),
                'has_pending_submission'=> $boy->has_pending_submission,
                'can_take_orders'       => !$boy->has_pending_submission,
                'pending_submission'    => $pendingSubmission,
                'today_submission'      => $todaySubmission,
                'total_submitted'       => (float) $totalSubmitted,
                'submission_history'    => DeliveryCashSubmission::where('delivery_boy_id', $boy->id)
                    ->orderByDesc('created_at')
                    ->limit(20)
                    ->get(),
            ],
        ]);
    }

    // ── Submit Cash Payment ─────────────────────────────────────────
    public function submitCash(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount'     => 'required|numeric|min:1',
            'screenshot' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $boy = $request->user();

        // Refresh pending status
        $this->checkPendingSubmissions($boy);
        $boy->refresh();

        if ($boy->has_pending_submission) {
            return response()->json([
                'status'  => false,
                'message' => 'You have a pending submission from a previous day. Please wait for admin approval.',
            ], 400);
        }

        // Check if already submitted today
        $todaySubmission = DeliveryCashSubmission::where('delivery_boy_id', $boy->id)
            ->where('submission_date', today()->toDateString())
            ->first();
        if ($todaySubmission) {
            return response()->json([
                'status'  => false,
                'message' => 'You have already submitted payment for today.',
            ], 400);
        }

        // Check amount doesn't exceed collected
        if ($request->amount > $boy->wallet_collected) {
            return response()->json([
                'status'  => false,
                'message' => 'Amount cannot exceed collected cash (₹' . $boy->wallet_collected . ').',
            ], 400);
        }

        // Upload screenshot
        $path = $request->file('screenshot')->store("cash_submissions/{$boy->id}", 'public');

        DB::beginTransaction();
        try {
            $submission = DeliveryCashSubmission::create([
                'delivery_boy_id' => $boy->id,
                'amount'          => $request->amount,
                'screenshot_path' => $path,
                'status'          => 'pending',
                'submission_date' => today()->toDateString(),
            ]);

            // Deduct from wallet collected
            $boy->update([
                'wallet_collected'      => max(0, (float) $boy->wallet_collected - $request->amount),
                'has_pending_submission'=> true,
            ]);

            // Send email to admin (optional — we'll notify the delivery boy)
            try {
                Mail::to($boy->email)->send(new DeliveryCashSubmissionMail(
                    $boy->full_name,
                    $request->amount,
                    today()->toDateString()
                ));
            } catch (\Exception $e) {
                Log::error('[DeliveryWallet] Submission email failed: ' . $e->getMessage());
            }

            DB::commit();
            return response()->json([
                'status'  => true,
                'message' => 'Payment submitted successfully! Awaiting admin verification.',
                'data'    => $submission,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[DeliveryWallet] Submit cash failed: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to submit. Try again.'], 500);
        }
    }

    // ── Admin: List All Submissions ──────────────────────────────────
    public function adminListSubmissions(Request $request): JsonResponse
    {
        $status = $request->get('status', 'pending');
        $query = DeliveryCashSubmission::with('boy:id,full_name,email,phone_number');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $submissions = $query->orderByDesc('created_at')->paginate(20);
        return response()->json(['status' => true, 'data' => $submissions]);
    }

    // ── Admin: Verify (Approve/Reject) Submission ───────────────────
    public function adminVerifySubmission(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:approved,rejected',
            'notes'  => 'nullable|string|max:500',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $submission = DeliveryCashSubmission::findOrFail($id);
        $boy = DeliveryBoy::findOrFail($submission->delivery_boy_id);

        DB::beginTransaction();
        try {
            $submission->update([
                'status'      => $request->action,
                'admin_notes' => $request->notes,
                'verified_at' => now(),
            ]);

            // Clear pending flag
            $hasOtherPending = DeliveryCashSubmission::where('delivery_boy_id', $boy->id)
                ->where('status', 'pending')
                ->where('id', '!=', $id)
                ->exists();

            $boy->update(['has_pending_submission' => $hasOtherPending]);

            // Send email
            try {
                if ($request->action === 'approved') {
                    Mail::to($boy->email)->send(new DeliveryCashApprovedMail(
                        $boy->full_name,
                        $submission->amount,
                        $submission->submission_date
                    ));
                } else {
                    Mail::to($boy->email)->send(new DeliveryCashRejectedMail(
                        $boy->full_name,
                        $submission->amount,
                        $request->notes ?? 'No reason provided'
                    ));
                }
            } catch (\Exception $e) {
                Log::error('[DeliveryWallet] Verify email failed: ' . $e->getMessage());
            }

            DB::commit();
            return response()->json([
                'status'  => true,
                'message' => 'Submission ' . $request->action . '.',
                'data'    => $submission->refresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[DeliveryWallet] Verify failed: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Verification failed.'], 500);
        }
    }

    // ── Admin: Set Wallet Limit ─────────────────────────────────────
    public function adminSetWalletLimit(Request $request, int $boyId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'wallet_limit' => 'required|numeric|min:0',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $boy = DeliveryBoy::findOrFail($boyId);
        $boy->update(['wallet_limit' => $request->wallet_limit]);

        return response()->json([
            'status'  => true,
            'message' => 'Wallet limit updated to ₹' . $request->wallet_limit,
            'data'    => [
                'wallet_limit'     => (float) $boy->wallet_limit,
                'wallet_collected' => (float) $boy->wallet_collected,
            ],
        ]);
    }

    // ── Admin: List All Delivery Boys with Wallet Info ──────────────
    public function adminListBoys(Request $request): JsonResponse
    {
        $boys = DeliveryBoy::select(
            'id', 'full_name', 'email', 'phone_number', 'status',
            'wallet_limit', 'wallet_collected', 'has_pending_submission'
        )
        ->withSum([
            fn($q) => $q->where('status', 'approved'),
        ], 'total_approved_submissions')
        ->orderByDesc('wallet_collected')
        ->paginate(20);

        return response()->json(['status' => true, 'data' => $boys]);
    }

    // ── Helper: Check pending submissions from previous days ────────
    private function checkPendingSubmissions(DeliveryBoy $boy): void
    {
        $hasOldPending = DeliveryCashSubmission::where('delivery_boy_id', $boy->id)
            ->where('status', 'pending')
            ->where('submission_date', '<', today()->toDateString())
            ->exists();

        if ($hasOldPending && !$boy->has_pending_submission) {
            $boy->update(['has_pending_submission' => true]);
        } elseif (!$hasOldPending && $boy->has_pending_submission) {
            // Check if today's is also pending
            $todayPending = DeliveryCashSubmission::where('delivery_boy_id', $boy->id)
                ->where('status', 'pending')
                ->where('submission_date', today()->toDateString())
                ->exists();
            if (!$todayPending) {
                $boy->update(['has_pending_submission' => false]);
            }
        }
    }
}

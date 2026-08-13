<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\AppUser;
use App\Models\Wallet;
use App\Mail\SendOtpMail;
use App\Services\NotificationService;
use Carbon\Carbon;

class AuthController extends Controller
{
    // ── Send OTP ───────────────────────────────────────────
    public function sendOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $email = strtolower(trim($request->email));

        if (AppUser::where('email', $email)->where('is_blocked', 1)->exists()) {
            return response()->json(['status' => false, 'message' => 'Your account has been suspended.'], 403);
        }

        $otp  = random_int(1000, 9999);
        $user = AppUser::firstOrCreate(
            ['email' => $email],
            ['full_name' => Str::before($email, '@')]
        );

        $user->update([
            'otp'            => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(10),
        ]);

        try {
            Mail::to($email)->send(new SendOtpMail($otp, $email));
        } catch (\Exception $e) {
            Log::error('[Auth] OTP mail failed for ' . $email . ': ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to send OTP. Please try again.'], 500);
        }

        return response()->json(['status' => true, 'message' => 'OTP sent to your email']);
    }

    // ── Verify OTP ─────────────────────────────────────────
    public function verifyOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'         => 'required|email',
            'otp'           => 'required|digits:4',
            'fcm_token'     => 'nullable|string|max:500',
            'referral_code' => 'nullable|string|max:20',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $email = strtolower(trim($request->email));
        $user  = AppUser::where('email', $email)->where('otp', $request->otp)->first();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Invalid OTP. Please try again.'], 400);
        }
        if ($user->is_blocked) {
            return response()->json(['status' => false, 'message' => 'Your account has been suspended.'], 403);
        }
        if (!$user->otp_expires_at || Carbon::now()->gt($user->otp_expires_at)) {
            return response()->json(['status' => false, 'message' => 'OTP has expired. Please request a new one.'], 400);
        }

        $isNewUser = !$user->is_verified;
        $token     = bin2hex(random_bytes(30));

        $user->update([
            'api_token'      => $token,
            'otp'            => null,
            'otp_expires_at' => null,
            'is_verified'    => 1,
            'fcm_token'      => $request->fcm_token ?? $user->fcm_token,
            'last_active_at' => now(),
        ]);

        if ($isNewUser) {
            // Assign referral code
            $user->update(['referral_code' => strtoupper(Str::random(8))]);

            // Create wallet
            $user->getOrCreateWallet();

            // Handle referral bonus
            if ($request->filled('referral_code')) {
                $referrer = AppUser::where('referral_code', $request->referral_code)->first();
                if ($referrer && $referrer->id !== $user->id) {
                    $user->update(['referred_by' => $request->referral_code]);
                    $referrer->getOrCreateWallet()->credit(50, 'Referral bonus — invited ' . $user->email, 'referral', $user->id);
                    $user->getOrCreateWallet()->credit(30, 'Welcome bonus via referral', 'referral', $referrer->id);
                }
            }

            NotificationService::send($user->id, 'Welcome to Sweetan! 🎉', 'Your account is all set. Start exploring sweet treats!', 'welcome');
        }

        return response()->json([
            'status'      => true,
            'token'       => $token,
            'is_new_user' => $isNewUser,
            'user'        => $user->fresh()->makeHidden(['otp', 'otp_expires_at']),
        ]);
    }

    // ── Logout ─────────────────────────────────────────────
    public function logout(Request $request): JsonResponse
    {
        $token = $request->bearerToken() ?? $request->input('api_token');
        if ($token) {
            AppUser::where('api_token', $token)->update(['api_token' => null, 'fcm_token' => null]);
        }
        return response()->json(['status' => true, 'message' => 'Logged out successfully']);
    }

    // ── Update Profile ─────────────────────────────────────
    public function updateProfile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'full_name'    => 'nullable|string|max:100',
            'phone_number' => 'nullable|string|max:20',
            'dob'          => 'nullable|date',
            'gender'       => 'nullable|in:male,female,other',
            'picture'      => 'nullable|string|max:500',
            'fcm_token'    => 'nullable|string|max:500',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $user->update($validator->validated());

        return response()->json(['status' => true, 'message' => 'Profile updated', 'user' => $user->fresh()]);
    }

    // ── Get Profile ────────────────────────────────────────
    public function getProfile(Request $request): JsonResponse
    {
        $user   = $request->user();
        $wallet = $user->getOrCreateWallet();

        return response()->json([
            'status' => true,
            'data'   => array_merge($user->toArray(), ['wallet_balance' => $wallet->balance]),
        ]);
    }

    // ── Delete Account ─────────────────────────────────────
    public function deleteAccount(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->update([
            'is_blocked' => 1,
            'api_token'  => null,
            'email'      => 'deleted_' . $user->id . '_' . $user->email,
        ]);

        return response()->json(['status' => true, 'message' => 'Account deleted successfully']);
    }
}

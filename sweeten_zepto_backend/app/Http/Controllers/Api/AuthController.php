<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\AppUser;
use App\Models\ReferralCode;
use App\Mail\SendOtpMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function sendOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        if (AppUser::where('email', $request->email)->where('is_blocked', 1)->exists()) {
            return response()->json(['status' => false, 'message' => 'Your account has been suspended.'], 403);
        }

        $otp = random_int(1000, 9999);
        $user = AppUser::firstOrCreate(
            ['email' => $request->email],
            ['full_name' => explode('@', $request->email)[0]]
        );
        $user->update([
            'otp'             => $otp,
            'otp_expires_at'  => Carbon::now()->addMinutes(10),
        ]);

        Mail::to($request->email)->send(new SendOtpMail($otp, $request->email));

        return response()->json(['status' => true, 'message' => 'OTP sent to your email']);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'       => 'required|email',
            'otp'         => 'required|digits:4',
            'fcm_token'   => 'nullable|string',
            'referral_code' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $user = AppUser::where('email', $request->email)->where('otp', $request->otp)->first();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Invalid OTP'], 400);
        }
        if (Carbon::now()->gt($user->otp_expires_at)) {
            return response()->json(['status' => false, 'message' => 'OTP has expired. Please request a new one.'], 400);
        }

        $isNewUser = !$user->is_verified;
        $token = bin2hex(random_bytes(30));

        $user->update([
            'api_token'    => $token,
            'otp'          => null,
            'otp_expires_at' => null,
            'is_verified'  => 1,
            'fcm_token'    => $request->fcm_token,
            'last_active_at' => now(),
        ]);

        if ($isNewUser) {
            $refCode = strtoupper(Str::random(8));
            $user->update(['referral_code' => $refCode]);
            $user->getOrCreateWallet();

            if ($request->filled('referral_code')) {
                $referrer = AppUser::where('referral_code', $request->referral_code)->first();
                if ($referrer) {
                    $user->update(['referred_by' => $request->referral_code]);
                    $referrerWallet = $referrer->getOrCreateWallet();
                    $referrerWallet->credit(50, 'Referral bonus for inviting ' . $user->email, 'referral', $user->id);
                    $wallet = $user->getOrCreateWallet();
                    $wallet->credit(30, 'Welcome bonus via referral', 'referral', $referrer->id);
                }
            }
        }

        return response()->json([
            'status'      => true,
            'token'       => $token,
            'is_new_user' => $isNewUser,
            'user'        => $user->makeHidden(['otp','otp_expires_at']),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->bearerToken() ?? $request->header('Authorization');
        if ($token) {
            AppUser::where('api_token', $token)->update(['api_token' => null, 'fcm_token' => null]);
        }
        return response()->json(['status' => true, 'message' => 'Logged out successfully']);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id'     => 'required|exists:app_users,id',
            'full_name'   => 'nullable|string|max:100',
            'phone_number'=> 'nullable|string|max:20',
            'dob'         => 'nullable|date',
            'gender'      => 'nullable|in:male,female,other',
            'picture'     => 'nullable|string',
            'fcm_token'   => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $user = AppUser::findOrFail($request->user_id);
        $user->update($validator->safe()->except('user_id'));

        return response()->json(['status' => true, 'message' => 'Profile updated', 'user' => $user]);
    }

    public function getProfile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:app_users,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        $user = AppUser::findOrFail($request->user_id);
        $wallet = $user->getOrCreateWallet();
        return response()->json([
            'status' => true,
            'data'   => array_merge($user->toArray(), ['wallet_balance' => $wallet->balance]),
        ]);
    }

    public function deleteAccount(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:app_users,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        $user = AppUser::findOrFail($request->user_id);
        $user->update(['is_blocked' => 1, 'api_token' => null, 'email' => 'deleted_' . $user->id . '_' . $user->email]);
        return response()->json(['status' => true, 'message' => 'Account deleted successfully']);
    }
}

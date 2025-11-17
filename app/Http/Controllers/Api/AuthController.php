<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppUser;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AuthController extends Controller
{
    // STEP 1: Send OTP
   public function sendOtp(Request $request)
{
    $validator = Validator::make($request->all(), [
        'phone_number' => 'required|digits:10',
        'full_name'   => 'required|string|max:255'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    $phone_number = $request->phone_number;
    $name = $request->full_name;   // <-- FIXED

    // Check if user exists
    $user = AppUser::where('phone_number', $phone_number)->first();

    // If new user, create minimal user record
    if (!$user) {
        $user = AppUser::create([
            'full_name' => $name,
            'phone_number' => $phone_number
        ]);
    }

    // Dummy OTP
    $otp = "1234";

    $user->update([
        'otp_code'       => $otp,
        'otp_expires_at' => Carbon::now()->addMinutes(5)
    ]);

    return response()->json([
        'status' => true,
        'message' => 'OTP sent successfully (dummy)',
        'phone_number' => $phone_number,
        'otp' => $otp 
    ]);
}

    // STEP 2: Verify OTP
   public function verifyOtp(Request $request)
{
    $validator = Validator::make($request->all(), [
        'phone_number' => 'required|digits:10',
        'otp'    => 'required|digits:4'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    $user = AppUser::where('phone_number', $request->phone_number)->first();

    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => 'User not found'
        ], 404);
    }

    if ($user->otp_code != $request->otp) {   // <-- MATCHING FIELD
        return response()->json([
            'status' => false,
            'message' => 'Invalid OTP'
        ], 401);
    }

    if (Carbon::now()->greaterThan($user->otp_expires_at)) {
        return response()->json([
            'status' => false,
            'message' => 'OTP expired'
        ], 401);
    }

    // Verify and clear OTP
    $user->update([
        'otp_code' => null,
        'otp_expires_at' => null,
        'is_verified' => 1
    ]);

    return response()->json([
        'status' => true,
        'message' => 'OTP verified successfully. Logged in!',
        'user' => $user
    ]);
}

    // STEP 3: Update Profile (after OTP login)
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'  => 'required|exists:app_users,id',
            'dob'      => 'nullable|date',
            'gender'   => 'nullable|in:male,female,other',
            'picture'  => 'nullable|string',
            'state'    => 'nullable|string',
            'city'     => 'nullable|string',
            'pincode'  => 'nullable|string|max:10',
            'address'  => 'nullable|string',
            'lat'      => 'nullable|numeric',
            'lng'      => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = AppUser::find($request->user_id);

        $user->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }

 
}
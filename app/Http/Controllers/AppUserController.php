<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppUser;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AppUserController extends Controller
{
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|unique:app_users,phone_number',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $otp = '123456'; 

        $user = AppUser::create([
            'phone_number' => $request->phone_number,
            'otp' => $otp,
            'is_verified' => 0
        ]);


        return response()->json([
            'message' => 'OTP sent to your phone number',
            'phone_number' => $user->phone_number,
            'otp' => $otp
        ], 201);
    }
     public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|exists:app_users,phone_number',
            'otp' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = AppUser::where('phone_number', $request->phone_number)
                       ->where('otp', $request->otp)
                       ->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }

        $user->is_verified = 1;
        $user->otp = null;
        $user->save();

        return response()->json(['message' => 'OTP verified successfully', 'user_id' => $user->id], 200);
    }
    public function updateProfile(Request $request, $id)
    {
        $user = AppUser::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'full_name' => 'nullable|string|max:100',
            'email' => 'nullable|email|unique:app_users,email,' . $id,
            'password' => 'nullable|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        if(isset($data['password'])){
            $data['password'] = bcrypt($data['password']);
        }

        $user->update($data);

        return response()->json(['message' => 'Profile updated successfully', 'data' => $user], 200);
    }
}

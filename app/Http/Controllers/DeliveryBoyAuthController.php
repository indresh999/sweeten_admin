<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeliveryBoyRegisterRequest;
use App\Http\Requests\DeliveryBoyLoginRequest;
use App\Models\DeliveryBoy;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class DeliveryBoyAuthController extends Controller
{
    public function register(DeliveryBoyRegisterRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $deliveryBoy = DeliveryBoy::create($data);

        $token = $deliveryBoy->createToken('deliveryboy')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful',
            'data' => $deliveryBoy,
            'token' => $token
        ]);
    }

    public function login(DeliveryBoyLoginRequest $request)
    {
        $deliveryBoy = DeliveryBoy::where('phone_number', $request->phone_number)->first();

        if (!$deliveryBoy || !Hash::check($request->password, $deliveryBoy->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if ($deliveryBoy->status === 'blocked') {
            return response()->json(['message' => 'Account blocked'], 403);
        }

        $deliveryBoy->last_login_at = now();
        $deliveryBoy->status = 'online';
        $deliveryBoy->save();

        $token = $deliveryBoy->createToken('deliveryboy')->plainTextToken;

        return response()->json([
            'message' => 'Logged in successfully',
            'data' => $deliveryBoy,
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->status = 'offline';
        $user->save();

        $user->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }
}
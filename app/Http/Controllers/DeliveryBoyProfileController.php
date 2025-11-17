<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeliveryBoyProfileUpdateRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class DeliveryBoyProfileController extends Controller
{
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function update(DeliveryBoyProfileUpdateRequest $request)
    {
        $deliveryBoy = $request->user();
        $data = $request->validated();

        if ($request->hasFile('picture')) {
            $path = $request->file('picture')->store('public/deliveryboy');
            $data['picture'] = $path;
        }

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $deliveryBoy->update($data);

        return response()->json([
            'message' => 'Profile updated',
            'data' => $deliveryBoy
        ]);
    }

    public function updateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $deliveryBoy = $request->user();

        $deliveryBoy->update([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return response()->json(['message' => 'Location updated']);
    }
}
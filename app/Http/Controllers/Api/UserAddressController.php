<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserAddress;
use Illuminate\Support\Facades\Validator;

class UserAddressController extends Controller
{
    // GET /addresses/{user_id}
    // Flutter expects { data: [...] }
    public function listAddresses($user_id)
    {
        $addresses = UserAddress::where('user_id', $user_id)
            ->orderByDesc('is_default')
            ->get();

        return response()->json(['status' => true, 'data' => $addresses]);
    }

    // POST /addresses
    public function addAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'      => 'required|exists:app_users,id',
            'label'        => 'nullable|string|max:50',
            'address_line' => 'required|string',
            'city'         => 'required|string|max:100',
            'state'        => 'required|string|max:100',
            'pincode'      => 'required|string|max:10',
            'lat'          => 'nullable|numeric',
            'lng'          => 'nullable|numeric',
            'is_default'   => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $isDefault = $request->is_default
            ?? UserAddress::where('user_id', $request->user_id)->count() === 0;

        if ($isDefault) {
            UserAddress::where('user_id', $request->user_id)->update(['is_default' => false]);
        }

        $address = UserAddress::create([
            'user_id'      => $request->user_id,
            'label'        => $request->label ?? 'Home',
            'address_line' => $request->address_line,
            'city'         => $request->city,
            'state'        => $request->state,
            'pincode'      => $request->pincode,
            'lat'          => $request->lat ?? 0,
            'lng'          => $request->lng ?? 0,
            'is_default'   => $isDefault,
        ]);

        return response()->json(['status' => true, 'message' => 'Address added', 'data' => $address]);
    }

    // PUT /addresses/{id}
    public function updateAddress(Request $request, $id)
    {
        $address = UserAddress::findOrFail($id);
        $address->update($request->only(['label', 'address_line', 'city', 'state', 'pincode', 'lat', 'lng']));
        return response()->json(['status' => true, 'message' => 'Address updated', 'data' => $address]);
    }

    // DELETE /addresses/{id}
    public function deleteAddress($id)
    {
        UserAddress::findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'Address deleted']);
    }

    // POST /addresses/{id}/set-default
    public function setDefaultAddress($id)
    {
        $address = UserAddress::findOrFail($id);
        UserAddress::where('user_id', $address->user_id)->update(['is_default' => false]);
        $address->update(['is_default' => true]);
        return response()->json(['status' => true, 'message' => 'Default address set', 'data' => $address]);
    }
}

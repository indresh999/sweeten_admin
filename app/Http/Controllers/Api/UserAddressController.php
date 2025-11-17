<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserAddress;
use Illuminate\Support\Facades\Validator;

class UserAddressController extends Controller
{
    // Add new address
    public function addAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'      => 'required|exists:app_users,id',
            'label'        => 'nullable|string',
            'address_line' => 'required|string',
            'city'         => 'required|string',
            'state'        => 'required|string',
            'pincode'      => 'required|string|max:10',
            'lat'          => 'nullable|numeric',
            'lng'          => 'nullable|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // If user has no default address, set this as default
        $isDefault = UserAddress::where('user_id', $request->user_id)->count() == 0;

        $address = UserAddress::create([
            'user_id'      => $request->user_id,
            'label'        => $request->label,
            'address_line' => $request->address_line,
            'city'         => $request->city,
            'state'        => $request->state,
            'pincode'      => $request->pincode,
            'lat'          => $request->lat,
            'lng'          => $request->lng,
            'is_default'   => $isDefault
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Address added successfully',
            'data' => $address
        ]);
    }

    // Update address
    public function updateAddress(Request $request, $id)
    {
        $address = UserAddress::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'label'        => 'nullable|string',
            'address_line' => 'nullable|string',
            'city'         => 'nullable|string',
            'state'        => 'nullable|string',
            'pincode'      => 'nullable|string|max:10',
            'lat'          => 'nullable|numeric',
            'lng'          => 'nullable|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $address->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Address updated successfully',
            'data' => $address
        ]);
    }

    // Delete address
    public function deleteAddress($id)
    {
        $address = UserAddress::findOrFail($id);
        $address->delete();

        return response()->json([
            'status' => true,
            'message' => 'Address deleted successfully'
        ]);
    }

    // List all addresses of user
    public function listAddresses($user_id)
    {
        $addresses = UserAddress::where('user_id', $user_id)->orderByDesc('is_default')->get();

        return response()->json([
            'status' => true,
            'message' => 'Addresses fetched successfully',
            'data' => $addresses
        ]);
    }

    // Set default address
    public function setDefaultAddress($id)
    {
        $address = UserAddress::findOrFail($id);

        // Remove default from all other addresses
        UserAddress::where('user_id', $address->user_id)->update(['is_default' => false]);

        // Set new default
        $address->update(['is_default' => true]);

        return response()->json([
            'status' => true,
            'message' => 'Default address updated successfully',
            'data' => $address
        ]);
    }
}
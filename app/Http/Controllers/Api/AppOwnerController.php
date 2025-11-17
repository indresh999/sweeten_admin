<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\AppOwnerUser;
use App\Models\ShopImage;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class AppOwnerController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:100',
            'email' => 'required|email|unique:app_owner_shops,email',
            'password' => 'required|string|min:6',
            'phone_number' => 'nullable|string|max:20',
            'restaurant_name' => 'required|string|max:100',
            'restaurant_address' => 'nullable|string',
            'city' => 'nullable|string|max:50',
            'state' => 'nullable|string|max:50',
            'zip_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:50',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'gst_number' => 'nullable|string|max:20',
            'pan_number' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['password'] = Hash::make($data['password']);

        $owner = AppOwnerUser::create($data);

        return response()->json(['message' => 'Profile successfully', 'data' => $owner], 201);
    }

    public function update(Request $request, $id)
    {
        $owner = AppOwnerUser::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'full_name' => 'sometimes|required|string|max:100',
            'email' => 'sometimes|required|email|unique:restaurant_owners,email,' . $id,
            'password' => 'sometimes|nullable|string|min:6',
            'phone_number' => 'nullable|string|max:20',
            'restaurant_name' => 'sometimes|required|string|max:100',
            'restaurant_address' => 'nullable|string',
            'city' => 'nullable|string|max:50',
            'state' => 'nullable|string|max:50',
            'zip_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:50',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'gst_number' => 'nullable|string|max:20',
            'pan_number' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $owner->update($data);

        return response()->json(['message' => 'Restaurant owner updated successfully', 'data' => $owner], 200);
    }

    public function toggleStatus($id)
    {
        $owner = AppOwnerUser::findOrFail($id);
        $owner->status = $owner->status === 'active' ? 'inactive' : 'active';
        $owner->save();
        return response()->json(['message' => 'Owner status updated', 'status' => $owner->status], 200);
    }

    public function uploadShopImage(Request $request)
    {
        $request->validate([
            'shop_id' => 'required|exists:app_owner_shops,owner_id',
            'tag' => 'nullable|string|max:100',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', 
        ]);

        $file = $request->file('image');

        $fileName = uniqid().'_'.time().'.'.$file->getClientOriginalExtension();

        $image = Image::make($file->getRealPath());
        $image->resize(1200, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $path = 'shop_images/'.$fileName;
        Storage::disk('public')->put($path, (string) $image->encode(null, 75)); 

        $shopImage = ShopImage::create([
            'shop_id' => $request->shop_id,
            'tag' => $request->tag,
            'image_path' => $path,
        ]);

        return response()->json([
            'message' => 'Image uploaded successfully server',
            'data' => $shopImage
        ]);
    }
    public function deleteShopImage($id)
    {
        $shopImage = ShopImage::find($id);

        if (!$shopImage) {
            return response()->json(['message' => 'Image not found'], 404);
        }

        // Delete file from storage
        if (Storage::disk('public')->exists($shopImage->image_path)) {
            Storage::disk('public')->delete($shopImage->image_path);
        }

        // Delete record from DB
        $shopImage->delete();

        return response()->json(['message' => 'Image deleted successfully']);
    }

    public function listShopImages($shop_id)
    {
        $images = ShopImage::where('shop_id', $shop_id)->get();

        // Generate full URLs
        $images->map(function ($img) {
            $img->image_url = asset('storage/'.$img->image_path);
            return $img;
        });

        return response()->json([
            'message' => 'Shop images fetched successfully',
            'data' => $images
        ]);
    }

    public function getShopDetails($id)
    {
        try {
            $owner = AppOwnerUser::findOrFail($id);

            return response()->json([
                'message' => 'Shop details fetched successfully',
                'data' => $owner,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Shop not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    public function nearbyShops(Request $request)
    {
        $latitude  = $request->latitude;
        $longitude = $request->longitude;

        if (!$latitude || !$longitude) {
            return response()->json([
                'error' => 'latitude and longitude are required.'
            ], 422);
        }

        $status = $request->status;     
        $radius = $request->radius ?? 5; 

        $haversine = "
            (6371 * acos(
                cos(radians($latitude)) *
                cos(radians(latitude)) *
                cos(radians(longitude) - radians($longitude)) +
                sin(radians($latitude)) *
                sin(radians(latitude))
            ))
        ";

        $query = AppOwnerUser::select('*')
            ->selectRaw("$haversine AS distance")
            ->orderBy('distance', 'ASC')
            ->whereRaw("$haversine <= ?", [$radius]);

        if ($status) {
            $query->where('status', $status);
        }

        $query->with('images:id,shop_id,image_path');

        $shops = $query->limit(100)->get();

        return response()->json([
            'data' => $shops
        ], 200);
    }
}
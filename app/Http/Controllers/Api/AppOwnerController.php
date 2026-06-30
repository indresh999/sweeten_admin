<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\AppOwnerUser;
use App\Models\Item;
use App\Models\Order;
use App\Models\ShopImage;

class AppOwnerController extends Controller
{
    // ── Register vendor ────────────────────────────────────
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name'          => 'required|string|max:100',
            'email'              => 'required|email|unique:app_owner_shops,email',
            'password'           => 'required|string|min:6',
            'phone_number'       => 'nullable|string|max:20',
            'restaurant_name'    => 'required|string|max:100',
            'restaurant_address' => 'nullable|string',
            'city'               => 'nullable|string|max:50',
            'state'              => 'nullable|string|max:50',
            'latitude'           => 'nullable|numeric',
            'longitude'          => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $data             = $validator->validated();
        $data['password'] = Hash::make($data['password']);
        $owner            = AppOwnerUser::create($data);

        return response()->json(['status' => true, 'message' => 'Store registered', 'data' => $owner], 201);
    }

    // ── Vendor login ───────────────────────────────────────
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $user = AppOwnerUser::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['status' => false, 'message' => 'Invalid credentials'], 401);
        }

        $user->makeHidden(['password']);
        return response()->json(['status' => true, 'message' => 'Login successful', 'data' => $user]);
    }

    // ── Update vendor ──────────────────────────────────────
    public function update(Request $request, $id)
    {
        $owner = AppOwnerUser::where('shop_id', $id)->firstOrFail();
        $owner->update($request->except(['password', 'email']));
        return response()->json(['status' => true, 'message' => 'Updated', 'data' => $owner]);
    }

    // ── Nearby shops ───────────────────────────────────────
    // GET /nearby-shops?latitude=X&longitude=Y&radius=N
    // Flutter expects: { data: [ { shop_id, restaurant_name, images:[{id,image_path}], distance, ... } ] }
    public function nearbyShops(Request $request)
    {
        $lat    = (float) ($request->latitude  ?? 0);
        $lng    = (float) ($request->longitude ?? 0);
        $radius = (float) ($request->radius    ?? 10);

        $shops = AppOwnerUser::with('images:id,shop_id,image_path,tag')
            ->where('status', 'active')
            ->when($lat && $lng, function ($q) use ($lat, $lng, $radius) {
                $q->selectRaw(
                    'app_owner_shops.*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
                    [$lat, $lng, $lat]
                )->having('distance', '<=', $radius)->orderBy('distance');
            }, function ($q) {
                $q->select('app_owner_shops.*');
            })
            ->get();

        return response()->json(['status' => true, 'data' => $shops]);
    }

    // ── Shop details ───────────────────────────────────────
    public function getShopDetails($id)
    {
        $shop = AppOwnerUser::with('images')->where('shop_id', $id)->first();
        if (!$shop) {
            return response()->json(['status' => false, 'message' => 'Shop not found'], 404);
        }
        return response()->json(['status' => true, 'data' => $shop]);
    }

    // ── Shop dashboard ─────────────────────────────────────
    public function shopDashboard(Request $request)
    {
        $shopId = $request->shop_id;
        return response()->json(['status' => true, 'data' => [
            'total_orders'   => Order::where('shop_id', $shopId)->count(),
            'pending_orders' => Order::where('shop_id', $shopId)->where('status', 'pending')->count(),
            'total_items'    => Item::where('shop_id', $shopId)->count(),
        ]]);
    }

    // ── Shop images ────────────────────────────────────────
    public function uploadShopImage(Request $request)
    {
        $request->validate(['shop_id' => 'required', 'image_path' => 'required|string']);
        $img = ShopImage::create($request->only('shop_id', 'image_path', 'tag'));
        return response()->json(['status' => true, 'data' => $img]);
    }

    public function deleteShopImage($id)
    {
        ShopImage::findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'Image deleted']);
    }

    public function listShopImages($shopId)
    {
        return response()->json(['status' => true, 'data' => ShopImage::where('shop_id', $shopId)->get()]);
    }

    public function toggleStatus($id)
    {
        $shop   = AppOwnerUser::where('shop_id', $id)->firstOrFail();
        $status = $shop->status === 'active' ? 'inactive' : 'active';
        $shop->update(['status' => $status]);
        return response()->json(['status' => true, 'data' => $shop]);
    }
}

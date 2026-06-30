<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\AppOwnerUser;
use App\Models\ShopImage;
use App\Models\ShopSchedule;
use App\Models\Item;
use App\Models\Order;
use App\Models\AppSetting;
use Intervention\Image\Facades\Image;

class ShopController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'full_name'          => 'required|string|max:100',
            'email'              => 'required|email|unique:app_owner_shops,email',
            'password'           => 'required|string|min:6',
            'phone_number'       => 'nullable|string|max:20',
            'restaurant_name'    => 'required|string|max:100',
            'restaurant_address' => 'nullable|string',
            'city'               => 'nullable|string|max:100',
            'state'              => 'nullable|string|max:100',
            'zip_code'           => 'nullable|string|max:20',
            'country'            => 'nullable|string|max:100',
            'latitude'           => 'nullable|numeric',
            'longitude'          => 'nullable|numeric',
            'gst_number'         => 'nullable|string|max:20',
            'pan_number'         => 'nullable|string|max:20',
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);

        $data = $validator->validated();
        $data['password'] = Hash::make($data['password']);
        $shop = AppOwnerUser::create($data);

        return response()->json(['status' => true, 'message' => 'Shop registered successfully', 'data' => $shop], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);

        $shop = AppOwnerUser::where('email', $request->email)->first();
        if (!$shop || !Hash::check($request->password, $shop->password)) {
            return response()->json(['status' => false, 'message' => 'Invalid credentials'], 401);
        }

        return response()->json(['status' => true, 'message' => 'Login successful', 'data' => $shop->makeHidden(['password'])]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $shop = AppOwnerUser::where('shop_id', $id)->firstOrFail();
        $validator = Validator::make($request->all(), [
            'full_name'          => 'sometimes|string|max:100',
            'email'              => 'sometimes|email|unique:app_owner_shops,email,' . $id . ',shop_id',
            'password'           => 'nullable|string|min:6',
            'phone_number'       => 'nullable|string|max:20',
            'restaurant_name'    => 'sometimes|string|max:100',
            'restaurant_address' => 'nullable|string',
            'city'               => 'nullable|string|max:100',
            'state'              => 'nullable|string|max:100',
            'latitude'           => 'nullable|numeric',
            'longitude'          => 'nullable|numeric',
            'gst_number'         => 'nullable|string|max:20',
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);

        $data = $validator->validated();
        if (!empty($data['password'])) $data['password'] = Hash::make($data['password']);
        else unset($data['password']);
        $shop->update($data);

        return response()->json(['status' => true, 'data' => $shop->makeHidden(['password'])]);
    }

    public function nearbyShops(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius'    => 'nullable|numeric|min:1|max:50',
            'category'  => 'nullable|string',
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);

        $lat    = (float)$request->latitude;
        $lng    = (float)$request->longitude;
        $radius = (float)($request->radius ?? AppSetting::get('max_delivery_radius_km', 10));

        $shops = AppOwnerUser::select('*')
            ->selectRaw('(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
                [$lat, $lng, $lat])
            ->where('status', 'active')
            ->having('distance', '<=', $radius)
            ->orderBy('distance')
            ->with('images:id,shop_id,image_path,tag')
            ->limit(50)
            ->get()
            ->map(function ($shop) {
                $shop->is_open = $shop->isOpenNow();
                return $shop;
            });

        return response()->json(['status' => true, 'data' => $shops]);
    }

    public function getShopDetails(int $id): JsonResponse
    {
        $shop = AppOwnerUser::with(['images:id,shop_id,image_path,tag', 'schedules'])->findOrFail($id);
        $shop->is_open    = $shop->isOpenNow();
        $shop->avg_rating = \App\Models\ShopReview::where('shop_id', $shop->shop_id)->where('is_approved', 1)->avg('rating');
        return response()->json(['status' => true, 'data' => $shop->makeHidden(['password'])]);
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $shop = AppOwnerUser::findOrFail($id);
        $shop->status = $shop->status === 'active' ? 'inactive' : 'active';
        $shop->save();
        return response()->json(['status' => true, 'shop_status' => $shop->status]);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), ['shop_id' => 'required|exists:app_owner_shops,shop_id']);
        if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);

        $sid = $request->shop_id;
        $today = today();
        return response()->json([
            'status' => true,
            'data'   => [
                'total_items'         => Item::where('shop_id', $sid)->count(),
                'active_items'        => Item::where('shop_id', $sid)->where('status', 'active')->count(),
                'total_orders'        => Order::where('shop_id', $sid)->count(),
                'today_orders'        => Order::where('shop_id', $sid)->whereDate('created_at', $today)->count(),
                'pending_orders'      => Order::where('shop_id', $sid)->where('status', 'pending')->count(),
                'today_revenue'       => Order::where('shop_id', $sid)->whereDate('created_at', $today)->where('status', 'delivered')->sum('final_amount'),
                'total_revenue'       => Order::where('shop_id', $sid)->where('status', 'delivered')->sum('final_amount'),
                'avg_rating'          => round(\App\Models\ShopReview::where('shop_id', $sid)->where('is_approved', 1)->avg('rating') ?? 0, 1),
                'recent_orders'       => Order::with(['items', 'user:id,full_name,phone_number'])->where('shop_id', $sid)->orderByDesc('created_at')->take(5)->get(),
            ],
        ]);
    }

    public function shopOrders(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'shop_id'  => 'required|exists:app_owner_shops,shop_id',
            'status'   => 'nullable|string',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);

        $query = Order::with(['items', 'user:id,full_name,phone_number', 'assignment.boy:id,full_name,phone_number'])
            ->where('shop_id', $request->shop_id)
            ->orderByDesc('created_at');

        if ($request->filled('status')) $query->where('status', $request->status);

        return response()->json(['status' => true, 'data' => $query->paginate($request->get('per_page', 20))]);
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'shop_id' => 'required|exists:app_owner_shops,shop_id',
            'tag'     => 'nullable|string|max:100',
            'image'   => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);

        $file     = $request->file('image');
        $fileName = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path     = 'shop_images/' . $fileName;

        $img = Image::make($file->getRealPath())->resize(1200, null, fn($c) => $c->aspectRatio()->upsize());
        Storage::disk('public')->put($path, (string)$img->encode(null, 80));

        $shopImage = ShopImage::create(['shop_id' => $request->shop_id, 'tag' => $request->tag, 'image_path' => $path]);
        return response()->json(['status' => true, 'data' => $shopImage], 201);
    }

    public function deleteImage(int $id): JsonResponse
    {
        $img = ShopImage::findOrFail($id);
        Storage::disk('public')->delete($img->image_path);
        $img->delete();
        return response()->json(['status' => true, 'message' => 'Image deleted']);
    }

    public function listImages(int $shopId): JsonResponse
    {
        $images = ShopImage::where('shop_id', $shopId)->get()->map(fn($i) => array_merge($i->toArray(), ['url' => asset('storage/' . $i->image_path)]));
        return response()->json(['status' => true, 'data' => $images]);
    }

    public function updateSchedule(Request $request, int $shopId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'schedules'              => 'required|array|size:7',
            'schedules.*.day_of_week'=> 'required|integer|min:0|max:6',
            'schedules.*.open_time'  => 'required|date_format:H:i',
            'schedules.*.close_time' => 'required|date_format:H:i|after:schedules.*.open_time',
            'schedules.*.is_closed'  => 'nullable|boolean',
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);

        foreach ($request->schedules as $s) {
            ShopSchedule::updateOrCreate(
                ['shop_id' => $shopId, 'day_of_week' => $s['day_of_week']],
                ['open_time' => $s['open_time'], 'close_time' => $s['close_time'], 'is_closed' => $s['is_closed'] ?? false]
            );
        }
        return response()->json(['status' => true, 'message' => 'Schedule updated']);
    }

    public function getSchedule(int $shopId): JsonResponse
    {
        $schedules = ShopSchedule::where('shop_id', $shopId)->orderBy('day_of_week')->get();
        return response()->json(['status' => true, 'data' => $schedules]);
    }
}

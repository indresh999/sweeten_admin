<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendOtpMail;
use App\Models\AppOwnerUser;
use App\Models\ShopImage;
use App\Models\ShopSchedule;
use App\Models\Item;
use App\Models\Order;
use App\Models\AppSetting;
use App\Models\ShopReview;
use App\Models\ShopView;
use Intervention\Image\Facades\Image;

class ShopController extends Controller
{
    // ── Register ───────────────────────────────────────────────────────────────
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'full_name'          => 'required|string|max:100',
            'email'              => 'required|email|max:255|unique:app_owner_shops,email',
            'password'           => 'required|string|min:8|confirmed',
            'phone_number'       => 'required|string|regex:/^[6-9]\d{9}$/|unique:app_owner_shops,phone_number',
            'restaurant_name'    => 'required|string|max:150',
            'restaurant_address' => 'required|string|max:500',
            'city'               => 'required|string|max:100',
            'state'              => 'required|string|max:100',
            'zip_code'           => 'required|string|regex:/^\d{6}$/',
            'country'            => 'nullable|string|max:100',
            'latitude'           => 'nullable|numeric|between:-90,90',
            'longitude'          => 'nullable|numeric|between:-180,180',
            'gst_number'         => 'nullable|string|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
            'pan_number'         => 'nullable|string|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
            'fssai_number'       => 'nullable|string|max:20',
        ], [
            'phone_number.regex'  => 'Enter a valid 10-digit Indian mobile number.',
            'zip_code.regex'      => 'Enter a valid 6-digit PIN code.',
            'gst_number.regex'    => 'Enter a valid GST number (e.g. 22AAAAA0000A1Z5).',
            'pan_number.regex'    => 'Enter a valid PAN number (e.g. ABCDE1234F).',
            'password.confirmed'  => 'Password and confirm password do not match.',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $data             = $validator->validated();
        $data['password'] = Hash::make($data['password']);
        $data['status']   = 'pending';
        $data['country']  = $data['country'] ?? 'India';

        $shop = AppOwnerUser::create($data);

        return response()->json([
            'status'  => true,
            'message' => 'Store registered successfully. Our team will review and activate your store within 24 hours.',
            'data'    => $shop->makeHidden(['password', 'api_token']),
        ], 201);
    }

    // ── Login ──────────────────────────────────────────────────────────────────
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'     => 'required|email',
            'password'  => 'required|string',
            'fcm_token' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $shop = AppOwnerUser::where('email', strtolower(trim($request->email)))->first();

        if (!$shop || !Hash::check($request->password, $shop->password)) {
            return response()->json(['status' => false, 'message' => 'Invalid email or password.'], 401);
        }

        match ($shop->status) {
            'blocked'  => throw new \Symfony\Component\HttpKernel\Exception\HttpException(
                403, 'Your store has been suspended. Please contact support.'
            ),
            'pending'  => throw new \Symfony\Component\HttpKernel\Exception\HttpException(
                403, 'Your store is under review. You will be notified once activated.'
            ),
            'inactive' => throw new \Symfony\Component\HttpKernel\Exception\HttpException(
                403, 'Your store is currently inactive. Enable it from your dashboard.'
            ),
            default    => null,
        };

        $token = $shop->generateToken();

        $updateData = ['last_active_at' => now()];
        if ($request->filled('fcm_token')) {
            $updateData['fcm_token'] = $request->fcm_token;
        }
        $shop->updateQuietly($updateData);

        return response()->json([
            'status'  => true,
            'message' => 'Login successful.',
            'token'   => $token,
            'data'    => $shop->fresh()->makeHidden(['password', 'api_token']),
        ]);
    }

    // ── Send OTP (email-based login) ───────────────────────────────────────────
    public function sendOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $email = strtolower(trim($request->email));
        $shop  = AppOwnerUser::where('email', $email)->first();

        if (!$shop) {
            // Generic message — don't reveal registration status
            return response()->json(['status' => true, 'message' => 'If this email is registered, an OTP will be sent shortly.']);
        }

        if ($shop->status === 'blocked') {
            return response()->json(['status' => false, 'message' => 'Your store has been suspended. Please contact support.'], 403);
        }

        $otp = random_int(100000, 999999); // 6-digit

        $shop->update([
            'otp_code'       => (string) $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        try {
            Mail::to($email)->send(new SendOtpMail($otp, $email));
        } catch (\Exception $e) {
            Log::error('[ShopOTP] mail failed for ' . $email . ': ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to send OTP. Please try again.'], 500);
        }

        return response()->json(['status' => true, 'message' => 'OTP sent to your registered email address. Valid for 10 minutes.']);
    }

    // ── Verify OTP ─────────────────────────────────────────────────────────────
    public function verifyOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'     => 'required|email',
            'otp'       => 'required|digits:6',
            'fcm_token' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $email = strtolower(trim($request->email));
        $shop  = AppOwnerUser::where('email', $email)->first();

        if (!$shop || $shop->otp_code !== (string) $request->otp) {
            return response()->json(['status' => false, 'message' => 'Invalid OTP. Please try again.'], 400);
        }

        if (!$shop->otp_expires_at || now()->gt($shop->otp_expires_at)) {
            return response()->json(['status' => false, 'message' => 'OTP has expired. Please request a new one.'], 400);
        }

        // Invalidate OTP immediately to prevent replay attacks
        $shop->update(['otp_code' => null, 'otp_expires_at' => null]);

        match ($shop->status) {
            'blocked'  => throw new \Symfony\Component\HttpKernel\Exception\HttpException(
                403, 'Your store has been suspended. Please contact support.'
            ),
            'pending'  => throw new \Symfony\Component\HttpKernel\Exception\HttpException(
                403, 'Your store is under review. You will be notified once activated.'
            ),
            'inactive' => throw new \Symfony\Component\HttpKernel\Exception\HttpException(
                403, 'Your store is currently inactive. Enable it from your dashboard.'
            ),
            default    => null,
        };

        $token = $shop->generateToken();

        $updateData = ['last_active_at' => now()];
        if ($request->filled('fcm_token')) {
            $updateData['fcm_token'] = $request->fcm_token;
        }
        $shop->updateQuietly($updateData);

        return response()->json([
            'status'  => true,
            'message' => 'OTP verified. Login successful.',
            'token'   => $token,
            'data'    => $shop->fresh()->makeHidden(['password', 'api_token']),
        ]);
    }

    // ── Logout ─────────────────────────────────────────────────────────────────
    public function logout(Request $request): JsonResponse
    {
        $shop = $request->user();
        if ($shop) {
            $shop->updateQuietly(['api_token' => null]);
        }
        return response()->json(['status' => true, 'message' => 'Logged out successfully.']);
    }

    // ── Get My Profile ─────────────────────────────────────────────────────────
    public function me(Request $request): JsonResponse
    {
        $shop = $request->user();
        return response()->json([
            'status' => true,
            'data'   => $shop->load('images', 'schedules')->makeHidden(['password', 'api_token']),
        ]);
    }

    // ── Update Profile ─────────────────────────────────────────────────────────
    public function update(Request $request): JsonResponse
    {
        $shop = $request->user();

        $validator = Validator::make($request->all(), [
            'full_name'           => 'sometimes|string|max:100',
            'phone_number'        => 'sometimes|string|regex:/^[6-9]\d{9}$/|unique:app_owner_shops,phone_number,' . $shop->shop_id . ',shop_id',
            'restaurant_name'     => 'sometimes|string|max:150',
            'restaurant_address'  => 'sometimes|string|max:500',
            'city'                => 'sometimes|string|max:100',
            'state'               => 'sometimes|string|max:100',
            'zip_code'            => 'sometimes|string|regex:/^\d{6}$/',
            'country'             => 'nullable|string|max:100',
            'latitude'            => 'nullable|numeric|between:-90,90',
            'longitude'           => 'nullable|numeric|between:-180,180',
            'gst_number'          => 'nullable|string|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
            'pan_number'          => 'nullable|string|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
            'fssai_number'        => 'nullable|string|max:20',
            'bank_account_name'   => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:30',
            'bank_ifsc'           => 'nullable|string|regex:/^[A-Z]{4}0[A-Z0-9]{6}$/',
            'bank_name'           => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $shop->update($validator->validated());

        return response()->json([
            'status'  => true,
            'message' => 'Profile updated.',
            'data'    => $shop->fresh()->makeHidden(['password', 'api_token']),
        ]);
    }

    // ── Change Password ────────────────────────────────────────────────────────
    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|confirmed|different:current_password',
        ], [
            'password.different'  => 'New password must be different from your current password.',
            'password.confirmed'  => 'New password and confirm password do not match.',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $shop = $request->user();

        if (!Hash::check($request->current_password, $shop->password)) {
            return response()->json(['status' => false, 'message' => 'Current password is incorrect.'], 400);
        }

        $shop->update([
            'password'  => Hash::make($request->password),
            'api_token' => null, // Invalidate existing sessions
        ]);

        return response()->json(['status' => true, 'message' => 'Password changed. Please log in again.']);
    }

    // ── Toggle Active/Inactive ─────────────────────────────────────────────────
    public function toggleStatus(Request $request): JsonResponse
    {
        $shop = $request->user();

        if ($shop->status === 'blocked') {
            return response()->json(['status' => false, 'message' => 'Blocked stores cannot be toggled. Contact admin.'], 403);
        }

        $shop->status = $shop->status === 'active' ? 'inactive' : 'active';
        $shop->save();

        return response()->json(['status' => true, 'shop_status' => $shop->status]);
    }

    // ── Dashboard ──────────────────────────────────────────────────────────────
    public function dashboard(Request $request): JsonResponse
    {
        $shop  = $request->user();
        $sid   = $shop->shop_id;
        $today = today();

        return response()->json([
            'status' => true,
            'data'   => [
                'total_items'    => Item::where('shop_id', $sid)->count(),
                'active_items'   => Item::where('shop_id', $sid)->where('status', 'active')->count(),
                'total_orders'   => Order::where('shop_id', $sid)->count(),
                'today_orders'   => Order::where('shop_id', $sid)->whereDate('created_at', $today)->count(),
                'pending_orders' => Order::where('shop_id', $sid)->where('status', 'pending')->count(),
                'today_revenue'  => (float) Order::where('shop_id', $sid)->whereDate('created_at', $today)->where('status', 'delivered')->sum('final_amount'),
                'total_revenue'  => (float) Order::where('shop_id', $sid)->where('status', 'delivered')->sum('final_amount'),
                'avg_rating'     => round(ShopReview::where('shop_id', $sid)->where('is_approved', 1)->avg('rating') ?? 0, 1),
                'recent_orders'  => Order::with(['items', 'user:id,full_name,phone_number'])
                    ->where('shop_id', $sid)
                    ->orderByDesc('created_at')
                    ->take(5)
                    ->get(),
            ],
        ]);
    }

    // ── Shop Orders ────────────────────────────────────────────────────────────
    public function shopOrders(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status'    => 'nullable|string|in:pending,confirmed,preparing,out_for_delivery,delivered,cancelled',
            'per_page'  => 'nullable|integer|min:1|max:50',
            'from_date' => 'nullable|date_format:Y-m-d',
            'to_date'   => 'nullable|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $sid   = $request->user()->shop_id;
        $query = Order::with([
            'items',
            'user:id,full_name,phone_number',
            'assignment.boy:id,full_name,phone_number',
        ])
            ->where('shop_id', $sid)
            ->orderByDesc('created_at');

        if ($request->filled('status'))    $query->where('status', $request->status);
        if ($request->filled('from_date')) $query->whereDate('created_at', '>=', $request->from_date);
        if ($request->filled('to_date'))   $query->whereDate('created_at', '<=', $request->to_date);

        return response()->json([
            'status' => true,
            'data'   => $query->paginate($request->integer('per_page', 20)),
        ]);
    }

    // ── Nearby Shops (public) ──────────────────────────────────────────────────
    public function nearbyShops(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius'    => 'nullable|numeric|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $lat    = (float) $request->latitude;
        $lng    = (float) $request->longitude;
        $radius = (float) ($request->radius ?? AppSetting::get('max_delivery_radius_km', 10));

        $shops = AppOwnerUser::select('*')
            ->selectRaw(
                '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
                [$lat, $lng, $lat]
            )
            ->where('status', 'active')
            ->having('distance', '<=', $radius)
            ->orderBy('distance')
            ->with('images:id,shop_id,image_path,tag')
            ->limit(60)
            ->get()
            ->map(function ($shop) {
                $shop->is_open   = $shop->isOpenNow();
                $shop->logo_url  = $shop->images->where('tag', 'logo')->first()?->image_path;
                $shop->cover_url = $shop->images->where('tag', '!=', 'logo')->first()?->image_path;
                return $shop->makeHidden(['password', 'api_token']);
            });

        return response()->json(['status' => true, 'data' => $shops]);
    }

    // ── Shop Details (public) ──────────────────────────────────────────────────
    public function getShopDetails(int $id): JsonResponse
    {
        $shop = AppOwnerUser::with(['images', 'schedules'])->findOrFail($id);

        $shop->is_open      = $shop->isOpenNow();
        $shop->avg_rating   = round(ShopReview::where('shop_id', $shop->shop_id)->where('is_approved', 1)->avg('rating') ?? 0, 1);
        $shop->review_count = ShopReview::where('shop_id', $shop->shop_id)->where('is_approved', 1)->count();

        // Log shop view
        try {
            $user      = request()->bearerToken() ? \App\Models\AppUser::where('api_token', request()->bearerToken())->first() : null;
            $lastOrder = $user ? Order::where('user_id', $user->id)->latest()->first(['city','state']) : null;
            ShopView::create([
                'shop_id' => $shop->shop_id,
                'user_id' => $user?->id,
                'city'    => $lastOrder?->city,
                'state'   => $lastOrder?->state,
            ]);
        } catch (\Throwable) {}

        return response()->json(['status' => true, 'data' => $shop->makeHidden(['password', 'api_token'])]);
    }

    // ── Upload Image ───────────────────────────────────────────────────────────
    public function uploadImage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tag'   => 'nullable|string|in:logo,cover,gallery',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $shopId   = $request->user()->shop_id;
        $file     = $request->file('image');
        $fileName = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path     = 'shop_images/' . $fileName;

        try {
            $img = Image::make($file->getRealPath())
                ->resize(1200, null, fn($c) => $c->aspectRatio()->upsize());
            Storage::disk('public')->put($path, (string) $img->encode(null, 82));
        } catch (\Exception $e) {
            Log::error('[ShopImage] resize failed: ' . $e->getMessage());
            $path = $file->store('shop_images', 'public');
        }

        $shopImage = ShopImage::create([
            'shop_id'    => $shopId,
            'tag'        => $request->get('tag', 'gallery'),
            'image_path' => $path,
        ]);

        return response()->json([
            'status' => true,
            'data'   => array_merge($shopImage->toArray(), ['url' => asset('storage/' . $path)]),
        ], 201);
    }

    // ── Delete Image ───────────────────────────────────────────────────────────
    public function deleteImage(Request $request, int $id): JsonResponse
    {
        $img = ShopImage::where('id', $id)
            ->where('shop_id', $request->user()->shop_id)
            ->firstOrFail();

        Storage::disk('public')->delete($img->image_path);
        $img->delete();

        return response()->json(['status' => true, 'message' => 'Image deleted.']);
    }

    // ── List Images ────────────────────────────────────────────────────────────
    public function listImages(Request $request): JsonResponse
    {
        $images = ShopImage::where('shop_id', $request->user()->shop_id)
            ->get()
            ->map(fn($i) => array_merge($i->toArray(), ['url' => asset('storage/' . $i->image_path)]));

        return response()->json(['status' => true, 'data' => $images]);
    }

    // ── Schedule ───────────────────────────────────────────────────────────────
    public function updateSchedule(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'schedules'               => 'required|array|size:7',
            'schedules.*.day_of_week' => 'required|integer|min:0|max:6',
            'schedules.*.open_time'   => 'required_unless:schedules.*.is_closed,true|date_format:H:i',
            'schedules.*.close_time'  => 'required_unless:schedules.*.is_closed,true|date_format:H:i',
            'schedules.*.is_closed'   => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $shopId = $request->user()->shop_id;

        foreach ($request->schedules as $s) {
            ShopSchedule::updateOrCreate(
                ['shop_id' => $shopId, 'day_of_week' => $s['day_of_week']],
                [
                    'open_time'  => $s['open_time']  ?? '09:00',
                    'close_time' => $s['close_time'] ?? '21:00',
                    'is_closed'  => $s['is_closed']  ?? false,
                ]
            );
        }

        return response()->json(['status' => true, 'message' => 'Schedule updated.']);
    }

    public function getSchedule(Request $request): JsonResponse
    {
        $shopId    = $request->user()?->shop_id ?? $request->route('id');
        $schedules = ShopSchedule::where('shop_id', $shopId)->orderBy('day_of_week')->get();
        return response()->json(['status' => true, 'data' => $schedules]);
    }

    // ── Shop Menu (details + gallery + menu by subcategory) ───────────────────
    public function shopMenu(int $id): JsonResponse
    {
        $shop = AppOwnerUser::with([
            'images'    => fn($q) => $q->whereNotNull('image_path')->where('image_path', '!=', '')->orderBy('sort_order'),
            'schedules',
        ])
        ->where('status', 'active')
        ->findOrFail($id);

        $rating      = round((float) (ShopReview::where('shop_id', $id)->where('is_approved', 1)->avg('rating') ?? 0), 1);
        $reviewCount = (int) ShopReview::where('shop_id', $id)->where('is_approved', 1)->count();

        $images = $shop->images->map(fn($img) => asset('storage/' . $img->image_path))->values();

        $items = Item::with(['subcategory:id,name,sort_order'])
            ->where('shop_id', $id)
            ->where('status', 1)
            ->orderBy('display_order')
            ->orderBy('item_name')
            ->get();

        $menu = $items
            ->groupBy('subcategory_id')
            ->map(function ($groupItems, $subId) {
                $sub = $groupItems->first()->subcategory;
                return [
                    'id'    => (int) ($subId ?? 0),
                    'name'  => $sub?->name ?? 'Other',
                    'sort'  => (int) ($sub?->sort_order ?? 99),
                    'items' => $groupItems->map(fn($item) => [
                        'id'          => $item->id,
                        'name'        => $item->item_name,
                        'description' => $item->description,
                        'price'       => (float) $item->price,
                        'offer_price' => $item->offer_price ? (float) $item->offer_price : null,
                        'is_veg'      => (bool) $item->is_veg,
                        'is_featured' => (bool) $item->is_featured,
                        'badge'       => $item->badge,
                        'image_url'   => $item->thumbnail_url,
                        'spice_level' => $item->spice_level,
                    ])->values(),
                ];
            })
            ->sortBy('sort')
            ->values();

        return response()->json([
            'status' => true,
            'data'   => [
                'shop' => [
                    'id'           => $shop->shop_id,
                    'name'         => $shop->restaurant_name,
                    'address'      => $shop->restaurant_address,
                    'city'         => $shop->city,
                    'area'         => $shop->popular_area,
                    'rating'       => $rating,
                    'review_count' => $reviewCount,
                    'is_open'      => $shop->isOpenNow(),
                    'images'       => $images,
                    'logo_url'     => null,
                ],
                'menu' => $menu,
            ],
        ]);
    }

    // ── Home: Featured Shops ───────────────────────────────────────────────────
    public function featured(): JsonResponse
    {
        $shops = AppOwnerUser::where('status', 'active')
            ->where('is_featured', true)
            ->orderBy('featured_sort_order')
            ->with(['images' => fn($q) => $q->whereNotNull('image_path')->where('image_path', '!=', '')->orderBy('sort_order')])
            ->withAvg('reviews as avg_rating', 'rating')
            ->withCount('reviews as review_count')
            ->get()
            ->map(fn($s) => $this->formatShop($s));

        return response()->json(['status' => true, 'data' => $shops]);
    }

    // ── Home: Popular Shops ────────────────────────────────────────────────────
    public function popular(): JsonResponse
    {
        $shops = AppOwnerUser::where('status', 'active')
            ->where('is_popular', true)
            ->orderBy('popular_area')
            ->orderBy('popular_sort_order')
            ->with(['images' => fn($q) => $q->whereNotNull('image_path')->where('image_path', '!=', '')->orderBy('sort_order')])
            ->withAvg('reviews as avg_rating', 'rating')
            ->withCount('reviews as review_count')
            ->get();

        $areas = $shops->pluck('popular_area')->filter()->unique()->values();

        return response()->json([
            'status' => true,
            'data'   => [
                'areas' => $areas,
                'shops' => $shops->map(fn($s) => $this->formatShop($s)),
            ],
        ]);
    }

    private function formatShop(AppOwnerUser $s): array
    {
        $img = $s->images->first();
        return [
            'id'           => $s->shop_id,
            'name'         => $s->restaurant_name,
            'city'         => $s->city,
            'area'         => $s->popular_area,
            'image_url'    => $img ? $img->url : null,
            'rating'       => round((float) ($s->avg_rating ?? 0), 1),
            'review_count' => (int) ($s->review_count ?? 0),
            'is_open'      => $s->isOpenNow(),
        ];
    }
}

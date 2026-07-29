<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\AppUser;
use App\Models\UserAddress;
use App\Models\Wishlist;
use App\Models\SearchHistory;
use App\Models\Notification;
use App\Models\ShopReview;
use App\Models\Order;

class UserController extends Controller
{
    public function addAddress(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id'      => 'required|exists:app_users,id',
            'label'        => 'nullable|string|max:50',
            'address_line' => 'required|string|max:255',
            'city'         => 'required|string|max:100',
            'state'        => 'required|string|max:100',
            'pincode'      => 'required|string|max:10',
            'lat'          => 'nullable|numeric|between:-90,90',
            'lng'          => 'nullable|numeric|between:-180,180',
            'is_default'   => 'nullable|boolean',
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);

        if ($request->boolean('is_default')) {
            UserAddress::where('user_id', $request->user_id)->update(['is_default' => false]);
        }
        $isFirstAddress = !UserAddress::where('user_id', $request->user_id)->exists();
        $address = UserAddress::create(array_merge($validator->validated(), ['is_default' => $isFirstAddress || $request->boolean('is_default')]));

        return response()->json(['status' => true, 'message' => 'Address saved', 'data' => $address], 201);
    }

    public function updateAddress(Request $request, int $id): JsonResponse
    {
        $address = UserAddress::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'label'        => 'nullable|string|max:50',
            'address_line' => 'nullable|string|max:255',
            'city'         => 'nullable|string|max:100',
            'state'        => 'nullable|string|max:100',
            'pincode'      => 'nullable|string|max:10',
            'lat'          => 'nullable|numeric|between:-90,90',
            'lng'          => 'nullable|numeric|between:-180,180',
            'is_default'   => 'nullable|boolean',
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);

        if ($request->boolean('is_default') && !$address->is_default) {
            UserAddress::where('user_id', $address->user_id)->update(['is_default' => false]);
        }

        $address->update($validator->validated());
        return response()->json(['status' => true, 'message' => 'Address updated', 'data' => $address->fresh()]);
    }

    public function deleteAddress(int $id): JsonResponse
    {
        $address = UserAddress::findOrFail($id);
        $wasDefault = $address->is_default;
        $userId = $address->user_id;
        $address->delete();
        if ($wasDefault) {
            UserAddress::where('user_id', $userId)->oldest()->first()?->update(['is_default' => true]);
        }
        return response()->json(['status' => true, 'message' => 'Address deleted']);
    }

    public function listAddresses(int $userId): JsonResponse
    {
        $addresses = UserAddress::where('user_id', $userId)->orderByDesc('is_default')->get();
        return response()->json(['status' => true, 'data' => $addresses]);
    }

    public function setDefaultAddress(int $id): JsonResponse
    {
        $address = UserAddress::findOrFail($id);
        UserAddress::where('user_id', $address->user_id)->update(['is_default' => false]);
        $address->update(['is_default' => true]);
        return response()->json(['status' => true, 'message' => 'Default address updated', 'data' => $address->fresh()]);
    }

    public function addToWishlist(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:app_users,id',
            'item_id' => 'required|exists:items,id',
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        Wishlist::firstOrCreate(['user_id' => $request->user_id, 'item_id' => $request->item_id]);
        return response()->json(['status' => true, 'message' => 'Added to wishlist']);
    }

    public function removeFromWishlist(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:app_users,id',
            'item_id' => 'required|exists:items,id',
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        Wishlist::where('user_id', $request->user_id)->where('item_id', $request->item_id)->delete();
        return response()->json(['status' => true, 'message' => 'Removed from wishlist']);
    }

    public function getWishlist(int $userId): JsonResponse
    {
        $items = Wishlist::with(['item:id,item_name,images,status,shop_id,is_veg', 'item.defaultVariant:id,item_id,label,price,offer_price,is_default'])
            ->where('user_id', $userId)
            ->get();
        return response()->json(['status' => true, 'data' => $items]);
    }

    public function getNotifications(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id'  => 'required|exists:app_users,id',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);

        $notifications = Notification::where(fn($q) => $q->where('user_id', $request->user_id)->orWhere('user_type', 'all'))
            ->where('user_type', 'app_user')
            ->orderByDesc('sent_at')
            ->paginate($request->get('per_page', 20));

        return response()->json(['status' => true, 'data' => $notifications]);
    }

    public function markNotificationRead(int $id): JsonResponse
    {
        Notification::where('id', $id)->update(['is_read' => 1]);
        return response()->json(['status' => true, 'message' => 'Marked as read']);
    }

    public function markAllNotificationsRead(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), ['user_id' => 'required|exists:app_users,id']);
        if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        Notification::where('user_id', $request->user_id)->where('is_read', 0)->update(['is_read' => 1]);
        return response()->json(['status' => true, 'message' => 'All notifications marked as read']);
    }

    public function getShopReviews(int $shopId): JsonResponse
    {
        $reviews = ShopReview::with('user:id,full_name,picture')
            ->where('shop_id', $shopId)->where('is_approved', 1)
            ->orderByDesc('created_at')
            ->paginate(10);
        $avgRating = ShopReview::where('shop_id', $shopId)->where('is_approved', 1)->avg('rating');
        return response()->json(['status' => true, 'data' => $reviews, 'avg_rating' => round($avgRating, 1)]);
    }
}

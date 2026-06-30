<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\Banner;

class BannerController extends Controller
{
    public function activeBanners(Request $request): JsonResponse
    {
        $type = $request->get('type');
        $today = now()->toDateString();

        $query = Banner::where('status', 'active')
            ->where(fn($q) => $q->whereNull('start_date')->orWhere('start_date', '<=', $today))
            ->where(fn($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', $today))
            ->orderBy('sort_order')
            ->orderByDesc('id');

        if ($type) $query->where('banner_type', $type);

        $banners = $query->get();

        return response()->json(['status' => true, 'data' => $banners]);
    }

    public function trackClick(int $id): JsonResponse
    {
        Banner::where('id', $id)->increment('click_count');
        return response()->json(['status' => true]);
    }

    public function adminCreate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'nullable|string|max:255',
            'image_url'   => 'required|string|max:500',
            'banner_type' => 'nullable|in:hero,strip,popup,deals,category',
            'target_type' => 'nullable|in:url,shop,category,item,none',
            'target_id'   => 'nullable|integer',
            'target_url'  => 'nullable|url',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'sort_order'  => 'nullable|integer',
            'is_sponsored'=> 'nullable|boolean',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        $banner = Banner::create(array_merge($validator->validated(), ['status' => 'active']));
        return response()->json(['status' => true, 'data' => $banner], 201);
    }

    public function adminUpdate(Request $request, int $id): JsonResponse
    {
        $banner = Banner::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'title'       => 'nullable|string|max:255',
            'image_url'   => 'nullable|string|max:500',
            'banner_type' => 'nullable|in:hero,strip,popup,deals,category',
            'target_type' => 'nullable|in:url,shop,category,item,none',
            'target_id'   => 'nullable|integer',
            'target_url'  => 'nullable|url',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date',
            'sort_order'  => 'nullable|integer',
            'status'      => 'nullable|in:active,inactive',
            'is_sponsored'=> 'nullable|boolean',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        $banner->update($validator->validated());
        return response()->json(['status' => true, 'data' => $banner]);
    }

    public function adminDelete(int $id): JsonResponse
    {
        Banner::findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'Banner deleted']);
    }
}

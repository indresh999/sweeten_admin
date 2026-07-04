<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppOwnerUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminShopHomeLayoutController extends Controller
{
    public function index()
    {
        $featured = AppOwnerUser::where('status', 'active')
            ->where('is_featured', true)
            ->orderBy('featured_sort_order')
            ->with(['images' => fn($q) => $q->whereNotNull('image_path')->where('image_path', '!=', '')->orderBy('sort_order')])
            ->get();

        $popularByArea = AppOwnerUser::where('status', 'active')
            ->where('is_popular', true)
            ->orderBy('popular_area')
            ->orderBy('popular_sort_order')
            ->with(['images' => fn($q) => $q->whereNotNull('image_path')->where('image_path', '!=', '')->orderBy('sort_order')])
            ->get()
            ->groupBy('popular_area');

        $allActive = AppOwnerUser::where('status', 'active')
            ->orderBy('restaurant_name')
            ->select('shop_id', 'restaurant_name', 'city')
            ->get();

        $areas = AppOwnerUser::where('is_popular', true)
            ->whereNotNull('popular_area')
            ->distinct()
            ->pluck('popular_area')
            ->sort()
            ->values();

        return view('admin.shop-home-layout.index', compact('featured', 'popularByArea', 'allActive', 'areas'));
    }

    // POST /admin/shop-home-layout/featured/save
    public function saveFeatured(Request $request): JsonResponse
    {
        $data = $request->validate([
            'items'               => 'required|array',
            'items.*.id'          => 'required|integer|exists:app_owner_shops,shop_id',
            'items.*.is_featured' => 'required|boolean',
            'items.*.sort_order'  => 'required|integer|min:0',
        ]);

        foreach ($data['items'] as $item) {
            AppOwnerUser::where('shop_id', $item['id'])->update([
                'is_featured'         => $item['is_featured'],
                'featured_sort_order' => $item['sort_order'],
            ]);
        }

        return response()->json(['status' => true, 'message' => 'Featured shops saved.']);
    }

    // POST /admin/shop-home-layout/popular/save
    public function savePopular(Request $request): JsonResponse
    {
        $data = $request->validate([
            'items'             => 'required|array',
            'items.*.id'        => 'required|integer|exists:app_owner_shops,shop_id',
            'items.*.is_popular' => 'required|boolean',
            'items.*.area'      => 'nullable|string|max:100',
            'items.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($data['items'] as $item) {
            AppOwnerUser::where('shop_id', $item['id'])->update([
                'is_popular'       => $item['is_popular'],
                'popular_area'     => $item['is_popular'] ? ($item['area'] ?: null) : null,
                'popular_sort_order' => $item['sort_order'],
            ]);
        }

        return response()->json(['status' => true, 'message' => 'Popular shops saved.']);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Banner;

class BannerController extends Controller
{
    // GET /banners?type=hero  — returns active banners for Flutter
    public function activeBanners(Request $request): JsonResponse
    {
        $type = $request->get('type', 'hero');

        $banners = Banner::active()
            ->when($type !== 'all', fn($q) => $q->where('banner_type', $type))
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get([
                'id', 'title', 'heading', 'subtitle', 'cta_label',
                'media_type', 'media_path', 'thumbnail_path',
                'target_type', 'target_id', 'target_url',
                'sort_order', 'is_sponsored',
            ]);

        return response()->json([
            'status' => true,
            'data'   => $banners,
        ]);
    }

    // POST /banners/{id}/click  — fire-and-forget click tracking
    public function trackClick(int $id): JsonResponse
    {
        Banner::where('id', $id)->increment('click_count');
        return response()->json(['status' => true]);
    }
}

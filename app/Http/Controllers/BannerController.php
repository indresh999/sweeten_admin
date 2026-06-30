<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Banner;

class BannerController extends Controller
{
    // GET /banners?type=hero  OR  GET /banners/active
    // Flutter passes ?type=hero; backend just returns all active banners
    public function listActiveBanners(Request $request)
    {
        $today = now()->toDateString();

        $banners = Banner::where('status', 'active')
            ->where(fn($q) => $q->whereNull('start_date')->orWhere('start_date', '<=', $today))
            ->where(fn($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', $today))
            ->orderByDesc('id')
            ->get();

        return response()->json(['status' => true, 'data' => $banners]);
    }

    public function listAllBanners()
    {
        return response()->json(['status' => true, 'data' => Banner::orderByDesc('id')->get()]);
    }

    public function addBanner(Request $request)
    {
        $request->validate(['image_url' => 'required|string', 'title' => 'nullable|string']);
        $banner = Banner::create($request->only('title', 'image_url', 'start_date', 'end_date', 'created_by'));
        return response()->json(['status' => true, 'message' => 'Banner added', 'data' => $banner]);
    }

    public function updateBanner(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);
        $banner->update($request->only('title', 'image_url', 'start_date', 'end_date'));
        return response()->json(['status' => true, 'message' => 'Banner updated', 'data' => $banner]);
    }

    public function activateBanner($id)
    {
        Banner::findOrFail($id)->update(['status' => 'active']);
        return response()->json(['status' => true, 'message' => 'Activated']);
    }

    public function deactivateBanner($id)
    {
        Banner::findOrFail($id)->update(['status' => 'inactive']);
        return response()->json(['status' => true, 'message' => 'Deactivated']);
    }
}

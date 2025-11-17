<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Banner;

class BannerController extends Controller
{
    // Add new banner
    public function addBanner(Request $request)
    {
        $request->validate([
            'title'      => 'nullable|string',
            'image_url'  => 'required|string',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date',
            'created_by' => 'nullable|numeric'
        ]);

        $banner = Banner::create($request->all());

        return response()->json([
            'message' => 'Banner added successfully.',
            'data' => $banner
        ]);
    }

    // Update banner
    public function updateBanner(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        $request->validate([
            'title'      => 'nullable|string',
            'image_url'  => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date',
            'updated_by' => 'nullable|numeric'
        ]);

        $banner->update($request->all());

        return response()->json([
            'message' => 'Banner updated successfully.',
            'data'    => $banner
        ]);
    }

    // Activate banner
    public function activateBanner($id)
    {
        $banner = Banner::findOrFail($id);

        $banner->update([
            'status' => 'active'
        ]);

        return response()->json([
            'message' => 'Banner activated successfully.',
            'data' => $banner
        ]);
    }

    // Deactivate banner
    public function deactivateBanner($id)
    {
        $banner = Banner::findOrFail($id);

        $banner->update([
            'status' => 'inactive'
        ]);

        return response()->json([
            'message' => 'Banner deactivated successfully.',
            'data' => $banner
        ]);
    }

    // List only active + valid banners for app
    public function listActiveBanners()
    {
        $today = now()->toDateString();

        $banners = Banner::where('status', 'active')
            ->where(function ($q) use ($today) {
                $q->whereNull('start_date')
                  ->orWhere('start_date', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', $today);
            })
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json([
            'message' => 'Active banners fetched successfully.',
            'data' => $banners
        ]);
    }

    // List all banners (admin)
    public function listAllBanners()
    {
        $banners = Banner::orderBy('id', 'DESC')->get();

        return response()->json([
            'message' => 'All banners fetched successfully.',
            'data' => $banners
        ]);
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Banner;

class AdminBannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('sort_order')->orderByDesc('id')->paginate(20);
        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banners.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'nullable|string|max:255',
            'heading'     => 'nullable|string|max:255',
            'subtitle'    => 'nullable|string|max:500',
            'cta_label'   => 'nullable|string|max:100',
            'media_type'  => 'required|in:image,gif,video',
            'media'       => 'required|file|max:51200|' . $this->mediaMimes($request->media_type),
            'thumbnail'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'banner_type' => 'required|in:hero,strip,popup,deals,category',
            'target_type' => 'nullable|in:url,shop,category,item,none',
            'target_id'   => 'nullable|integer',
            'target_url'  => 'nullable|url',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'sort_order'  => 'nullable|integer|min:0',
            'status'      => 'nullable|in:active,inactive,draft',
            'is_sponsored'=> 'nullable|boolean',
        ]);

        $mediaPath = $request->file('media')->store('banners', 'public');
        $thumbPath = $request->hasFile('thumbnail')
            ? $request->file('thumbnail')->store('banners/thumbs', 'public')
            : null;

        Banner::create([
            'title'        => $request->title,
            'heading'      => $request->heading,
            'subtitle'     => $request->subtitle,
            'cta_label'    => $request->cta_label,
            'media_type'   => $request->media_type,
            'media_path'   => $mediaPath,
            'image_url'    => $mediaPath,
            'thumbnail_path' => $thumbPath,
            'banner_type'  => $request->banner_type,
            'target_type'  => $request->target_type ?? 'none',
            'target_id'    => $request->target_id,
            'target_url'   => $request->target_url,
            'start_date'   => $request->start_date,
            'end_date'     => $request->end_date,
            'sort_order'   => $request->sort_order ?? 0,
            'status'       => $request->status ?? 'active',
            'is_sponsored' => $request->boolean('is_sponsored'),
        ]);

        return redirect()->route('admin.banners.index')->with('success', 'Banner created successfully.');
    }

    public function edit(int $id)
    {
        $banner = Banner::findOrFail($id);
        return view('admin.banners.form', compact('banner'));
    }

    public function update(Request $request, int $id)
    {
        $banner = Banner::findOrFail($id);

        $rules = [
            'title'       => 'nullable|string|max:255',
            'heading'     => 'nullable|string|max:255',
            'subtitle'    => 'nullable|string|max:500',
            'cta_label'   => 'nullable|string|max:100',
            'media_type'  => 'required|in:image,gif,video',
            'thumbnail'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'banner_type' => 'required|in:hero,strip,popup,deals,category',
            'target_type' => 'nullable|in:url,shop,category,item,none',
            'target_id'   => 'nullable|integer',
            'target_url'  => 'nullable|url',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date',
            'sort_order'  => 'nullable|integer|min:0',
            'status'      => 'nullable|in:active,inactive,draft',
            'is_sponsored'=> 'nullable|boolean',
        ];

        if ($request->hasFile('media')) {
            $rules['media'] = 'required|file|max:51200|' . $this->mediaMimes($request->media_type);
        }

        $request->validate($rules);

        $data = $request->only([
            'title', 'heading', 'subtitle', 'cta_label',
            'media_type', 'banner_type', 'target_type', 'target_id', 'target_url',
            'start_date', 'end_date', 'sort_order', 'status',
        ]);

        $data['is_sponsored'] = $request->boolean('is_sponsored');

        if ($request->hasFile('media')) {
            if ($banner->media_path) Storage::disk('public')->delete($banner->media_path);
            $data['media_path'] = $request->file('media')->store('banners', 'public');
            $data['image_url'] = $data['media_path'];
        }

        if ($request->hasFile('thumbnail')) {
            if ($banner->thumbnail_path) Storage::disk('public')->delete($banner->thumbnail_path);
            $data['thumbnail_path'] = $request->file('thumbnail')->store('banners/thumbs', 'public');
        }

        $banner->update($data);
        return redirect()->route('admin.banners.index')->with('success', 'Banner updated successfully.');
    }

    public function destroy(int $id)
    {
        $banner = Banner::findOrFail($id);
        if ($banner->media_path)     Storage::disk('public')->delete($banner->media_path);
        if ($banner->thumbnail_path) Storage::disk('public')->delete($banner->thumbnail_path);
        $banner->delete();
        return back()->with('success', 'Banner deleted.');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function mediaMimes(string $type): string
    {
        return match ($type) {
            'video' => 'mimes:mp4,mov,avi,webm',
            'gif'   => 'mimes:gif',
            default => 'mimes:jpeg,jpg,png,webp',
        };
    }
}

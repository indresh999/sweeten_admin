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
        $banners = Banner::orderBy('sort_order')->paginate(20);
        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banners.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'nullable|string|max:255',
            'image'       => 'required|image|mimes:jpeg,png,jpg,webp|max:4096',
            'banner_type' => 'required|in:hero,strip,popup,deals,category',
            'target_type' => 'nullable|in:url,shop,category,item,none',
            'target_id'   => 'nullable|integer',
            'target_url'  => 'nullable|url',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'sort_order'  => 'nullable|integer',
            'is_sponsored'=> 'nullable|boolean',
        ]);

        $path = $request->file('image')->store('banners','public');

        Banner::create([
            'title'        => $request->title,
            'image_url'    => asset('storage/'.$path),
            'banner_type'  => $request->banner_type,
            'target_type'  => $request->target_type ?? 'none',
            'target_id'    => $request->target_id,
            'target_url'   => $request->target_url,
            'start_date'   => $request->start_date,
            'end_date'     => $request->end_date,
            'sort_order'   => $request->sort_order ?? 0,
            'is_sponsored' => $request->boolean('is_sponsored'),
            'status'       => 'active',
        ]);

        return redirect()->route('admin.banners.index')->with('success','Banner created.');
    }

    public function edit(int $id)
    {
        $banner = Banner::findOrFail($id);
        return view('admin.banners.edit', compact('banner'));
    }

    public function update(Request $request, int $id)
    {
        $banner = Banner::findOrFail($id);
        $data   = $request->except(['_token','_method','image']);
        if ($request->hasFile('image')) {
            $data['image_url'] = asset('storage/'.$request->file('image')->store('banners','public'));
        }
        $banner->update($data);
        return redirect()->route('admin.banners.index')->with('success','Banner updated.');
    }

    public function destroy(int $id)
    {
        Banner::findOrFail($id)->delete();
        return back()->with('success','Banner deleted.');
    }
}

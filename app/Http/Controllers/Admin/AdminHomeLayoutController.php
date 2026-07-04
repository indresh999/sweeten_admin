<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeSection;
use App\Models\ItemCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminHomeLayoutController extends Controller
{
    public function index()
    {
        $sections = HomeSection::with(['categories' => fn($q) => $q
            ->where('status', 1)
            ->select('id', 'category_name', 'image', 'is_featured', 'home_section_id', 'home_sort_order')
            ->orderBy('home_sort_order')
        ])->orderBy('sort_order')->get();

        $hidden = ItemCategory::where('status', 1)
            ->whereNull('home_section_id')
            ->select('id', 'category_name', 'image', 'is_featured', 'home_section_id', 'home_sort_order')
            ->orderBy('home_sort_order')
            ->orderBy('id')
            ->get();

        return view('admin.home-layout.index', compact('sections', 'hidden'));
    }

    public function save(Request $request): JsonResponse
    {
        $data = $request->validate([
            'sections'                     => 'required|array',
            'sections.*.temp_id'           => 'required|string',
            'sections.*.server_id'         => 'nullable|integer',
            'sections.*.title'             => 'required|string|max:100',
            'sections.*.sort_order'        => 'required|integer|min:0',
            'categories'                   => 'required|array',
            'categories.*.id'              => 'required|integer|exists:item_categories,id',
            'categories.*.section_temp_id' => 'nullable|string',
            'categories.*.sort_order'      => 'required|integer|min:0',
            'deleted_section_ids'          => 'nullable|array',
            'deleted_section_ids.*'        => 'integer',
        ]);

        DB::transaction(function () use ($data) {
            if (!empty($data['deleted_section_ids'])) {
                HomeSection::whereIn('id', $data['deleted_section_ids'])->delete();
            }

            // Upsert sections; build temp_id → server_id map
            $tempToServer = [];
            foreach ($data['sections'] as $s) {
                $section = !empty($s['server_id'])
                    ? (HomeSection::find($s['server_id']) ?? new HomeSection)
                    : new HomeSection;

                $section->fill(['title' => $s['title'], 'sort_order' => $s['sort_order']])->save();
                $tempToServer[$s['temp_id']] = $section->id;
            }

            foreach ($data['categories'] as $cat) {
                $sectionId = null;
                if (!empty($cat['section_temp_id']) && isset($tempToServer[$cat['section_temp_id']])) {
                    $sectionId = $tempToServer[$cat['section_temp_id']];
                }
                ItemCategory::where('id', $cat['id'])->update([
                    'home_section_id' => $sectionId,
                    'home_sort_order' => $cat['sort_order'],
                ]);
            }
        });

        return response()->json(['status' => true, 'message' => 'Home layout saved successfully.']);
    }
}

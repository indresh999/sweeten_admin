<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HomeSection;
use Illuminate\Http\JsonResponse;

class ItemCategoryController extends Controller
{
    // GET /categories — returns active categories grouped by dynamic home sections
    public function index(): JsonResponse
    {
        $sections = HomeSection::with(['categories' => fn($q) => $q
            ->where('status', 1)
            ->orderBy('home_sort_order')
        ])->orderBy('sort_order')->get();

        $mapped = $sections->map(fn($s) => [
            'id'         => $s->id,
            'title'      => $s->title,
            'categories' => $s->categories->map(fn($c) => [
                'id'          => $c->id,
                'name'        => $c->category_name,
                'slug'        => $c->slug,
                'image_url'   => $c->image ? asset($c->image) : null,
                'is_featured' => (bool) $c->is_featured,
            ])->values(),
        ])->filter(fn($s) => count($s['categories']) > 0)->values();

        return response()->json(['status' => true, 'data' => ['sections' => $mapped]]);
    }
}

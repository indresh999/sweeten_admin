<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Models\ItemCategory;

class ItemCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = ItemCategory::where('status', 'active')
            ->select('id', 'category_name', 'image', 'commission_percent', 'commission_type')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn($c) => array_merge($c->toArray(), ['image_url' => $c->image ? asset('storage/' . $c->image) : null]));

        return response()->json(['status' => true, 'data' => $categories]);
    }
}

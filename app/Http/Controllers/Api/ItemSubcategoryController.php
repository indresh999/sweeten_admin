<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\ItemSubcategory;

class ItemSubcategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ItemSubcategory::query();
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        $subs = $query->where('status', 'active')->orderBy('sort_order')->orderBy('id')->get();
        return response()->json(['status' => true, 'data' => $subs]);
    }
}

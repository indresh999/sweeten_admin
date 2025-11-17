<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\ItemCategory;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Validator;

class ItemCategoryController extends Controller
{
    public function index()
    {
        $categories = ItemCategory::where('status', 'active')->get();
        return response()->json(['data' => $categories], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_name' => 'required|string|unique:item_categories,category_name',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $category = ItemCategory::create($validator->validated());
        return response()->json(['message' => 'Category added successfully', 'data' => $category], 201);
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ItemSubcategory;
use App\Models\ItemCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ItemSubcategoryController extends Controller
{
    /**
     * List subcategories (optionally by category)
     */
    public function index(Request $request)
    {
        $query = ItemSubcategory::with('category')
            ->where('status', 1);

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $subcategories = $query->get();

        return response()->json([
            'data' => $subcategories
        ], 200);
    }

    /**
     * Store new subcategory
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:item_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
            'image' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        // Prevent duplicate subcategory under same category
        $exists = ItemSubcategory::where('category_id', $request->category_id)
            ->where('name', $request->name)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Subcategory already exists under this category'
            ], 409);
        }

        $subcategory = ItemSubcategory::create($validator->validated());

        return response()->json([
            'message' => 'Subcategory added successfully',
            'data' => $subcategory
        ], 201);
    }

    /**
     * Show single subcategory
     */
    public function show($id)
    {
        $subcategory = ItemSubcategory::with('category')->find($id);

        if (!$subcategory) {
            return response()->json([
                'message' => 'Subcategory not found'
            ], 404);
        }

        return response()->json([
            'data' => $subcategory
        ], 200);
    }

    /**
     * Update subcategory
     */
    public function update(Request $request, $id)
    {
        $subcategory = ItemSubcategory::find($id);

        if (!$subcategory) {
            return response()->json([
                'message' => 'Subcategory not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'category_id' => 'sometimes|exists:item_categories,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
            'image' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        // Check duplicate on update
        if ($request->has('name') || $request->has('category_id')) {
            $categoryId = $request->category_id ?? $subcategory->category_id;
            $name = $request->name ?? $subcategory->name;

            $exists = ItemSubcategory::where('category_id', $categoryId)
                ->where('name', $name)
                ->where('id', '!=', $subcategory->id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'Subcategory already exists under this category'
                ], 409);
            }
        }

        $subcategory->update($validator->validated());

        return response()->json([
            'message' => 'Subcategory updated successfully',
            'data' => $subcategory
        ], 200);
    }

    /**
     * Delete subcategory
     */
    public function destroy($id)
    {
        $subcategory = ItemSubcategory::find($id);

        if (!$subcategory) {
            return response()->json([
                'message' => 'Subcategory not found'
            ], 404);
        }

        $subcategory->delete();

        return response()->json([
            'message' => 'Subcategory deleted successfully'
        ], 200);
    }
}
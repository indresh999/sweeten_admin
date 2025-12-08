<?php

namespace App\Http\Controllers\Api;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\AppHomeFilter;
use App\Models\ItemSubcategory; // ⬅ ADD THIS
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ItemController extends Controller
{
    // ======================================================
    //  LIST ITEMS BY OWNER
    // ======================================================
    public function listByOwner($shop_id)
    {
        $items = Item::with(['category', 'subcategory'])
            ->where('shop_id', $shop_id)
            ->get();

        return response()->json(['data' => $items], 200);
    }

    // ======================================================
    //  STORE NEW ITEM
    // ======================================================
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shop_id'        => 'required|exists:app_owner_shops,shop_id',
            'category_id'    => 'required|exists:item_categories,id',
            'subcategory_id' => 'nullable|exists:item_subcategories,id',
            'item_name'      => 'required|string|max:100',
            'description'    => 'nullable|string',
            'price'          => 'required|numeric|min:0',
            'offer_price'    => 'nullable|numeric|min:0',
            'min_quantity'   => 'nullable|integer|min:1',
            'weight_or_piece'=> 'nullable|string|max:50',
            'status'         => 'nullable|in:active,inactive',
            'images.*'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // ------------------------------------------------------
        // VALIDATE SUBCATEGORY BELONGS TO CATEGORY
        // ------------------------------------------------------
        if (!empty($data['subcategory_id'])) {
            $sub = ItemSubcategory::find($data['subcategory_id']);

            if ($sub->category_id != $data['category_id']) {
                return response()->json([
                    'errors' => ['subcategory_id' => 'Subcategory does not belong to selected category.']
                ], 422);
            }
        }

        // ------------------------------------------------------
        // IMAGE UPLOAD
        // ------------------------------------------------------
        $imagePaths = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('uploads/items', 'public');
                $imagePaths[] = $path;
            }
        }

        $data['images'] = json_encode($imagePaths);

        // ------------------------------------------------------
        // CREATE ITEM
        // ------------------------------------------------------
        $item = Item::create($data);

        return response()->json([
            'message' => 'Item added successfully',
            'data'    => $item
        ], 201);
    }

    // ======================================================
    //  UPDATE ITEM
    // ======================================================
    public function update(Request $request, $id)
    {
        $item = Item::find($id);

        if (!$item) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'shop_id'        => 'sometimes|required|integer',
            'category_id'    => 'sometimes|required|exists:item_categories,id',
            'subcategory_id' => 'nullable|exists:item_subcategories,id',
            'item_name'      => 'sometimes|required|string|max:255',
            'description'    => 'sometimes|nullable|string',
            'price'          => 'sometimes|required|numeric',
            'offer_price'    => 'sometimes|nullable|numeric',
            'min_quantity'   => 'sometimes|required|integer',
            'weight_or_piece'=> 'sometimes|required|string',
            'status'         => 'sometimes|required|in:active,inactive',
            'images.*'       => 'sometimes|file|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // ------------------------------------------------------
        // CHECK SUBCATEGORY RELATION
        // ------------------------------------------------------
        if (!empty($data['subcategory_id'])) {
            $sub = ItemSubcategory::find($data['subcategory_id']);

            $categoryId = $data['category_id'] ?? $item->category_id;

            if ($sub->category_id != $categoryId) {
                return response()->json([
                    'errors' => ['subcategory_id' => 'Subcategory does not belong to selected category.']
                ], 422);
            }
        }

        // ------------------------------------------------------
        // IMAGE UPDATING
        // ------------------------------------------------------
        if ($request->hasFile('images')) {
            $images = [];

            foreach ($request->file('images') as $image) {
                $path = $image->store('uploads/items', 'public');
                $images[] = $path;
            }

            $data['images'] = json_encode($images);
        }

        // ------------------------------------------------------
        // UPDATE ITEM
        // ------------------------------------------------------
        $item->update($data);

        return response()->json([
            'message' => 'Item updated successfully',
            'item'    => $item
        ]);
    }

    // ======================================================
    //  DELETE ITEM
    // ======================================================
    public function destroy($id)
    {
        $item = Item::findOrFail($id);
        $item->delete();

        return response()->json(['message' => 'Item deleted successfully'], 200);
    }

    // ======================================================
    //  TOGGLE STATUS
    // ======================================================
    public function toggleStatus($id)
    {
        $item = Item::findOrFail($id);
        $item->status = $item->status === 'active' ? 'inactive' : 'active';
        $item->save();

        return response()->json([
            'message' => 'Item status updated',
            'status'  => $item->status
        ], 200);
    }

    // ======================================================
    //  APP HOME FILTER
    // ======================================================
    public function getAppHomeFilter()
    {
        $filters = AppHomeFilter::get();
        return response()->json(['data' => $filters], 200);
    }
}
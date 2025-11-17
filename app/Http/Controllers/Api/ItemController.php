<?php

namespace App\Http\Controllers\Api;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\AppOwnerUser;
use App\Models\ShopImage;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;


class ItemController extends Controller
{
    public function listByOwner($owner_id)
    {
        $items = Item::with('category')->where('owner_id', $owner_id)->get();
        return response()->json(['data' => $items], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'owner_id' => 'required|exists:app_owner_shops,owner_id',
            'category_id' => 'required|exists:item_categories,id',
            'item_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'offer_price' => 'nullable|numeric|min:0',
            'min_quantity' => 'nullable|integer|min:1',
            'weight_or_piece' => 'nullable|string|max:50',
            'status' => 'nullable|in:active,inactive',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        $imagePaths = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('uploads/items', 'public');
                $imagePaths[] = $path;
            }
        }
        $data['images'] = json_encode($imagePaths);

        $item = Item::create($data);

        return response()->json([
            'message' => 'Item added successfully',
            'data' => $item
        ], 201);
    }

    public function update(Request $request, $id)
    {
        // Find the item by ID
        $item = Item::find($id);

        if (!$item) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'owner_id'       => 'sometimes|required|integer',
            'category_id'    => 'sometimes|required|integer',
            'item_name'      => 'sometimes|required|string|max:255',
            'description'    => 'sometimes|nullable|string',
            'price'          => 'sometimes|required|numeric',
            'offer_price'    => 'sometimes|nullable|numeric',
            'min_quantity'   => 'sometimes|required|integer',
            'weight_or_piece'=> 'sometimes|required|string',
            'status'         => 'sometimes|required|in:active,inactive',
            'images'         => 'sometimes|nullable|array',
            'images.*'       => 'sometimes|file|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update fields
        $data = $request->only([
            'owner_id', 'category_id', 'item_name', 'description', 'price', 
            'offer_price', 'min_quantity', 'weight_or_piece', 'status'
        ]);

        // Handle images if uploaded
        if ($request->has('images')) {
            $images = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('items', 'public'); // store in storage/app/public/items
                $images[] = $path;
            }
            $data['images'] = json_encode($images);
        }

        $item->update($data);

        return response()->json(['message' => 'Item updated successfully', 'item' => $item]);
    }

    public function destroy($id)
    {
        $item = Item::findOrFail($id);
        $item->delete();

        return response()->json(['message' => 'Item deleted successfully'], 200);
    }

    public function toggleStatus($id)
    {
        $item = Item::findOrFail($id);
        $item->status = $item->status === 'active' ? 'inactive' : 'active';
        $item->save();

        return response()->json(['message' => 'Item status updated', 'status' => $item->status], 200);
    }
}
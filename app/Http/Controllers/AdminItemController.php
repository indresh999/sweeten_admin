<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemSubcategory;
use App\Models\AppOwnerUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminItemController extends Controller
{
    public function index(Request $request)
    {
        $owners = AppOwnerUser::orderBy('restaurant_name')->get();

        $items = Item::with(['category', 'subcategory', 'owner'])
            ->when($request->shop_id, fn ($q) => $q->where('shop_id', $request->shop_id))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('items.index', compact('items', 'owners'));
    }

    public function create()
    {
        $categories = ItemCategory::where('status', 1)->get();
        $owners = AppOwnerUser::all();

        return view('items.create', compact('categories', 'owners'));
    }

    public function getSubcategories($categoryId)
    {
        return response()->json(
            ItemSubcategory::where('category_id', $categoryId)
                ->where('status', 1)
                ->get()
        );
    }

    // ------------------------------------------------------
    // STORE ITEM
    // ------------------------------------------------------
    public function store(Request $request)
    {
        $request->validate([
            'owner_id'       => 'required',
            'category_id'    => 'required|exists:item_categories,id',
            'subcategory_id' => 'required|exists:item_subcategories,id',
            'item_name'      => 'required|string|max:255',
            'price'          => 'required|numeric',
            'images.*'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $imagePaths = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $filename = Str::uuid() . '.webp';
                $imagePaths[] = $image->storeAs('items', $filename, 'public'); // ✅ FIX
            }
        }

        Item::create([
            'shop_id'         => $request->owner_id,
            'category_id'     => $request->category_id,
            'subcategory_id'  => $request->subcategory_id,
            'item_name'       => $request->item_name,
            'description'     => $request->description,
            'price'           => $request->price,
            'offer_price'     => $request->offer_price,
            'min_quantity'    => $request->min_quantity ?? 1,
            'weight_or_piece' => $request->weight_or_piece,
            'gst_percent'     => $request->gst_percent,
            'status'          => $request->status ?? 'active',
            'images'          => $imagePaths, // ✅ FIX
        ]);

        return redirect()
            ->route('admin.items.index')
            ->with('success', 'Item created successfully.');
    }

    // ------------------------------------------------------
    // SHOW ITEM
    // ------------------------------------------------------
    public function show($id)
    {
        $item = Item::with(['category', 'subcategory', 'owner'])
            ->findOrFail($id);

        return view('items.show', compact('item'));
    }

    // ------------------------------------------------------
    // EDIT ITEM
    // ------------------------------------------------------
    public function edit($id)
    {
        $item = Item::findOrFail($id);

        $categories = ItemCategory::where('status', 1)->get();
        $owners = AppOwnerUser::all();

        $subcategories = ItemSubcategory::where('category_id', $item->category_id)
            ->where('status', 1)
            ->get();

        return view('items.edit', compact(
            'item',
            'categories',
            'owners',
            'subcategories'
        ));
    }

    // ------------------------------------------------------
    // UPDATE ITEM
    // ------------------------------------------------------
    public function update(Request $request, $id)
    {
        $request->validate([
            'owner_id'       => 'required',
            'category_id'    => 'required|exists:item_categories,id',
            'subcategory_id' => 'required|exists:item_subcategories,id',
            'item_name'      => 'required|string|max:255',
            'price'          => 'required|numeric',
            'images.*'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $item = Item::findOrFail($id);

        // Keep old images if no new upload
        $imagePaths = $item->images ?? [];

        if ($request->hasFile('images')) {

            // Delete old images
            foreach ($imagePaths as $img) {
                Storage::disk('public')->delete($img); // ✅ FIX
            }

            $imagePaths = [];

            foreach ($request->file('images') as $image) {
                $filename = Str::uuid() . '.webp';
                $imagePaths[] = $image->storeAs('items', $filename, 'public'); // ✅ FIX
            }
        }

        $item->update([
            'shop_id'         => $request->owner_id,
            'category_id'     => $request->category_id,
            'subcategory_id'  => $request->subcategory_id,
            'item_name'       => $request->item_name,
            'description'     => $request->description,
            'price'           => $request->price,
            'offer_price'     => $request->offer_price,
            'min_quantity'    => $request->min_quantity ?? 1,
            'weight_or_piece' => $request->weight_or_piece,
            'gst_percent'     => $request->gst_percent,
            'status'          => $request->status ?? 'active',
            'images'          => $imagePaths, // ✅ FIX
        ]);

        return redirect()
            ->route('admin.items.index')
            ->with('success', 'Item updated successfully.');
    }

    // ------------------------------------------------------
    // DELETE ITEM
    // ------------------------------------------------------
    public function destroy($id)
    {
        $item = Item::findOrFail($id);

        foreach ($item->images ?? [] as $img) {
            Storage::disk('public')->delete($img); // ✅ FIX
        }

        $item->delete();

        return redirect()
            ->route('admin.items.index')
            ->with('success', 'Item deleted successfully.');
    }
}
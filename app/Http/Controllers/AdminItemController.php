<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\AppOwnerUser; // shop owners
use Illuminate\Http\Request;

class AdminItemController extends Controller
{
   public function index(Request $request)
{
    $owners = AppOwnerUser::orderBy('restaurant_name')->get();

    $items = Item::with(['category', 'subcategory', 'owner'])
        ->when($request->shop_id, function ($query) use ($request) {
            $query->where('shop_id', $request->shop_id);
        })
        ->orderBy('id', 'desc')
        ->paginate(10)
        ->withQueryString(); // keep filter on pagination

    return view('items.index', compact('items', 'owners'));
}

    public function create()
    {
        $categories = ItemCategory::where('status',1)->get();
        $owners = AppOwnerUser::all(); // all shops
        return view('items.create', compact('categories','owners'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'owner_id'       => 'required',
            'category_id'    => 'required',
            'item_name'      => 'required|string',
            'price'          => 'required|numeric',
            'gst_percent'    => 'nullable|numeric',
            'images'         => 'nullable|image|max:2048'
        ]);

        $imageName = null;

        if ($request->hasFile('images')) {
            $imageName = time().'_item.'.$request->images->extension();
            $request->images->move(public_path('uploads/items'), $imageName);
        }

        Item::create([
            'owner_id'        => $request->owner_id,
            'category_id'     => $request->category_id,
            'item_name'       => $request->item_name,
            'description'     => $request->description,
            'price'           => $request->price,
            'offer_price'     => $request->offer_price,
            'min_quantity'    => $request->min_quantity,
            'weight_or_piece' => $request->weight_or_piece,
            'gst_percent'     => $request->gst_percent,
            'status'          => $request->status ?? 1,
            'images'          => $imageName
        ]);

        return redirect()->route('items.index')->with('success','Item created successfully.');
    }

    public function edit($id)
    {
        $item = Item::findOrFail($id);
        $categories = ItemCategory::where('status',1)->get();
        $owners = AppOwnerUser::all();
        return view('items.edit', compact('item','categories','owners'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'item_name' => 'required|string',
            'price'     => 'required|numeric'
        ]);

        $item = Item::findOrFail($id);

        $imageName = $item->images;

        if ($request->hasFile('images')) {
            $imageName = time().'_item.'.$request->images->extension();
            $request->images->move(public_path('uploads/items'), $imageName);
        }

        $item->update([
            'owner_id'        => $request->owner_id,
            'category_id'     => $request->category_id,
            'item_name'       => $request->item_name,
            'description'     => $request->description,
            'price'           => $request->price,
            'offer_price'     => $request->offer_price,
            'min_quantity'    => $request->min_quantity,
            'weight_or_piece' => $request->weight_or_piece,
            'gst_percent'     => $request->gst_percent,
            'status'          => $request->status ?? 1,
            'images'          => $imageName
        ]);

        return redirect()->route('items.index')->with('success','Item updated successfully.');
    }

    public function destroy($id)
    {
        Item::findOrFail($id)->delete();
        return redirect()->route('items.index')->with('success','Item deleted successfully.');
    }

    public function show($id)
    {
        $item = Item::with(['category', 'subcategory', 'owner'])->findOrFail($id);

        return view('items.show', compact('item'));
    }
}
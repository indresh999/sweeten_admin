<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppOwnerUser;

class ShopController extends Controller
{

    public function index(Request $request)
    {
        $shops = AppOwnerUser::with('images')->paginate(10);
        $totalStores = AppOwnerUser::count();
        return view('shops.shops', compact('shops', 'totalStores'));
    }

    public function show($id)
    {
        $shop = AppOwnerUser::with('images')->findOrFail($id);

        // Load shop orders
        $orders = \App\Models\Order::with(['user', 'items.item'])
            ->where('shop_id', $id)
            ->orderBy('id', 'desc')
            ->get();

        // Load shop items
        $items = \App\Models\Item::with(['category'])
            ->where('owner_id', $id)
            ->orderBy('id', 'desc')
            ->get();

        return view('shops.shop_details', compact('shop', 'orders', 'items'));
    }


    public function create()
    {
        $assets = [];
        return view('dashboards.shop_form', compact('assets'));
    }

public function edit($id)
{
    $shop = AppOwnerUser::with('images')->findOrFail($id);
    $assets = [];
    return view('dashboards.shop_form', compact('shop', 'assets', 'id'));
}


    public function update(Request $request, $id)
    {
        $shop = AppOwnerUser::findOrFail($id);

        // Validate only allowed fields
        $validated = $request->validate([
            'restaurant_name' => 'nullable|string',
            'phone_number' => 'nullable|string|max:20',
            'gst_number'   => 'nullable|string|max:50',
            'pan_number'   => 'nullable|string|max:50',
            'images.*'     => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Update ONLY specific fields
        $shop->update([
            'restaurant_name' => $validated['restaurant_name'] ?? $shop->restaurant_name,
            'phone_number' => $validated['phone_number'] ?? $shop->phone_number,
            'gst_number'   => $validated['gst_number'] ?? $shop->gst_number,
            'pan_number'   => $validated['pan_number'] ?? $shop->pan_number,
        ]);

        // Handle image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('uploads/shops', 'public');

                $shop->images()->create([
                    'image_path' => 'storage/' . $path,
                ]);
            }
        }

        return redirect()->route('shops')->with('success', 'Shop updated successfully!');
    }
public function orderDetails($id)
{
    $order = \App\Models\Order::with([
        'items.item',
        'user',
        'owner',
        'assignment.boy'
    ])->findOrFail($id);

    return view('shops.order_details', compact('order'));
}

}
   

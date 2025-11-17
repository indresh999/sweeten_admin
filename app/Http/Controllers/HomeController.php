<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppOwnerUser;
use App\Models\Order;

class HomeController extends Controller
{
    public function signin()
    {
        return view('auth.login');
    }
  public function index(Request $request)
{
    $assets = ['chart', 'animation'];

    // SHOPS
    $shops = AppOwnerUser::with('images')->paginate(10);
    $totalStores = AppOwnerUser::count();

    // ORDERS FILTER
    $from = $request->input('from_date');
    $to = $request->input('to_date');

    $ordersQuery = Order::with(['user', 'owner'])
        ->orderBy('created_at', 'desc');

    if ($from) {
        $ordersQuery->whereDate('created_at', '>=', $from);
    }
    if ($to) {
        $ordersQuery->whereDate('created_at', '<=', $to);
    }

    // Show recent 10 by default
    $orders = $ordersQuery->paginate(10);

    return view('dashboards.dashboard', compact(
        'assets',
        'shops',
        'totalStores',
        'orders',
        'from',
        'to'
    ));
}

    public function show($id)
    {
        $shop = AppOwnerUser::with('images')->findOrFail($id);
        return view('dashboards.store_details', compact('shop'));
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
        $shop->update($request->except('images'));

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('uploads/shops', 'public');
                $shop->images()->create(['image_path' => 'storage/' . $path]);
            }
        }

        return redirect()->route('shops.index')->with('success', 'Shop updated successfully!');
    }

}
   

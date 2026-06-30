<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ItemCategory;
use App\Models\AppSetting;
use App\Models\Order;

class AdminTaxController extends Controller
{
    public function index()
    {
        $globalGst  = AppSetting::get('global_gst_percent', 0);
        $categories = ItemCategory::select('id','category_name','commission_percent','commission_type')->get();
        $settings   = [
            'global_gst_percent'     => AppSetting::get('global_gst_percent', 0),
            'delivery_base_charge'   => AppSetting::get('delivery_base_charge', 30),
            'free_delivery_above'    => AppSetting::get('free_delivery_above', 199),
            'min_order_amount'       => AppSetting::get('min_order_amount', 0),
            'default_delivery_minutes'=> AppSetting::get('default_delivery_minutes', 45),
            'delivery_earn_per_order'=> AppSetting::get('delivery_earn_per_order', 30),
            'platform_commission_pct'=> AppSetting::get('platform_commission_pct', 5),
        ];
        return view('admin.tax.index', compact('settings','categories','globalGst'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'global_gst_percent'     => 'nullable|numeric|min:0|max:100',
            'delivery_base_charge'   => 'nullable|numeric|min:0',
            'free_delivery_above'    => 'nullable|numeric|min:0',
            'min_order_amount'       => 'nullable|numeric|min:0',
            'default_delivery_minutes'=> 'nullable|integer|min:1',
            'delivery_earn_per_order'=> 'nullable|numeric|min:0',
            'platform_commission_pct'=> 'nullable|numeric|min:0|max:100',
        ]);

        $keys = ['global_gst_percent','delivery_base_charge','free_delivery_above','min_order_amount',
                 'default_delivery_minutes','delivery_earn_per_order','platform_commission_pct'];

        foreach ($keys as $key) {
            if ($request->has($key)) AppSetting::set($key, $request->input($key));
        }

        return back()->with('success','Tax & fee settings updated.');
    }

    public function invoice(int $orderId)
    {
        $order = Order::with([
            'user:id,full_name,email,phone_number',
            'owner:shop_id,restaurant_name,restaurant_address,gst_number,phone_number',
            'items',
        ])->findOrFail($orderId);

        return view('admin.tax.invoice', compact('order'));
    }

    public function invoicePdf(int $orderId)
    {
        $order = Order::with([
            'user:id,full_name,email,phone_number',
            'owner:shop_id,restaurant_name,restaurant_address,gst_number',
            'items',
        ])->findOrFail($orderId);

        // Return printable HTML — browser prints to PDF
        return view('admin.tax.invoice-print', compact('order'));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\AppOwnerUser;
use App\Models\DeliveryBoy;
use App\Models\DeliveryEarning;

class AdminEarningsController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to',   now()->toDateString());

        $summary = [
            'total_revenue'      => (float) Order::where('status','delivered')->whereBetween(DB::raw('DATE(created_at)'),[$from,$to])->sum('final_amount'),
            'total_orders'       => Order::where('status','delivered')->whereBetween(DB::raw('DATE(created_at)'),[$from,$to])->count(),
            'total_gst'          => (float) Order::where('status','delivered')->whereBetween(DB::raw('DATE(created_at)'),[$from,$to])->sum('gst_amount'),
            'total_delivery_fee' => (float) Order::where('status','delivered')->whereBetween(DB::raw('DATE(created_at)'),[$from,$to])->sum('delivery_charge'),
            'total_handling'     => (float) Order::where('status','delivered')->whereBetween(DB::raw('DATE(created_at)'),[$from,$to])->sum('handling_fee'),
            'total_discount'     => (float) Order::where('status','delivered')->whereBetween(DB::raw('DATE(created_at)'),[$from,$to])->sum('discount_amount'),
            'delivery_payouts'   => (float) DeliveryEarning::whereBetween(DB::raw('DATE(created_at)'),[$from,$to])->sum('net_earning'),
            'pending_payouts'    => (float) DeliveryEarning::where('is_paid',false)->sum('net_earning'),
        ];

        $revenueByDay = Order::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(final_amount) as revenue'), DB::raw('COUNT(*) as orders'))
            ->where('status','delivered')->whereBetween(DB::raw('DATE(created_at)'),[$from,$to])
            ->groupBy('date')->orderBy('date')->get();

        return view('admin.earnings.index', compact('summary','revenueByDay','from','to'));
    }

    public function platform(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to',   now()->toDateString());

        $data = Order::select('id','final_amount','gst_amount','delivery_charge','handling_fee','packing_fee','discount_amount','wallet_used','created_at')
            ->with('owner:shop_id,restaurant_name','user:id,full_name')
            ->where('status','delivered')->whereBetween(DB::raw('DATE(created_at)'),[$from,$to])
            ->latest()->paginate(20)->withQueryString();

        return view('admin.earnings.platform', compact('data','from','to'));
    }

    public function vendors(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to',   now()->toDateString());

        $vendors = AppOwnerUser::select('app_owner_shops.*')
            ->selectRaw('COUNT(orders.id) as order_count')
            ->selectRaw('SUM(orders.final_amount) as gross_revenue')
            ->selectRaw('SUM(orders.gst_amount) as total_gst')
            ->selectRaw('SUM(orders.discount_amount) as total_discount')
            ->leftJoin('orders','orders.shop_id','=','app_owner_shops.shop_id')
            ->where('orders.status','delivered')
            ->whereBetween(DB::raw('DATE(orders.created_at)'),[$from,$to])
            ->groupBy('app_owner_shops.shop_id')
            ->orderByDesc('gross_revenue')
            ->paginate(20)->withQueryString();

        return view('admin.earnings.vendors', compact('vendors','from','to'));
    }

    public function delivery(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to',   now()->toDateString());

        $boys = DeliveryBoy::select('delivery_boys.*')
            ->selectRaw('COUNT(de.id) as delivery_count')
            ->selectRaw('SUM(de.net_earning) as total_earned')
            ->selectRaw('SUM(CASE WHEN de.is_paid=0 THEN de.net_earning ELSE 0 END) as pending_amount')
            ->leftJoin('delivery_earnings as de','de.delivery_boy_id','=','delivery_boys.id')
            ->whereBetween(DB::raw('DATE(de.created_at)'),[$from,$to])
            ->groupBy('delivery_boys.id')
            ->orderByDesc('total_earned')
            ->paginate(20)->withQueryString();

        return view('admin.earnings.delivery', compact('boys','from','to'));
    }

    public function payout(Request $request)
    {
        $request->validate(['delivery_boy_id'=>'required|exists:delivery_boys,id']);
        DeliveryEarning::where('delivery_boy_id',$request->delivery_boy_id)->where('is_paid',false)->update(['is_paid'=>true,'paid_at'=>now()]);
        return back()->with('success','Payout marked as paid.');
    }

    public function export(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to',   now()->toDateString());

        $orders = Order::with(['user:id,full_name','owner:shop_id,restaurant_name'])
            ->where('status','delivered')->whereBetween(DB::raw('DATE(created_at)'),[$from,$to])->get();

        $csv = "Order ID,Date,Customer,Vendor,Subtotal,GST,Delivery,Handling,Discount,Wallet Used,Final Amount\n";
        foreach ($orders as $o) {
            $csv .= implode(',', [
                $o->id, $o->created_at->format('Y-m-d'),
                '"'.($o->user?->full_name ?? '').'"',
                '"'.($o->owner?->restaurant_name ?? '').'"',
                $o->total_amount, $o->gst_amount, $o->delivery_charge,
                $o->handling_fee, $o->discount_amount, $o->wallet_used, $o->final_amount,
            ])."\n";
        }
        return response($csv,200,['Content-Type'=>'text/csv','Content-Disposition'=>'attachment;filename="earnings_'.$from.'_'.$to.'.csv"']);
    }
}

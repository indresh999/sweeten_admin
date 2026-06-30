<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AppUser;
use App\Models\AppOwnerUser;
use App\Models\Order;
use App\Models\DeliveryBoy;
use App\Models\Item;
use App\Models\DeliveryEarning;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->get('from', today()->startOfMonth()->toDateString());
        $to   = $request->get('to',   today()->toDateString());

        $stats = [
            'total_customers'    => AppUser::where('is_blocked', 0)->count(),
            'total_vendors'      => AppOwnerUser::count(),
            'active_vendors'     => AppOwnerUser::where('status', 'active')->count(),
            'pending_vendors'    => AppOwnerUser::where('status', 'pending')->count(),
            'total_orders'       => Order::count(),
            'today_orders'       => Order::whereDate('created_at', today())->count(),
            'pending_orders'     => Order::where('status', 'pending')->count(),
            'delivered_orders'   => Order::where('status', 'delivered')->count(),
            'cancelled_orders'   => Order::where('status', 'cancelled')->count(),
            'total_revenue'      => (float) Order::where('status', 'delivered')->sum('final_amount'),
            'today_revenue'      => (float) Order::where('status', 'delivered')->whereDate('created_at', today())->sum('final_amount'),
            'month_revenue'      => (float) Order::where('status', 'delivered')->whereMonth('created_at', now()->month)->sum('final_amount'),
            'range_revenue'      => (float) Order::where('status', 'delivered')->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])->sum('final_amount'),
            'total_items'        => Item::count(),
            'active_items'       => Item::where('status', 'active')->count(),
            'total_delivery_boys'=> DeliveryBoy::count(),
            'online_delivery_boys'=> DeliveryBoy::where('status', 'online')->count(),
            'total_commission'   => (float) DeliveryEarning::sum('net_earning'),
        ];

        $recentOrders = Order::with(['user:id,full_name,phone_number', 'owner:shop_id,restaurant_name'])
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->latest()->take(10)->get();

       $topVendors = AppOwnerUser::query()

    ->leftJoinSub(

        Order::select(

                'shop_id',

                DB::raw('COUNT(*) as order_count'),

                DB::raw('SUM(final_amount) as revenue')

            )

            ->where('status', 'delivered')

            ->groupBy('shop_id'),

        'order_stats',

        function ($join) {

            $join->on('order_stats.shop_id', '=', 'app_owner_shops.shop_id');

        }

    )

    ->select(

        'app_owner_shops.*',

        DB::raw('COALESCE(order_stats.order_count,0) as order_count'),

        DB::raw('COALESCE(order_stats.revenue,0) as revenue')

    )

    ->orderByDesc('revenue')

    ->take(5)

    ->get();

        $chartData = Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as orders'),
            DB::raw('SUM(final_amount) as revenue')
        )
        ->where('status', 'delivered')
        ->where('created_at', '>=', now()->subDays(29))
        ->groupBy('date')->orderBy('date')->get();

        return view('admin.dashboard.index', compact('stats', 'recentOrders', 'topVendors', 'chartData', 'from', 'to'));
    }

    public function stats()
    {
        return response()->json([
            'total_customers'   => AppUser::where('is_blocked', 0)->count(),
            'total_vendors'     => AppOwnerUser::count(),
            'today_orders'      => Order::whereDate('created_at', today())->count(),
            'today_revenue'     => (float) Order::where('status', 'delivered')->whereDate('created_at', today())->sum('final_amount'),
        ]);
    }

    public function chart(Request $request)
    {
        $days = (int) $request->get('days', 7);
        $data = Order::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as orders'), DB::raw('SUM(final_amount) as revenue'))
            ->where('status', 'delivered')
            ->where('created_at', '>=', now()->subDays($days - 1))
            ->groupBy('date')->orderBy('date')->get();
        return response()->json($data);
    }
}

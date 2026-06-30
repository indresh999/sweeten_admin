<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\AppOwnerUser;

class AdminReportsController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }

    public function orders(Request $request)
    {
        $from = $request->get('from', now()->subDays(29)->toDateString());
        $to   = $request->get('to',   now()->toDateString());

        $daily = Order::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status="delivered" THEN 1 ELSE 0 END) as delivered'),
                DB::raw('SUM(CASE WHEN status="cancelled" THEN 1 ELSE 0 END) as cancelled'))
            ->whereBetween(DB::raw('DATE(created_at)'),[$from,$to])
            ->groupBy('date')->orderBy('date')->get();

        $statusBreakdown = Order::whereBetween(DB::raw('DATE(created_at)'),[$from,$to])
            ->groupBy('status')
            ->select('status', DB::raw('COUNT(*) as count'))
            ->get();

        $paymentBreakdown = Order::whereBetween(DB::raw('DATE(created_at)'),[$from,$to])
            ->groupBy('payment_method')
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(final_amount) as total'))
            ->get();

        return view('admin.reports.orders', compact('daily','statusBreakdown','paymentBreakdown','from','to'));
    }

    public function revenue(Request $request)
    {
        $from  = $request->get('from', now()->subDays(29)->toDateString());
        $to    = $request->get('to',   now()->toDateString());
        $group = $request->get('group', 'day');

        $groupByExpr = match($group) {
            'week'  => DB::raw('YEARWEEK(created_at) as period'),
            'month' => DB::raw('DATE_FORMAT(created_at, "%Y-%m") as period'),
            default => DB::raw('DATE(created_at) as period'),
        };

        $data = Order::select($groupByExpr,
                DB::raw('SUM(final_amount) as revenue'),
                DB::raw('SUM(gst_amount) as gst'),
                DB::raw('SUM(delivery_charge) as delivery'),
                DB::raw('SUM(discount_amount) as discount'),
                DB::raw('COUNT(*) as orders'))
            ->where('status','delivered')
            ->whereBetween(DB::raw('DATE(created_at)'),[$from,$to])
            ->groupBy('period')->orderBy('period')->get();

        return view('admin.reports.revenue', compact('data','from','to','group'));
    }

    public function vendors(Request $request)
    {
        return redirect()->route('admin.earnings.vendors', $request->only(['from','to']));
    }

    public function delivery(Request $request)
    {
        return redirect()->route('admin.earnings.delivery', $request->only(['from','to']));
    }
}

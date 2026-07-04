<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Item;
use App\Models\AppOwnerUser;
use App\Models\ItemView;
use App\Models\ShopView;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ItemCategory;

class AdminMonitorController extends Controller
{
    // ── Main Dashboard ─────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $from     = $request->get('from', now()->subDays(29)->toDateString());
        $to       = $request->get('to',   now()->toDateString());
        $cityFilter    = $request->get('city');
        $categoryFilter= $request->get('category_id');
        $vendorFilter  = $request->get('shop_id');

        // ── Top Sold Products ───────────────────────────────────────────────
        $topSoldQuery = OrderItem::select(
                'item_id',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(item_total) as total_revenue'),
                DB::raw('COUNT(DISTINCT order_id) as order_count')
            )
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'delivered')
            ->whereBetween(DB::raw('DATE(orders.created_at)'), [$from, $to]);

        if ($vendorFilter) {
            $topSoldQuery->where('orders.shop_id', $vendorFilter);
        }
        if ($categoryFilter) {
            $topSoldQuery->join('items as i_cat', 'i_cat.id', '=', 'order_items.item_id')
                         ->where('i_cat.category_id', $categoryFilter);
        }

        $topSold = $topSoldQuery->groupBy('item_id')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                $item = Item::select('id','item_name','price','offer_price','status','shop_id')
                    ->with('owner:shop_id,restaurant_name')
                    ->find($row->item_id);
                $row->item = $item;
                return $row;
            });

        // ── Most Viewed Products ────────────────────────────────────────────
        $topViewedQuery = ItemView::select(
                'item_id',
                DB::raw('COUNT(*) as view_count'),
                DB::raw('COUNT(DISTINCT user_id) as unique_visitors')
            )
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to]);

        if ($cityFilter) {
            $topViewedQuery->where('city', $cityFilter);
        }
        if ($vendorFilter) {
            $topViewedQuery->where('shop_id', $vendorFilter);
        }
        if ($categoryFilter) {
            $topViewedQuery->join('items as iv_items', 'iv_items.id', '=', 'item_views.item_id')
                           ->where('iv_items.category_id', $categoryFilter);
        }

        $topViewed = $topViewedQuery->groupBy('item_id')
            ->orderByDesc('view_count')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                $item = Item::select('id','item_name','price','offer_price','shop_id')
                    ->with('owner:shop_id,restaurant_name')
                    ->find($row->item_id);
                $row->item = $item;
                return $row;
            });

        // ── Most Visited Shops ──────────────────────────────────────────────
        $topShopsQuery = ShopView::select(
                'shop_id',
                DB::raw('COUNT(*) as visit_count'),
                DB::raw('COUNT(DISTINCT user_id) as unique_visitors')
            )
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to]);

        if ($cityFilter) {
            $topShopsQuery->where('city', $cityFilter);
        }
        if ($vendorFilter) {
            $topShopsQuery->where('shop_id', $vendorFilter);
        }

        $topShops = $topShopsQuery->groupBy('shop_id')
            ->orderByDesc('visit_count')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                $row->shop = AppOwnerUser::select('shop_id','restaurant_name','city','status')
                    ->find($row->shop_id);
                return $row;
            });

        // ── View Location Breakdown ─────────────────────────────────────────
        $viewsByCity = ItemView::select('city', DB::raw('COUNT(*) as view_count'))
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->whereNotNull('city')
            ->groupBy('city')
            ->orderByDesc('view_count')
            ->limit(10)
            ->get();

        // ── Date-wise Revenue Chart ─────────────────────────────────────────
        $dateChart = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(CASE WHEN status="delivered" THEN final_amount ELSE 0 END) as revenue')
            )
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to]);

        if ($vendorFilter) {
            $dateChart->where('shop_id', $vendorFilter);
        }

        $dateChart = $dateChart->groupBy('date')->orderBy('date')->get();

        // ── Date-wise Item Views Chart ──────────────────────────────────────
        $viewChart = ItemView::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as views')
            )
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->groupBy('date')->orderBy('date')->get();

        // ── Summary Counters ────────────────────────────────────────────────
        $summary = [
            'total_item_views'  => ItemView::whereBetween(DB::raw('DATE(created_at)'), [$from, $to])->count(),
            'total_shop_visits' => ShopView::whereBetween(DB::raw('DATE(created_at)'), [$from, $to])->count(),
            'total_orders'      => Order::whereBetween(DB::raw('DATE(created_at)'), [$from, $to])->count(),
            'delivered_orders'  => Order::where('status','delivered')->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])->count(),
            'total_revenue'     => Order::where('status','delivered')->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])->sum('final_amount'),
        ];

        // ── Filter Options ──────────────────────────────────────────────────
        $categories = ItemCategory::select('id','category_name')->orderBy('category_name')->get();
        $vendors    = AppOwnerUser::select('shop_id','restaurant_name','city')
            ->where('status','active')->orderBy('restaurant_name')->get();
        $cities     = ShopView::select('city')
            ->whereNotNull('city')->distinct()->pluck('city')->sort()->values();

        return view('admin.monitor.index', compact(
            'from','to','cityFilter','categoryFilter','vendorFilter',
            'topSold','topViewed','topShops','viewsByCity',
            'dateChart','viewChart','summary',
            'categories','vendors','cities'
        ));
    }

    // ── AJAX: date-wise chart data ──────────────────────────────────────────
    public function chartData(Request $request)
    {
        $from   = $request->get('from', now()->subDays(29)->toDateString());
        $to     = $request->get('to',   now()->toDateString());
        $type   = $request->get('type', 'revenue'); // revenue | views | visits

        if ($type === 'revenue') {
            $data = Order::select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(CASE WHEN status="delivered" THEN final_amount ELSE 0 END) as value')
                )
                ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
                ->groupBy('date')->orderBy('date')->get();
        } elseif ($type === 'views') {
            $data = ItemView::select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as value')
                )
                ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
                ->groupBy('date')->orderBy('date')->get();
        } else {
            $data = ShopView::select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as value')
                )
                ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
                ->groupBy('date')->orderBy('date')->get();
        }

        return response()->json(['status' => true, 'data' => $data]);
    }

    // ── Top Products Table (paginated, with more filters) ───────────────────
    public function topProducts(Request $request)
    {
        $from     = $request->get('from', now()->subDays(29)->toDateString());
        $to       = $request->get('to',   now()->toDateString());
        $mode     = $request->get('mode', 'sold'); // sold | viewed
        $categoryFilter = $request->get('category_id');
        $vendorFilter   = $request->get('shop_id');
        $cityFilter     = $request->get('city');

        if ($mode === 'sold') {
            $query = OrderItem::select(
                    'order_items.item_id',
                    DB::raw('SUM(order_items.quantity) as total_qty'),
                    DB::raw('SUM(order_items.item_total) as total_revenue'),
                    DB::raw('COUNT(DISTINCT order_items.order_id) as order_count')
                )
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->where('orders.status', 'delivered')
                ->whereBetween(DB::raw('DATE(orders.created_at)'), [$from, $to]);

            if ($vendorFilter)  $query->where('orders.shop_id', $vendorFilter);
            if ($categoryFilter) {
                $query->join('items as cat_i', 'cat_i.id', '=', 'order_items.item_id')
                      ->where('cat_i.category_id', $categoryFilter);
            }

            $rows = $query->groupBy('order_items.item_id')
                ->orderByDesc('total_qty')
                ->paginate(20);
        } else {
            $query = ItemView::select(
                    'item_id',
                    DB::raw('COUNT(*) as view_count'),
                    DB::raw('COUNT(DISTINCT user_id) as unique_visitors')
                )
                ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to]);

            if ($cityFilter)    $query->where('city', $cityFilter);
            if ($vendorFilter)  $query->where('shop_id', $vendorFilter);
            if ($categoryFilter) {
                $query->join('items as vcat_i', 'vcat_i.id', '=', 'item_views.item_id')
                      ->where('vcat_i.category_id', $categoryFilter);
            }

            $rows = $query->groupBy('item_id')
                ->orderByDesc('view_count')
                ->paginate(20);
        }

        // Attach item details
        $itemIds = $rows->pluck('item_id')->toArray();
        $items   = Item::with(['owner:shop_id,restaurant_name', 'category:id,category_name'])
            ->whereIn('id', $itemIds)->get()->keyBy('id');

        $categories = ItemCategory::select('id','category_name')->orderBy('category_name')->get();
        $vendors    = AppOwnerUser::select('shop_id','restaurant_name')->where('status','active')->orderBy('restaurant_name')->get();
        $cities     = ItemView::select('city')->whereNotNull('city')->distinct()->pluck('city')->sort()->values();

        return view('admin.monitor.top-products', compact(
            'from','to','mode','categoryFilter','vendorFilter','cityFilter',
            'rows','items','categories','vendors','cities'
        ));
    }

    // ── Top Shops Table (paginated) ─────────────────────────────────────────
    public function topShops(Request $request)
    {
        $from       = $request->get('from', now()->subDays(29)->toDateString());
        $to         = $request->get('to',   now()->toDateString());
        $cityFilter = $request->get('city');
        $mode       = $request->get('mode', 'visits'); // visits | orders | revenue

        if ($mode === 'visits') {
            $query = ShopView::select(
                    'shop_id',
                    DB::raw('COUNT(*) as visit_count'),
                    DB::raw('COUNT(DISTINCT user_id) as unique_visitors')
                )
                ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to]);

            if ($cityFilter) $query->where('city', $cityFilter);

            $rows = $query->groupBy('shop_id')->orderByDesc('visit_count')->paginate(20);
        } else {
            $query = Order::select(
                    'shop_id',
                    DB::raw('COUNT(*) as order_count'),
                    DB::raw('SUM(CASE WHEN status="delivered" THEN final_amount ELSE 0 END) as revenue')
                )
                ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to]);

            if ($cityFilter) {
                $query->join('app_owner_shops as s_city', 's_city.shop_id', '=', 'orders.shop_id')
                      ->where('s_city.city', $cityFilter);
            }

            $col  = $mode === 'revenue' ? 'revenue' : 'order_count';
            $rows = $query->groupBy('shop_id')->orderByDesc($col)->paginate(20);
        }

        $shopIds = $rows->pluck('shop_id')->toArray();
        $shops   = AppOwnerUser::select('shop_id','restaurant_name','city','status')
            ->whereIn('shop_id', $shopIds)->get()->keyBy('shop_id');

        $cities = ShopView::select('city')->whereNotNull('city')->distinct()->pluck('city')->sort()->values();

        return view('admin.monitor.top-shops', compact(
            'from','to','mode','cityFilter','rows','shops','cities'
        ));
    }

    // ── Location Analytics ──────────────────────────────────────────────────
    public function locationAnalytics(Request $request)
    {
        $from = $request->get('from', now()->subDays(29)->toDateString());
        $to   = $request->get('to',   now()->toDateString());

        $itemViewsByCity = ItemView::select('city', DB::raw('COUNT(*) as views'), DB::raw('COUNT(DISTINCT user_id) as unique_users'))
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->whereNotNull('city')
            ->groupBy('city')->orderByDesc('views')->limit(20)->get();

        $shopVisitsByCity = ShopView::select('city', DB::raw('COUNT(*) as visits'), DB::raw('COUNT(DISTINCT user_id) as unique_users'))
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->whereNotNull('city')
            ->groupBy('city')->orderByDesc('visits')->limit(20)->get();

        $ordersByCity = Order::select('city', DB::raw('COUNT(*) as orders'), DB::raw('SUM(CASE WHEN status="delivered" THEN final_amount ELSE 0 END) as revenue'))
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->whereNotNull('city')
            ->groupBy('city')->orderByDesc('orders')->limit(20)->get();

        return view('admin.monitor.location', compact(
            'from','to','itemViewsByCity','shopVisitsByCity','ordersByCity'
        ));
    }
}

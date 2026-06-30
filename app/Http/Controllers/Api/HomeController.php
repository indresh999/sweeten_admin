<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\Banner;
use App\Models\Deal;
use App\Models\ItemCategory;
use App\Models\Item;
use App\Models\AppOwnerUser;
use App\Models\AppSetting;

class HomeController extends Controller
{
    public function homeData(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'lat'    => 'nullable|numeric',
            'lng'    => 'nullable|numeric',
            'radius' => 'nullable|numeric',
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);

        $lat    = (float)$request->get('lat', 0);
        $lng    = (float)$request->get('lng', 0);
        $radius = (float)$request->get('radius', AppSetting::get('max_delivery_radius_km', 10));
        $today  = now()->toDateString();

        $heroBanners = Banner::where('status', 'active')
            ->where('banner_type', 'hero')
            ->where(fn($q) => $q->whereNull('start_date')->orWhere('start_date', '<=', $today))
            ->where(fn($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', $today))
            ->orderBy('sort_order')->orderByDesc('id')
            ->limit(10)->get();

        $stripBanners = Banner::where('status', 'active')
            ->where('banner_type', 'strip')
            ->where(fn($q) => $q->whereNull('start_date')->orWhere('start_date', '<=', $today))
            ->where(fn($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', $today))
            ->orderBy('sort_order')->limit(5)->get();

        $categories = ItemCategory::where('status', 'active')
            ->select('id','category_name','image','commission_percent','commission_type')
            ->orderBy('id')->limit(20)->get();

        $activeDeals = Deal::with(['dealItems.item:id,item_name,images,is_veg'])
            ->where('is_active', 1)->where('starts_at', '<=', now())->where('ends_at', '>=', now())
            ->orderByDesc('id')->limit(5)->get()
            ->map(fn($d) => array_merge($d->toArray(), ['time_left_seconds' => max(0, now()->diffInSeconds($d->ends_at, false))]));

        $shopsQuery = AppOwnerUser::select('shop_id','restaurant_name','city','state','status','latitude','longitude')
            ->where('status', 'active');
        if ($lat && $lng) {
            $shopsQuery->selectRaw('(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance', [$lat, $lng, $lat])
                ->having('distance', '<=', $radius)->orderBy('distance');
        }
        $nearbyShops = $shopsQuery->with('images:id,shop_id,image_path')->limit(20)->get()
            ->map(fn($s) => array_merge($s->toArray(), ['is_open' => $s->isOpenNow()]));

        $featuredItems = Item::with(['owner:shop_id,restaurant_name', 'defaultVariant:id,item_id,label,price,offer_price,gst_percent,is_default'])
            ->where('is_featured', 1)->where('status', 'active')
            ->when($lat && $lng, fn($q) => $q->whereIn('shop_id', $nearbyShops->pluck('shop_id')))
            ->orderByDesc('rating_avg')->limit(12)->get();

        $minOrder        = AppSetting::get('min_order_amount', 0);
        $freeDeliveryAbove = AppSetting::get('free_delivery_above', 199);

        return response()->json([
            'status' => true,
            'data'   => [
                'hero_banners'      => $heroBanners,
                'strip_banners'     => $stripBanners,
                'categories'        => $categories,
                'active_deals'      => $activeDeals,
                'nearby_shops'      => $nearbyShops,
                'featured_items'    => $featuredItems,
                'app_info'          => [
                    'min_order_amount'     => (float)$minOrder,
                    'free_delivery_above'  => (float)$freeDeliveryAbove,
                ],
            ],
        ]);
    }
}

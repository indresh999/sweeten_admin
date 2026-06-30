<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\AppOwnerUser;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    // ── Full search  GET /search?q=X  ──────────────────────
    // Flutter parses: data.shops and data.items
    public function search(Request $request)
    {
        $q   = trim($request->input('q', ''));
        $lat = (float) ($request->input('lat', 0));
        $lng = (float) ($request->input('lng', 0));

        if (strlen($q) < 2) {
            return response()->json(['status' => true, 'data' => ['shops' => [], 'items' => []]]);
        }

        // ── Shops ─────────────────────────────────────────
        $shopsQuery = AppOwnerUser::where('status', 'active')
            ->where(function ($query) use ($q) {
                $query->where('restaurant_name', 'LIKE', "%{$q}%")
                      ->orWhere('city', 'LIKE', "%{$q}%");
            })
            ->select('shop_id', 'restaurant_name', 'city', 'state', 'latitude', 'longitude');

        if ($lat && $lng) {
            $shopsQuery->selectRaw(
                '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
                [$lat, $lng, $lat]
            )->orderBy('distance');
        }

        $shops = $shopsQuery->limit(20)->get();

        // ── Items ─────────────────────────────────────────
        $items = Item::with([
                'defaultVariant:id,item_id,label,price,offer_price,is_default',
                'owner:shop_id,restaurant_name',
                'category:id,category_name',
            ])
            ->where('status', 'active')
            ->where(function ($q2) use ($q) {
                $q2->where('item_name', 'LIKE', "%{$q}%")
                   ->orWhere('description', 'LIKE', "%{$q}%");
            })
            ->select('id', 'item_name', 'description', 'images', 'shop_id', 'category_id')
            ->limit(30)
            ->get()
            ->map(function ($item) {
                $images = is_array($item->images)
                    ? $item->images
                    : (json_decode($item->images, true) ?? []);
                return [
                    'id'           => $item->id,
                    'item_name'    => $item->item_name,
                    'description'  => $item->description,
                    'image_urls'   => $images,
                    'shop_id'      => $item->shop_id,
                    'shop_name'    => $item->owner?->restaurant_name ?? '',
                    'category'     => $item->category?->category_name ?? '',
                    'price'        => $item->defaultVariant?->price ?? 0,
                    'offer_price'  => $item->defaultVariant?->offer_price,
                ];
            });

        return response()->json([
            'status' => true,
            'data'   => [
                'shops' => $shops,
                'items' => $items,
                'total' => $shops->count() + $items->count(),
            ],
        ]);
    }

    // ── Suggestions  GET /search/suggestions?q=X ──────────
    public function suggestions(Request $request)
    {
        $q   = trim($request->input('q', ''));
        $lat = (float) ($request->input('lat', 0));
        $lng = (float) ($request->input('lng', 0));

        if (strlen($q) < 2) {
            return response()->json(['status' => true, 'data' => ['shops' => [], 'items' => []]]);
        }

        $shops = AppOwnerUser::where('status', 'active')
            ->where('restaurant_name', 'LIKE', "{$q}%")
            ->select('shop_id', 'restaurant_name', 'city')
            ->limit(5)->get();

        $items = Item::where('status', 'active')
            ->where('item_name', 'LIKE', "{$q}%")
            ->select('id', 'item_name', 'images', 'shop_id')
            ->limit(5)->get()
            ->map(fn($i) => [
                'id'        => $i->id,
                'item_name' => $i->item_name,
                'image_url' => collect(is_array($i->images) ? $i->images : json_decode($i->images, true) ?? [])->first(),
            ]);

        return response()->json([
            'status' => true,
            'data'   => ['shops' => $shops, 'items' => $items],
        ]);
    }
}

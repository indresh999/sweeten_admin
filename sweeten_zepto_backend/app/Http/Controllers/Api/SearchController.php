<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\Item;
use App\Models\AppOwnerUser;
use App\Models\SearchHistory;

class SearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'q'        => 'required|string|min:1|max:200',
            'lat'      => 'nullable|numeric',
            'lng'      => 'nullable|numeric',
            'user_id'  => 'nullable|exists:app_users,id',
            'per_page' => 'nullable|integer|min:1|max:30',
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);

        $q   = $request->q;
        $lat = (float)$request->lat;
        $lng = (float)$request->lng;

        $shopQuery = AppOwnerUser::select('shop_id','restaurant_name','city','state','status')
            ->where('status', 'active')
            ->where(fn($q2) => $q2->where('restaurant_name', 'LIKE', "%$q%")->orWhere('city', 'LIKE', "%$q%"));

        if ($lat && $lng) {
            $shopQuery->selectRaw('(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance', [$lat, $lng, $lat])
                ->having('distance', '<', 20)
                ->orderBy('distance');
        }
        $shops = $shopQuery->limit(10)->get();

        $items = Item::with(['owner:shop_id,restaurant_name', 'defaultVariant:id,item_id,label,price,offer_price,is_default'])
            ->where('status', 'active')
            ->where(fn($q2) => $q2->where('item_name', 'LIKE', "%$q%")->orWhere('description', 'LIKE', "%$q%"))
            ->paginate($request->get('per_page', 20));

        if ($request->filled('user_id')) {
            SearchHistory::create(['user_id' => $request->user_id, 'query' => $q, 'result_count' => $items->total() + $shops->count()]);
        }

        return response()->json(['status' => true, 'data' => ['shops' => $shops, 'items' => $items]]);
    }

    public function suggestions(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'q'       => 'required|string|min:2|max:100',
            'lat'     => 'nullable|numeric',
            'lng'     => 'nullable|numeric',
            'user_id' => 'nullable|exists:app_users,id',
        ]);
        if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);

        $q = $request->q;

        $shopSuggestions = AppOwnerUser::where('status', 'active')
            ->where('restaurant_name', 'LIKE', "$q%")
            ->select('shop_id as id', 'restaurant_name as name')
            ->selectRaw('"shop" as type')
            ->limit(4)->get();

        $itemSuggestions = Item::where('status', 'active')
            ->where('item_name', 'LIKE', "$q%")
            ->select('id', 'item_name as name')
            ->selectRaw('"item" as type')
            ->limit(6)->get();

        $recentSearches = [];
        if ($request->filled('user_id')) {
            $recentSearches = SearchHistory::where('user_id', $request->user_id)
                ->where('query', 'LIKE', "$q%")
                ->orderByDesc('created_at')
                ->distinct()
                ->limit(3)
                ->pluck('query');
        }

        return response()->json(['status' => true, 'data' => [
            'shops'          => $shopSuggestions,
            'items'          => $itemSuggestions,
            'recent_searches'=> $recentSearches,
        ]]);
    }

    public function clearSearchHistory(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), ['user_id' => 'required|exists:app_users,id']);
        if ($validator->fails()) return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        SearchHistory::where('user_id', $request->user_id)->delete();
        return response()->json(['status' => true, 'message' => 'Search history cleared']);
    }
}

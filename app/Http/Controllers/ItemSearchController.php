<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\AppOwnerUser;

class ItemSearchController extends Controller
{
    public function searchNearbyItems(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'radius' => 'nullable|numeric',
            'keyword' => 'nullable|string'
        ]);

        $lat = $request->lat;
        $lng = $request->lng;
        $radius = $request->radius ?? 5; // KM default

        // Find shop IDs nearby
        $ownerIds = AppOwnerUser::select('shop_id')
            ->selectRaw("
                ( 6371 * acos(
                    cos(radians(?)) * 
                    cos(radians(latitude)) * 
                    cos(radians(longitude) - radians(?)) + 
                    sin(radians(?)) * 
                    sin(radians(latitude))
                )) AS distance
            ", [$lat, $lng, $lat])
            ->having("distance", "<=", $radius)
            ->pluck("shop_id");

        if ($ownerIds->isEmpty()) {
            return response()->json([
                'message' => 'No nearby shops found.',
                'data' => []
            ], 200);
        }

        // Fetch items from those shops
        $itemsQuery = Item::with('category', 'owner')
            ->whereIn('owner_id', $ownerIds);

        if ($request->keyword) {
            $itemsQuery->where('item_name', 'LIKE', '%' . $request->keyword . '%');
        }

        $items = $itemsQuery->paginate(20);

        return response()->json([
            'message' => 'Items fetched successfully.',
            'data' => $items
        ]);
    }
}
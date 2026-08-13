<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GooglePlacesController extends Controller
{
    public function autocomplete(Request $request)
    {
        $request->validate([
            'input' => 'required|string|min:2',
        ]);

        $apiKey = config('services.google.maps_key', env('GOOGLE_MAPS_API_KEY'));
        $country = $request->get('country', 'in');
        $type = $request->get('type', 'city'); // city | address

        $params = [
            'input'      => $request->input('input'),
            'key'        => $apiKey,
            'components' => "country:{$country}",
        ];

        if ($type === 'city') {
            $params['types'] = '(cities)';
        } else {
            $params['types'] = 'geocode|establishment';
        }

        $response = Http::get('https://maps.googleapis.com/maps/api/place/autocomplete/json', $params);

        if ($response->failed()) {
            return response()->json(['status' => false, 'message' => 'Failed to fetch suggestions'], 500);
        }

        $data = $response->json();
        $predictions = collect($data['predictions'] ?? [])->map(fn($p) => [
            'description' => $p['description'],
            'place_id'    => $p['place_id'],
        ]);

        return response()->json(['status' => true, 'data' => $predictions]);
    }

    public function placeDetails(Request $request)
    {
        $request->validate([
            'place_id' => 'required|string',
        ]);

        $apiKey = config('services.google.maps_key', env('GOOGLE_MAPS_API_KEY'));

        $response = Http::get('https://maps.googleapis.com/maps/api/place/details/json', [
            'place_id' => $request->input('place_id'),
            'key'      => $apiKey,
            'fields'   => 'name,geometry.location,formatted_address,address_components',
        ]);

        if ($response->failed()) {
            return response()->json(['status' => false, 'message' => 'Failed to fetch place details'], 500);
        }

        $data = $response->json();
        $result = $data['result'] ?? null;

        if (!$result) {
            return response()->json(['status' => false, 'message' => 'Place not found'], 404);
        }

        // Extract address components
        $city = '';
        $state = '';
        $zipCode = '';
        $country = '';
        foreach ($result['address_components'] ?? [] as $component) {
            if (in_array('locality', $component['types'])) {
                $city = $component['long_name'];
            }
            if (in_array('administrative_area_level_1', $component['types'])) {
                $state = $component['long_name'];
            }
            if (in_array('postal_code', $component['types'])) {
                $zipCode = $component['long_name'];
            }
            if (in_array('country', $component['types'])) {
                $country = $component['long_name'];
            }
        }

        return response()->json([
            'status' => true,
            'data' => [
                'city'    => $city ?: $result['name'],
                'state'   => $state,
                'zip_code'=> $zipCode,
                'country' => $country,
                'lat'     => $result['geometry']['location']['lat'] ?? null,
                'lng'     => $result['geometry']['location']['lng'] ?? null,
                'address' => $result['formatted_address'] ?? '',
            ],
        ]);
    }
}

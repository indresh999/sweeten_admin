<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LocationController extends Controller
{
    private function mapsKey(): ?string
    {
        $key = AppSetting::get('google_maps_api_key');
        if ($key && strlen(trim($key)) > 10) return trim($key);
        // Fallback to .env when admin panel key not yet saved
        $env = env('GOOGLE_MAPS_API_KEY', '');
        return strlen(trim($env)) > 10 ? trim($env) : null;
    }

    private function apiError(string $googleStatus): JsonResponse
    {
        $msg = match ($googleStatus) {
            'REQUEST_DENIED'  => 'Google API key is invalid or the required API is not enabled in Google Cloud Console.',
            'OVER_QUERY_LIMIT'=> 'Google API quota exceeded. Try again later.',
            'INVALID_REQUEST' => 'Invalid request parameters.',
            default           => "Google API error: {$googleStatus}",
        };
        return response()->json(['status' => false, 'message' => $msg], 502);
    }

    // ─── POST/GET /api/location/autocomplete?input=...&session_token=... ──────

    public function autocomplete(Request $request): JsonResponse
    {
        $input = trim($request->input('input', ''));
        if (strlen($input) < 2) {
            return response()->json(['status' => true, 'predictions' => []]);
        }

        $key = $this->mapsKey();
        if (!$key) {
            return response()->json([
                'status'  => false,
                'message' => 'Google Maps API key not configured. Go to Admin → Settings → Google Maps.',
            ], 503);
        }

        // ── Places API (New) ─────────────────────────────────────────────────
        $res = Http::timeout(8)
            ->withHeaders([
                'X-Goog-Api-Key' => $key,
                'Content-Type'   => 'application/json',
            ])
            ->post('https://places.googleapis.com/v1/places:autocomplete', [
                'input'        => $input,
                'regionCode'   => 'IN',
                'languageCode' => 'en',
                'sessionToken' => $request->input('session_token', ''),
            ]);

        if (!$res->successful()) {
            $errStatus = $res->json('error.status') ?? $res->json('status') ?? 'ERROR';
            return $this->apiError($errStatus);
        }

        $suggestions = $res->json('suggestions') ?? [];

        $predictions = collect($suggestions)
            ->filter(fn($s) => isset($s['placePrediction']))
            ->map(function ($s) {
                $p = $s['placePrediction'];
                return [
                    'place_id'       => $p['placeId'] ?? '',
                    'main_text'      => $p['structuredFormat']['mainText']['text'] ?? $p['text']['text'] ?? '',
                    'secondary_text' => $p['structuredFormat']['secondaryText']['text'] ?? '',
                    'description'    => $p['text']['text'] ?? '',
                ];
            })
            ->values();

        return response()->json(['status' => true, 'predictions' => $predictions]);
    }

    // ─── GET /api/location/details?place_id=...&session_token=... ────────────

    public function details(Request $request): JsonResponse
    {
        $placeId = $request->input('place_id', '');
        if (!$placeId) {
            return response()->json(['status' => false, 'message' => 'place_id required'], 422);
        }

        $key = $this->mapsKey();
        if (!$key) {
            return response()->json([
                'status'  => false,
                'message' => 'Google Maps API key not configured.',
            ], 503);
        }

        // ── Places API (New) — place details ─────────────────────────────────
        $res = Http::timeout(8)
            ->withHeaders([
                'X-Goog-Api-Key'   => $key,
                'X-Goog-FieldMask' => 'id,displayName,formattedAddress,location,addressComponents',
            ])
            ->get("https://places.googleapis.com/v1/places/{$placeId}", [
                'languageCode' => 'en',
                'sessionToken' => $request->input('session_token', ''),
            ]);

        if (!$res->successful()) {
            $errStatus = $res->json('error.status') ?? $res->json('status') ?? 'ERROR';
            return $this->apiError($errStatus);
        }

        $data = $res->json();
        $loc  = $data['location'] ?? null;

        if (!$loc) {
            return response()->json(['status' => false, 'message' => 'Place not found'], 404);
        }

        $components = $data['addressComponents'] ?? [];

        return response()->json([
            'status'            => true,
            'lat'               => $loc['latitude'],
            'lng'               => $loc['longitude'],
            'formatted_address' => $data['formattedAddress'] ?? '',
            'name'              => $data['displayName']['text'] ?? '',
            'locality'          => $this->extractComponent($components, ['sublocality_level_1', 'sublocality', 'neighborhood']),
            'city'              => $this->extractComponent($components, ['locality']),
            'state'             => $this->extractComponent($components, ['administrative_area_level_1']),
            'pincode'           => $this->extractComponent($components, ['postal_code']),
        ]);
    }

    // ─── GET /api/location/reverse?lat=...&lng=... ───────────────────────────

    public function reverse(Request $request): JsonResponse
    {
        $lat = $request->input('lat');
        $lng = $request->input('lng');

        if (!$lat || !$lng) {
            return response()->json(['status' => false, 'message' => 'lat and lng required'], 422);
        }

        $key = $this->mapsKey();
        if (!$key) {
            return response()->json([
                'status'  => false,
                'message' => 'Google Maps API key not configured.',
            ], 503);
        }

        // ── Geocoding API (legacy — still separate from Places API) ──────────
        $res = Http::timeout(8)->get('https://maps.googleapis.com/maps/api/geocode/json', [
            'latlng'      => "{$lat},{$lng}",
            'key'         => $key,
            'language'    => 'en',
            'result_type' => 'street_address|route|locality|sublocality',
        ]);

        if (!$res->successful()) {
            return response()->json(['status' => false, 'message' => 'Geocoding API error'], 502);
        }

        $data    = $res->json();
        $status  = $data['status'] ?? 'ERROR';

        if ($status !== 'OK' && $status !== 'ZERO_RESULTS') {
            return $this->apiError($status);
        }

        $results = $data['results'] ?? [];

        if (empty($results)) {
            // Return raw coords even without a resolved address
            return response()->json([
                'status'            => true,
                'lat'               => (float) $lat,
                'lng'               => (float) $lng,
                'formatted_address' => "{$lat}, {$lng}",
                'locality'          => '',
                'city'              => '',
                'state'             => '',
                'pincode'           => '',
            ]);
        }

        $best       = $results[0];
        $components = $best['address_components'] ?? [];

        return response()->json([
            'status'            => true,
            'lat'               => (float) $lat,
            'lng'               => (float) $lng,
            'formatted_address' => $best['formatted_address'] ?? '',
            'locality'          => $this->extractComponent($components, ['sublocality_level_1', 'sublocality', 'neighborhood']),
            'city'              => $this->extractComponent($components, ['locality']),
            'state'             => $this->extractComponent($components, ['administrative_area_level_1']),
            'pincode'           => $this->extractComponent($components, ['postal_code']),
        ]);
    }

    private function extractComponent(array $components, array $types): string
    {
        foreach ($components as $c) {
            $compTypes = $c['types'] ?? [];
            foreach ($types as $t) {
                if (in_array($t, $compTypes, true)) {
                    return $c['shortText'] ?? $c['longText']          // Places API (New) format
                        ?? $c['short_name'] ?? $c['long_name'] ?? ''; // Geocoding API format
                }
            }
        }
        return '';
    }
}

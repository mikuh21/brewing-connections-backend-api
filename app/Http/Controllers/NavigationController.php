<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use App\Models\Establishment;

class NavigationController extends Controller
{
    public function getDirections(Request $request)
    {
        $data = $request->validate([
            'origin' => 'required|array',
            'origin.lat' => 'required|numeric|min:-90|max:90',
            'origin.lng' => 'required|numeric|min:-180|max:180',
            'destination_id' => 'nullable|integer|exists:establishments,id',
            'destination' => 'nullable|array|required_without:destination_id',
            'destination.lat' => 'required_with:destination|numeric|min:-90|max:90',
            'destination.lng' => 'required_with:destination|numeric|min:-180|max:180',
        ]);

        $originLat = $data['origin']['lat'];
        $originLng = $data['origin']['lng'];
        $destId = $data['destination_id'] ?? null;
        $destLat = null;
        $destLng = null;

        if ($destId) {
            $destination = Establishment::find($destId);
            if (!$destination || !$destination->latitude || !$destination->longitude) {
                return response()->json(['message' => 'Destination coordinates not found'], 404);
            }

            $destLat = $destination->latitude;
            $destLng = $destination->longitude;
        } elseif (!empty($data['destination'])) {
            $destLat = $data['destination']['lat'];
            $destLng = $data['destination']['lng'];
        } else {
            return response()->json(['message' => 'A destination is required.'], 422);
        }

        $cacheKey = sprintf('directions_%s_%s_%s',
            number_format($originLat, 6, '.', ''),
            number_format($originLng, 6, '.', ''),
            $destId ?: sprintf('%s_%s', number_format($destLat, 6, '.', ''), number_format($destLng, 6, '.', ''))
        );

        $cached = Cache::get($cacheKey);
        if ($cached) {
            return response()->json($cached);
        }

        $mapsKey = Config::get('services.google_maps.key');
        $mapsBase = rtrim(Config::get('services.google_maps.base_url'), '/');

        if (!$mapsKey) {
            return response()->json(['message' => 'Google Maps API key not configured'], 500);
        }

        $url = $mapsBase . '/directions/json';

        $response = Http::get($url, [
            'origin' => "{$originLat},{$originLng}",
            'destination' => "{$destLat},{$destLng}",
            'mode' => 'driving',
            'key' => $mapsKey,
        ]);

        if (!$response->successful()) {
            return response()->json(['message' => 'Directions request failed', 'status' => $response->status()], 502);
        }

        $payload = $response->json();
        $status = $payload['status'] ?? 'UNKNOWN';

        if ($status === 'OK') {
            $route = $payload['routes'][0] ?? null;
            if (!$route) {
                return response()->json(['message' => 'No route found'], 404);
            }
            $leg = $route['legs'][0] ?? null;
            if (!$leg) {
                return response()->json(['message' => 'No legs returned'], 404);
            }

            $result = [
                'polyline' => $route['overview_polyline']['points'] ?? null,
                'distance' => $leg['distance']['text'] ?? null,
                'duration' => $leg['duration']['text'] ?? null,
                'steps' => collect($leg['steps'] ?? [])->map(function ($step) {
                    return [
                        'instruction' => strip_tags($step['html_instructions'] ?? ''),
                        'distance' => $step['distance']['text'] ?? null,
                    ];
                })->toArray(),
            ];

            Cache::remember($cacheKey, 600, fn () => $result);
            return response()->json($result);
        }

        if ($status === 'ZERO_RESULTS') {
            return response()->json(['message' => 'No route found for the specified origin and destination', 'status' => $status], 404);
        }

        if ($status === 'REQUEST_DENIED') {
            return response()->json(['message' => 'Google Maps request denied', 'status' => $status, 'error_message' => ($payload['error_message'] ?? null)], 403);
        }

        if ($status === 'OVER_QUERY_LIMIT') {
            return response()->json(['message' => 'Google Maps query limit exceeded', 'status' => $status], 429);
        }

        return response()->json(['message' => 'Google Maps error', 'status' => $status, 'error_message' => ($payload['error_message'] ?? null)], 500);
    }
}

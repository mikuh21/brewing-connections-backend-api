<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class CoffeeTrailController extends Controller
{
    public function generate(Request $request)
    {
        $data = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'varieties' => 'required|array|min:1',
            'varieties.*' => 'required|string|max:100',
            'types' => 'nullable|array|min:1',
            'types.*' => 'required|string|in:farm,cafe,roaster,reseller',
            'max_stops' => 'nullable|integer|min:2|max:5',
        ]);

        $lat = (float) $data['lat'];
        $lng = (float) $data['lng'];
        $varieties = array_values(array_filter($data['varieties'], fn ($v) => trim((string) $v) !== ''));
        $types = collect($data['types'] ?? [])
            ->map(fn ($type) => strtolower(trim((string) $type)))
            ->filter()
            ->unique()
            ->values()
            ->all();
        $maxStops = $data['max_stops'] ?? 3;

        if (count($varieties) === 0) {
            return response()->json(['message' => 'At least one coffee variety is required'], 422);
        }

        // Find candidate establishments (capped to max_stops * 2). We keep top 2x by distance.
        $candidates = Establishment::getNearbyForTrail($lat, $lng, $varieties, $types, $maxStops * 2);

        if ($candidates->isEmpty()) {
            return response()->json(['message' => 'No coffee establishments found in range for selected varieties'], 404);
        }

        $mapboxKey = Config::get('services.mapbox.api_key');
        $mapboxBase = rtrim(Config::get('services.mapbox.base_url'), '/');

        if (!$mapboxKey || !$mapboxBase) {
            return response()->json(['message' => 'Mapbox API configuration missing'], 500);
        }

        $buildCoords = function ($stops) use ($lat, $lng) {
            $coords = [sprintf('%F,%F', $lng, $lat)];
            foreach ($stops as $stop) {
                $coords[] = $stop->toWaypoint();
            }
            return implode(';', $coords);
        };

        $callMapbox = function ($coords) use ($mapboxBase, $mapboxKey) {
            $url = sprintf('%s/optimized-trips/v1/mapbox/driving/%s', $mapboxBase, urlencode($coords));
            return Http::get($url, [
                'access_token' => $mapboxKey,
                'source' => 'first',
                'destination' => 'last',
                'roundtrip' => 'false',
            ]);
        };

        // Do a preliminary request to get the optimization order.
        $coordsAll = $buildCoords($candidates);
        $preResp = $callMapbox($coordsAll);

        if (!$preResp->successful()) {
            return response()->json(['message' => 'Mapbox optimization request failed', 'status' => $preResp->status()], 502);
        }

        $preJson = $preResp->json();
        $preWaypoints = $preJson['waypoints'] ?? [];
        $preTrips = $preJson['trips'] ?? [];

        if (empty($preTrips) || empty($preWaypoints)) {
            return response()->json(['message' => 'Mapbox optimization response invalid or empty'], 502);
        }

        $candidateRouteOrder = collect($preWaypoints)
            ->filter(fn ($wp, $inputIndex) => $inputIndex !== 0) // exclude user origin
            ->map(function ($wp, $inputIndex) {
                return [
                    'input_index' => $inputIndex,
                    'waypoint_index' => $wp['waypoint_index'] ?? null,
                ];
            })
            ->filter(fn ($entry) => $entry['waypoint_index'] !== null)
            ->sortBy('waypoint_index')
            ->map(fn ($entry) => $entry['input_index'] - 1) // map to candidate index
            ->values();

        $selectedIndexList = $candidateRouteOrder->slice(0, $maxStops)->values();

        $selectedStops = $selectedIndexList->map(fn ($candidateIndex) => $candidates->get($candidateIndex));

        if ($selectedStops->isEmpty()) {
            return response()->json(['message' => 'No stops selected for route'], 404);
        }

        // Do a final mapbox route with selected stops to produce accurate geometry/distance/duration.
        $coordsSelected = $buildCoords($selectedStops);
        $finalResp = $callMapbox($coordsSelected);

        if (!$finalResp->successful()) {
            return response()->json(['message' => 'Mapbox optimization request failed for selected stops', 'status' => $finalResp->status()], 502);
        }

        $finalJson = $finalResp->json();
        $finalTrips = $finalJson['trips'] ?? [];

        if (empty($finalTrips)) {
            return response()->json(['message' => 'Mapbox optimization returned no trips for selected stops'], 502);
        }

        $trip = $finalTrips[0];

        $stops = $selectedStops->values()->map(function ($establishment, $index) {
            return [
                'sequence' => $index + 1,
                'establishment_id' => $establishment->id,
                'id' => $establishment->id,
                'name' => $establishment->name,
                'type' => $establishment->type,
                'address' => $establishment->address,
                'latitude' => $establishment->latitude,
                'longitude' => $establishment->longitude,
            ];
        });

        return response()->json([
            'stops' => $stops,
            'route_geometry' => $trip['geometry'] ?? null,
            'total_distance_km' => isset($trip['distance']) ? round($trip['distance'] / 1000, 3) : null,
            'total_duration_min' => isset($trip['duration']) ? round($trip['duration'] / 60, 1) : null,
        ]);
    }
}

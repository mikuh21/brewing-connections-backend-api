<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CoffeeTrail;
use App\Models\CoffeeTrailMarkerView;
use App\Models\Establishment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class CoffeeTrailController extends Controller
{
    protected function normalizeStringList(array $values): array
    {
        return collect($values)
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique(fn ($value) => strtolower($value))
            ->values()
            ->all();
    }

    protected function inferRecommendationReason($establishment, array $selectedVarieties): string
    {
        $selectedLookup = collect($selectedVarieties)
            ->map(fn ($name) => strtolower(trim((string) $name)))
            ->filter()
            ->values();

        $matchedVarieties = collect($establishment->varieties ?? [])
            ->pluck('name')
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->values();

        $matchingNames = $matchedVarieties
            ->filter(fn ($name) => $selectedLookup->contains(strtolower($name)))
            ->values();

        if ($matchingNames->isNotEmpty()) {
            return 'Matches your preferred coffee varieties: ' . $matchingNames->join(', ') . '.';
        }

        $type = strtolower((string) ($establishment->type ?? 'establishment'));
        return sprintf('Recommended based on your selected %s stops and nearby route fit.', $type);
    }

    protected function toTrailResponse(CoffeeTrail $trail): array
    {
        return [
            'trail_id' => $trail->id,
            'created_at' => optional($trail->created_at)->toIso8601String(),
            'origin' => [
                'latitude' => (float) ($trail->origin_lat ?? 0),
                'longitude' => (float) ($trail->origin_lng ?? 0),
            ],
            'preferences' => [
                'varieties' => array_values($trail->preferences['varieties'] ?? []),
                'types' => array_values($trail->preferences['types'] ?? []),
                'max_stops' => (int) ($trail->preferences['max_stops'] ?? 0),
            ],
            'stops' => array_values($trail->trail_data ?? []),
            'route_geometry' => $trail->route_geometry,
            'total_distance_km' => $trail->total_distance_km,
            'total_duration_min' => $trail->total_duration_min,
        ];
    }

    protected function buildPreviewPayload(array $varieties, array $types, int $maxStops): array
    {
        $selectedVarietyNames = collect($varieties)->take(2)->join(' + ');
        $selectedTypeLabels = collect($types)
            ->map(function ($type) {
                return match ($type) {
                    'farm' => 'Farms',
                    'cafe' => 'Cafes',
                    'roaster' => 'Roasters',
                    'reseller' => 'Resellers',
                    default => ucfirst((string) $type),
                };
            })
            ->take(2)
            ->join(' + ');

        $hasVarieties = count($varieties) > 0;
        $hasTypes = count($types) > 0;

        $headline = $hasVarieties && $hasTypes ? 'AI Recommendation Preview' : 'AI Setup Tip';
        $summary = $hasVarieties && $hasTypes
            ? sprintf(
                'Your trail will prioritize %s across %s with %d stop%s.',
                $selectedVarietyNames ?: 'your selected varieties',
                $selectedTypeLabels ?: 'your selected stop types',
                $maxStops,
                $maxStops > 1 ? 's' : ''
            )
            : 'Select at least one variety and one visit type so AI can personalize your route.';

        $routeHint = $maxStops >= 4
            ? 'Longer trail mode: AI will optimize pacing for more stops.'
            : 'Compact trail mode: AI will prioritize faster travel time between stops.';

        $balanceHint = 'Balanced trail: includes both sourcing and tasting opportunities.';
        if (count($types) === 1 && $types[0] === 'farm') {
            $balanceHint = 'Farm-focused route: best for bean-origin discovery.';
        } elseif (count($types) === 1 && $types[0] === 'cafe') {
            $balanceHint = 'Cafe-focused route: best for tasting and atmosphere variety.';
        }

        $suggestion = 'Your setup looks balanced. You can generate now.';
        if (!$hasVarieties) {
            $suggestion = 'Add at least one coffee variety to improve recommendation quality.';
        } elseif (!$hasTypes) {
            $suggestion = 'Pick at least one visit type so AI can shape the route style.';
        } elseif (count($types) === 1 && $types[0] === 'farm') {
            $suggestion = 'Tip: add one cafe stop to improve tasting variety.';
        } elseif (count($types) === 1 && $types[0] === 'cafe') {
            $suggestion = 'Tip: add one farm stop to enrich origin learning.';
        } elseif ($maxStops <= 2) {
            $suggestion = 'Tip: try 3-4 stops for better route diversity.';
        } elseif ($maxStops >= 5) {
            $suggestion = 'Tip: reduce to 4 stops if you want a shorter overall trip.';
        }

        $score = 20;
        if ($hasVarieties) {
            $score += 30;
        }
        if ($hasTypes) {
            $score += 30;
        }
        if ($maxStops >= 3 && $maxStops <= 4) {
            $score += 10;
        }
        if (in_array('farm', $types, true) && in_array('cafe', $types, true)) {
            $score += 5;
        }
        $score = max(20, min(95, $score));

        $confidenceLabel = $score >= 75 ? 'High confidence' : ($score >= 50 ? 'Medium confidence' : 'Low confidence');

        return [
            'headline' => $headline,
            'summary' => $summary,
            'route_hint' => $routeHint,
            'balance_hint' => $balanceHint,
            'suggestion' => $suggestion,
            'confidence_score' => $score,
            'confidence_label' => $confidenceLabel,
        ];
    }

    public function preview(Request $request)
    {
        $data = $request->validate([
            'varieties' => 'nullable|array',
            'varieties.*' => 'required|string|max:100',
            'types' => 'nullable|array',
            'types.*' => 'required|string|in:farm,cafe,roaster,reseller',
            'max_stops' => 'nullable|integer|min:2|max:5',
        ]);

        $varieties = $this->normalizeStringList($data['varieties'] ?? []);
        $types = collect($data['types'] ?? [])
            ->map(fn ($type) => strtolower(trim((string) $type)))
            ->filter()
            ->unique()
            ->values()
            ->all();
        $maxStops = (int) ($data['max_stops'] ?? 2);

        return response()->json($this->buildPreviewPayload($varieties, $types, $maxStops));
    }

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
        $varieties = $this->normalizeStringList($data['varieties']);
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
        $candidates = Establishment::getNearbyForTrail($lat, $lng, $varieties, $types, $maxStops * 2)
            ->load('varieties');

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

        $stops = $selectedStops->values()->map(function ($establishment, $index) use ($varieties) {
            return [
                'sequence' => $index + 1,
                'establishment_id' => $establishment->id,
                'id' => $establishment->id,
                'name' => $establishment->name,
                'type' => $establishment->type,
                'address' => $establishment->address,
                'latitude' => $establishment->latitude,
                'longitude' => $establishment->longitude,
                'why_recommended' => $this->inferRecommendationReason($establishment, $varieties),
            ];
        })->values();

        $trail = CoffeeTrail::create([
            'user_id' => optional($request->user())->id,
            'origin_lat' => $lat,
            'origin_lng' => $lng,
            'preferences' => [
                'varieties' => $varieties,
                'types' => $types,
                'max_stops' => (int) $maxStops,
            ],
            'trail_data' => $stops,
            'route_geometry' => $trip['geometry'] ?? null,
            'total_distance_km' => isset($trip['distance']) ? round($trip['distance'] / 1000, 3) : null,
            'total_duration_min' => isset($trip['duration']) ? round($trip['duration'] / 60, 1) : null,
        ]);

        return response()->json($this->toTrailResponse($trail));
    }

    public function history(Request $request)
    {
        $trails = CoffeeTrail::query()
            ->where('user_id', optional($request->user())->id)
            ->latest()
            ->limit(30)
            ->get();

        return response()->json([
            'history' => $trails->map(fn (CoffeeTrail $trail) => $this->toTrailResponse($trail))->values(),
        ]);
    }

    public function trackMarkerView(Request $request)
    {
        $payload = $request->validate([
            'establishment_id' => 'required|integer|exists:establishments,id',
            'map_session_id' => 'nullable|string|max:120',
        ]);

        $userId = optional($request->user())->id;
        if (!$userId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $establishmentId = (int) $payload['establishment_id'];
        $mapSessionId = trim((string) ($payload['map_session_id'] ?? '')) ?: null;
        $dedupeSince = now()->subSeconds(3);

        $existingRecent = CoffeeTrailMarkerView::query()
            ->where('user_id', $userId)
            ->where('establishment_id', $establishmentId)
            ->where('viewed_at', '>=', $dedupeSince)
            ->when($mapSessionId, function ($query) use ($mapSessionId) {
                $query->where('map_session_id', $mapSessionId);
            }, function ($query) {
                $query->whereNull('map_session_id');
            })
            ->exists();

        if ($existingRecent) {
            return response()->json([
                'tracked' => false,
                'reason' => 'duplicate_recent',
            ]);
        }

        CoffeeTrailMarkerView::query()->create([
            'user_id' => $userId,
            'establishment_id' => $establishmentId,
            'map_session_id' => $mapSessionId,
            'viewed_at' => now(),
        ]);

        return response()->json([
            'tracked' => true,
        ]);
    }
}

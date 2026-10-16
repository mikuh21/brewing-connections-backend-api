<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CoffeeTrail;
use App\Models\CoffeeTrailLocationCheck;
use App\Models\Establishment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class CoffeeTrailLocationCheckController extends Controller
{
    /**
     * Return the authenticated user's saved location checks for the Location Checks feed.
     */
    /**
     * Return community-wide location validation totals grouped by establishment.
     * This endpoint intentionally differs from index(), which returns the
     * authenticated user's own validation history.
     */
    public function communityIndex()
    {
        if (!Schema::hasTable('coffee_trail_location_checks')) {
            return response()->json([]);
        }

        $establishments = Establishment::query()
            ->whereHas('locationChecks')
            ->withCount([
                'locationChecks as correct_count' => function ($query) {
                    $query->where('response', 'yes');
                },
                'locationChecks as incorrect_count' => function ($query) {
                    $query->where('response', 'no');
                },
            ])
            ->orderBy('name')
            ->get()
            ->filter(fn ($establishment) => ((int) $establishment->correct_count + (int) $establishment->incorrect_count) > 0)
            ->map(function (Establishment $establishment) {
                return [
                    'establishment_id' => $establishment->id,
                    'establishment_name' => $establishment->name,
                    'establishment_type' => $establishment->type,
                    'establishment_address' => $establishment->address,
                    'latitude' => $establishment->latitude,
                    'longitude' => $establishment->longitude,
                    'correct_count' => (int) $establishment->correct_count,
                    'incorrect_count' => (int) $establishment->incorrect_count,
                    'total_count' => (int) $establishment->correct_count + (int) $establishment->incorrect_count,
                ];
            })
            ->values();

        return response()->json($establishments);
    }

    public function index(Request $request)
    {
        if (!Schema::hasTable('coffee_trail_location_checks')) {
            return response()->json([]);
        }

        $perPage = max(1, min((int) $request->query('per_page', 30), 100));

        $checks = CoffeeTrailLocationCheck::query()
            ->where('user_id', $request->user()->id)
            ->when($request->filled('trail_id'), function ($query) use ($request) {
                $query->where('coffee_trail_id', (int) $request->query('trail_id'));
            })
            ->with(['establishment' => function ($query) {
                $query->withTrashed();
            }])
            ->latest()
            ->limit($perPage)
            ->get()
            ->map(function (CoffeeTrailLocationCheck $check) {
                return [
                    'id' => $check->id,
                    'trail_id' => $check->coffee_trail_id,
                    'establishment_id' => $check->establishment_id,
                    'stop_sequence' => $check->stop_sequence,
                    'response' => $check->response,
                    'result' => $check->response === 'yes' ? 'correct_location' : 'incorrect_location',
                    'establishment_name' => $check->establishment?->name
                        ?? $check->establishment_name_snapshot
                        ?? 'Establishment',
                    'establishment_address' => $check->establishment?->address
                        ?? $check->establishment_address_snapshot,
                    'establishment_type' => $check->establishment?->type,
                    'review_status' => $check->review_status,
                    'created_at' => optional($check->created_at)->toIso8601String(),
                ];
            })
            ->values();

        return response()->json($checks);
    }

    /**
     * Compatibility endpoint used by the mobile app's trail-scoped API path.
     */
    public function storeForTrail(Request $request, int $trailId)
    {
        $request->merge(['trail_id' => $trailId]);

        return $this->store($request);
    }

    /**
     * Save a required response for a destination that belongs to the user's trail.
     */
    public function store(Request $request)
    {
        if (!Schema::hasTable('coffee_trail_location_checks')) {
            return response()->json([
                'message' => 'Location checks are not available yet. Please try again after the latest update.',
            ], 503);
        }

        $data = $request->validate([
            'trail_id' => ['required', 'integer', 'min:1'],
            'establishment_id' => ['required', 'integer', 'min:1', Rule::exists('establishments', 'id')],
            'stop_sequence' => ['required', 'integer', 'min:1', 'max:100'],
            'response' => ['required', Rule::in(['yes', 'no'])],
        ]);

        $trail = CoffeeTrail::query()
            ->whereKey($data['trail_id'])
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$trail) {
            return response()->json([
                'message' => 'The requested Coffee Trail was not found for your account.',
            ], 404);
        }

        $stops = is_array($trail->trail_data) ? $trail->trail_data : [];
        $matchingStop = null;

        foreach ($stops as $index => $stop) {
            if (!is_array($stop)) {
                continue;
            }

            $sequence = (int) ($stop['sequence'] ?? ($index + 1));
            $establishmentId = (int) ($stop['establishment_id'] ?? $stop['id'] ?? 0);

            if ($sequence === (int) $data['stop_sequence']
                && $establishmentId === (int) $data['establishment_id']) {
                $matchingStop = $stop;
                break;
            }
        }

        if (!$matchingStop) {
            return response()->json([
                'message' => 'This establishment does not match the selected stop in your Coffee Trail.',
            ], 422);
        }

        $establishment = Establishment::withTrashed()->find((int) $data['establishment_id']);

        if (!$establishment) {
            return response()->json([
                'message' => 'The establishment for this stop could not be found.',
            ], 404);
        }

        $check = CoffeeTrailLocationCheck::query()->updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'coffee_trail_id' => $trail->id,
                'stop_sequence' => (int) $data['stop_sequence'],
            ],
            [
                'establishment_id' => $establishment->id,
                'response' => $data['response'],
                'establishment_name_snapshot' => $establishment->name,
                'establishment_address_snapshot' => $establishment->address,
            ]
        );

        return response()->json([
            'message' => 'Location validation saved successfully.',
            'data' => [
                'id' => $check->id,
                'trail_id' => $check->coffee_trail_id,
                'establishment_id' => $check->establishment_id,
                'stop_sequence' => $check->stop_sequence,
                'response' => $check->response,
                'result' => $check->response === 'yes' ? 'correct_location' : 'incorrect_location',
                'establishment_name' => $check->establishment_name_snapshot,
                'establishment_address' => $check->establishment_address_snapshot,
                'review_status' => $check->review_status,
                'created_at' => optional($check->created_at)->toIso8601String(),
            ],
        ], 201);
    }
}

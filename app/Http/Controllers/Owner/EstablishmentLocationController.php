<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class EstablishmentLocationController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:13.85,14.05'],
            'longitude' => ['required', 'numeric', 'between:121.05,121.30'],
        ], [
            'latitude.between' => 'Choose a location within the Lipa map area.',
            'longitude.between' => 'Choose a location within the Lipa map area.',
        ]);

        $user = $request->user();

        $establishment = Establishment::query()
            ->whereNull('deleted_at')
            ->whereIn('type', ['farm', 'cafe'])
            ->where(function ($ownedByUser) use ($user) {
                $ownedByUser->where('owner_id', $user->id);

                if (Schema::hasColumn('establishments', 'user_id')) {
                    $ownedByUser->orWhere('user_id', $user->id);
                }
            })
            ->first();

        if (!$establishment) {
            return response()->json([
                'message' => 'No establishment is linked to your account. Please contact support.',
            ], 404);
        }

        $establishment->forceFill([
            'latitude' => (float) $validated['latitude'],
            'longitude' => (float) $validated['longitude'],
        ])->save();

        return response()->json([
            'message' => 'Your establishment location has been saved.',
            'establishment' => [
                'id' => $establishment->id,
                'name' => $establishment->name,
                'type' => $establishment->type,
                'latitude' => (float) $establishment->latitude,
                'longitude' => (float) $establishment->longitude,
            ],
        ]);
    }
}

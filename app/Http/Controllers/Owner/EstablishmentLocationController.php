<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class EstablishmentLocationController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'establishment_id' => ['required', 'integer', 'exists:establishments,id'],
            'latitude' => ['required', 'numeric', 'between:13.85,14.05'],
            'longitude' => ['required', 'numeric', 'between:121.05,121.30'],
        ], [
            'latitude.between' => 'Choose a location within the Lipa map area.',
            'longitude.between' => 'Choose a location within the Lipa map area.',
        ]);

        $user = $request->user();

        $establishment = Establishment::query()
            ->whereKey($validated['establishment_id'])
            ->whereNull('deleted_at')
            ->whereIn('type', ['farm', 'cafe', 'roaster'])
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

        $addressDetails = null;
        $reverseGeocodeSucceeded = false;

        try {
            $geocodeResponse = Http::withHeaders([
                'User-Agent' => 'BrewHub/1.0 (https://brewing-hub.online)',
                'Accept' => 'application/json',
            ])->timeout(5)->get('https://nominatim.openstreetmap.org/reverse', [
                'format' => 'jsonv2',
                'lat' => (float) $validated['latitude'],
                'lon' => (float) $validated['longitude'],
                'addressdetails' => 1,
                'zoom' => 18,
            ]);

            if ($geocodeResponse->successful()) {
                $geocodeData = $geocodeResponse->json();
                $addressDetails = is_array($geocodeData['address'] ?? null) ? $geocodeData['address'] : [];
                $reverseGeocodeSucceeded = !empty($geocodeData['display_name']);

                if ($reverseGeocodeSucceeded) {
                    $formattedAddress = trim((string) $geocodeData['display_name']);
                    $barangay = null;

                    foreach (['suburb', 'neighbourhood', 'quarter', 'village', 'hamlet', 'city_district', 'municipality'] as $candidate) {
                        if (!empty($addressDetails[$candidate])) {
                            $barangay = trim((string) $addressDetails[$candidate]);
                            break;
                        }
                    }

                    $establishment->address = mb_substr($formattedAddress, 0, 255);
                    if ($barangay !== null) {
                        $establishment->barangay = mb_substr($barangay, 0, 255);
                    }

                    $userLocation = [];
                    if (Schema::hasColumn('users', 'address')) {
                        $userLocation['address'] = mb_substr($formattedAddress, 0, 255);
                    }
                    if (Schema::hasColumn('users', 'barangay') && $barangay !== null) {
                        $userLocation['barangay'] = mb_substr($barangay, 0, 255);
                    }
                    if ($userLocation !== []) {
                        $user->forceFill($userLocation)->save();
                    }
                }
            }
        } catch (\\Throwable $exception) {
            // Saving the map pin must still work if the reverse-geocoding service is unavailable.
            report($exception);
        }

        $establishment->forceFill([
            'latitude' => (float) $validated['latitude'],
            'longitude' => (float) $validated['longitude'],
        ])->save();

        $message = $reverseGeocodeSucceeded
            ? 'Your establishment location and address have been saved.'
            : 'Your establishment location has been saved, but the address could not be retrieved automatically. Please try saving the pin again later.';

        return response()->json([
            'message' => $message,
            'address_updated' => $reverseGeocodeSucceeded,
            'establishment' => [
                'id' => $establishment->id,
                'name' => $establishment->name,
                'type' => $establishment->type,
                'address' => $establishment->address,
                'barangay' => $establishment->barangay,
                'latitude' => (float) $establishment->latitude,
                'longitude' => (float) $establishment->longitude,
            ],
        ]);
    }
}

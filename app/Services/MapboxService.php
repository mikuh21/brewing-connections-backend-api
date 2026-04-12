<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MapboxService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey  = config('services.mapbox.api_key');
        $this->baseUrl = config('services.mapbox.base_url');
    }

    /**
     * Get optimized route between multiple establishments
     * Used for Coffee Trail generation
     */
    public function getOptimizedRoute(array $coordinates): array
    {
        try {
            // Format coordinates for Mapbox
            // Each coordinate: "longitude,latitude"
            $coords = collect($coordinates)
                ->map(fn($c) => "{$c['longitude']},{$c['latitude']}")
                ->implode(';');

            $response = Http::get(
                "{$this->baseUrl}/optimized-trips/v1/mapbox/driving/{$coords}",
                [
                    'access_token'   => $this->apiKey,
                    'roundtrip'      => 'false',
                    'source'         => 'first',
                    'destination'    => 'last',
                    'geometries'     => 'geojson',
                    'overview'       => 'full',
                    'steps'          => 'true',
                ]
            );

            if ($response->successful()) {
                return [
                    'success'  => true,
                    'data'     => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => 'Mapbox route optimization failed',
            ];

        } catch (\Exception $e) {
            Log::error('Mapbox route error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get directions between two points
     * Used for navigation to a specific establishment
     */
    public function getDirections(
        float $fromLng,
        float $fromLat,
        float $toLng,
        float $toLat
    ): array {
        try {
            $response = Http::get(
                "{$this->baseUrl}/directions/v5/mapbox/driving/"
                . "{$fromLng},{$fromLat};{$toLng},{$toLat}",
                [
                    'access_token' => $this->apiKey,
                    'geometries'   => 'geojson',
                    'overview'     => 'full',
                    'steps'        => 'true',
                    'language'     => 'en',
                ]
            );

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data'    => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => 'Could not get directions',
            ];

        } catch (\Exception $e) {
            Log::error('Mapbox directions error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Calculate distance matrix between establishments
     * Used to find nearest farms/cafes to user location
     */
    public function getDistanceMatrix(
        array $origins,
        array $destinations
    ): array {
        try {
            $allCoords = array_merge($origins, $destinations);
            $coords    = collect($allCoords)
                ->map(fn($c) => "{$c['longitude']},{$c['latitude']}")
                ->implode(';');

            $sourceIndexes = implode(
                ',',
                range(0, count($origins) - 1)
            );
            $destIndexes   = implode(
                ',',
                range(
                    count($origins),
                    count($allCoords) - 1
                )
            );

            $response = Http::get(
                "{$this->baseUrl}/directions-matrix/v1/mapbox/driving/{$coords}",
                [
                    'access_token' => $this->apiKey,
                    'sources'      => $sourceIndexes,
                    'destinations' => $destIndexes,
                    'annotations'  => 'distance,duration',
                ]
            );

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data'    => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => 'Distance matrix failed',
            ];

        } catch (\Exception $e) {
            Log::error('Mapbox matrix error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Geocode an address to coordinates
     * Used when registering establishment by address
     */
    public function geocodeAddress(string $address): array
    {
        try {
            $encoded  = urlencode($address . ', Lipa, Batangas, Philippines');
            $response = Http::get(
                "{$this->baseUrl}/geocoding/v5/mapbox.places/{$encoded}.json",
                [
                    'access_token' => $this->apiKey,
                    'country'      => 'PH',
                    'proximity'    => '121.1631,13.9411', // Lipa City center
                    'limit'        => 1,
                ]
            );

            if ($response->successful()) {
                $data     = $response->json();
                $features = $data['features'] ?? [];

                if (!empty($features)) {
                    $coords = $features[0]['geometry']['coordinates'];
                    return [
                        'success'   => true,
                        'longitude' => $coords[0],
                        'latitude'  => $coords[1],
                        'place'     => $features[0]['place_name'],
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Address not found',
            ];

        } catch (\Exception $e) {
            Log::error('Mapbox geocoding error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
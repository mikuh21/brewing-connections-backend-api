<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleMapsService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey  = config('services.google_maps.key');
        $this->baseUrl = config('services.google_maps.base_url');
    }

    /**
     * Get directions between two points
     * Used for navigation to establishments
     */
    public function getDirections(
        float $fromLat,
        float $fromLng,
        float $toLat,
        float $toLng
    ): array {
        try {
            $response = Http::get(
                "{$this->baseUrl}/directions/json",
                [
                    'origin'      => "{$fromLat},{$fromLng}",
                    'destination' => "{$toLat},{$toLng}",
                    'mode'        => 'driving',
                    'language'    => 'en',
                    'region'      => 'ph',
                    'key'         => $this->apiKey,
                ]
            );

            if ($response->successful()) {
                $data = $response->json();

                if ($data['status'] === 'OK') {
                    $route = $data['routes'][0];
                    $leg   = $route['legs'][0];

                    return [
                        'success'          => true,
                        'distance'         => $leg['distance']['text'],
                        'duration'         => $leg['duration']['text'],
                        'distance_meters'  => $leg['distance']['value'],
                        'duration_seconds' => $leg['duration']['value'],
                        'steps'            => $leg['steps'],
                        'polyline'         => $route['overview_polyline']['points'],
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Could not get directions',
            ];

        } catch (\Exception $e) {
            Log::error('Google Maps directions error: '
                       . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Calculate distance between multiple points
     * Used to find nearest establishments
     */
    public function getDistanceMatrix(
        array $origins,
        array $destinations
    ): array {
        try {
            $originsStr = collect($origins)
                ->map(fn($o) => "{$o['lat']},{$o['lng']}")
                ->implode('|');

            $destsStr = collect($destinations)
                ->map(fn($d) => "{$d['lat']},{$d['lng']}")
                ->implode('|');

            $response = Http::get(
                "{$this->baseUrl}/distancematrix/json",
                [
                    'origins'      => $originsStr,
                    'destinations' => $destsStr,
                    'mode'         => 'driving',
                    'region'       => 'ph',
                    'key'          => $this->apiKey,
                ]
            );

            if ($response->successful()) {
                $data = $response->json();

                if ($data['status'] === 'OK') {
                    return [
                        'success' => true,
                        'data'    => $data,
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Distance matrix failed',
            ];

        } catch (\Exception $e) {
            Log::error('Google Maps matrix error: '
                       . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Geocode an address to coordinates
     * Used when registering a new establishment
     */
    public function geocodeAddress(string $address): array
    {
        try {
            $response = Http::get(
                "{$this->baseUrl}/geocode/json",
                [
                    'address'  => $address
                                  . ', Lipa, Batangas, Philippines',
                    'region'   => 'ph',
                    'language' => 'en',
                    'key'      => $this->apiKey,
                ]
            );

            if ($response->successful()) {
                $data = $response->json();

                if ($data['status'] === 'OK') {
                    $location = $data['results'][0]['geometry']['location'];
                    return [
                        'success'   => true,
                        'latitude'  => $location['lat'],
                        'longitude' => $location['lng'],
                        'formatted' => $data['results'][0]['formatted_address'],
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Address not found',
            ];

        } catch (\Exception $e) {
            Log::error('Google Maps geocoding error: '
                       . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Search nearby coffee-related places
     * Used to discover unregistered cafes nearby
     */
    public function searchNearbyPlaces(
        float  $lat,
        float  $lng,
        int    $radius = 5000,
        string $type   = 'cafe'
    ): array {
        try {
            $response = Http::get(
                "{$this->baseUrl}/place/nearbysearch/json",
                [
                    'location' => "{$lat},{$lng}",
                    'radius'   => $radius,
                    'type'     => $type,
                    'keyword'  => 'barako coffee',
                    'key'      => $this->apiKey,
                ]
            );

            if ($response->successful()) {
                $data = $response->json();

                if ($data['status'] === 'OK') {
                    return [
                        'success' => true,
                        'places'  => $data['results'],
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'No places found',
            ];

        } catch (\Exception $e) {
            Log::error('Google Maps places error: '
                       . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
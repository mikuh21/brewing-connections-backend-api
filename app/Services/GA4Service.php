<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GA4Service
{
    protected string $measurementId;
    protected string $apiSecret;
    protected string $baseUrl;

    public function __construct()
    {
        $this->measurementId = config('services.ga4.measurement_id');
        $this->apiSecret     = config('services.ga4.api_secret');
        $this->baseUrl       = config('services.ga4.base_url',
            'https://www.google-analytics.com/mp/collect'
        );
    }

    /**
     * Send any event to GA4
     */
    public function sendEvent(
        string $clientId,
        string $eventName,
        array  $params = []
    ): bool {
        try {
            $response = Http::post(
                "{$this->baseUrl}"
                . "?measurement_id={$this->measurementId}"
                . "&api_secret={$this->apiSecret}",
                [
                    'client_id' => $clientId,
                    'events'    => [[
                        'name'   => $eventName,
                        'params' => array_merge($params, [
                            'engagement_time_msec' => 100,
                        ]),
                    ]],
                ]
            );

            return $response->successful();

        } catch (\Exception $e) {
            Log::error('GA4 error: ' . $e->getMessage());
            return false;
        }
    }

    // ─────────────────────────────────────────
    // ESTABLISHMENT EVENTS
    // ─────────────────────────────────────────

    public function trackEstablishmentCreated(
        string $clientId,
        string $name,
        string $type,
        string $barangay
    ): bool {
        return $this->sendEvent(
            $clientId,
            'establishment_created',
            [
                'establishment_name' => $name,
                'establishment_type' => $type,
                'barangay'           => $barangay,
            ]
        );
    }

    public function trackEstablishmentView(
        string $clientId,
        string $name,
        string $variety
    ): bool {
        return $this->sendEvent(
            $clientId,
            'establishment_viewed',
            [
                'establishment_name' => $name,
                'variety'            => $variety,
            ]
        );
    }

    // ─────────────────────────────────────────
    // COFFEE TRAIL EVENTS
    // ─────────────────────────────────────────

    public function trackCoffeeTrail(
        string $clientId,
        string $variety,
        int    $stops,
        float  $distance
    ): bool {
        return $this->sendEvent(
            $clientId,
            'coffee_trail_generated',
            [
                'variety_preference' => $variety,
                'number_of_stops'    => $stops,
                'total_distance_km'  => $distance,
            ]
        );
    }

    // ─────────────────────────────────────────
    // MARKETPLACE EVENTS
    // ─────────────────────────────────────────

    public function trackOrder(
        string $clientId,
        string $orderId,
        float  $total,
        string $variety
    ): bool {
        return $this->sendEvent(
            $clientId,
            'purchase',
            [
                'transaction_id' => $orderId,
                'value'          => $total,
                'currency'       => 'PHP',
                'variety'        => $variety,
            ]
        );
    }

    public function trackBulkOrder(
        string $clientId,
        string $orderId,
        float  $total,
        int    $itemCount
    ): bool {
        return $this->sendEvent(
            $clientId,
            'bulk_order_placed',
            [
                'transaction_id' => $orderId,
                'value'          => $total,
                'currency'       => 'PHP',
                'item_count'     => $itemCount,
            ]
        );
    }

    // ─────────────────────────────────────────
    // REVIEW EVENTS
    // ─────────────────────────────────────────

    public function trackReview(
        string $clientId,
        string $establishmentName,
        float  $overallRating
    ): bool {
        return $this->sendEvent(
            $clientId,
            'review_submitted',
            [
                'establishment'  => $establishmentName,
                'overall_rating' => $overallRating,
            ]
        );
    }

    // ─────────────────────────────────────────
    // USER EVENTS
    // ─────────────────────────────────────────

    public function trackLogin(
        string $clientId,
        string $role
    ): bool {
        return $this->sendEvent(
            $clientId,
            'login',
            ['method' => $role]
        );
    }

    public function trackRegister(
        string $clientId,
        string $role
    ): bool {
        return $this->sendEvent(
            $clientId,
            'sign_up',
            ['method' => $role]
        );
    }
}
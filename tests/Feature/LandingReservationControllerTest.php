<?php

namespace Tests\Feature;

use Tests\TestCase;

class LandingReservationControllerTest extends TestCase
{
    public function test_it_rejects_past_pickup_dates_with_a_user_friendly_error(): void
    {
        $response = $this->postJson(route('reservations.orders.store'), [
            'product_id' => 1,
            'quantity' => 1,
            'pickup_date' => now()->subDay()->format('Y-m-d'),
            'pickup_time' => '10:00',
            'full_name' => 'Juan Dela Cruz',
            'email' => 'juan@example.com',
            'address' => '123 Sample Street',
            'phone' => '09171234567',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.pickup_date.0', 'Pickup date cannot be in the past.');
    }
}

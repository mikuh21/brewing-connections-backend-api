<?php

namespace Tests\Feature;

use Database\Seeders\CoffeeVarietySeeder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMapEstablishmentValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CoffeeVarietySeeder::class);
    }

    public function test_farm_establishment_can_be_created_without_owner_password(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->post('/admin/map', [
            'name' => 'Sunrise Farm',
            'type' => 'farm',
            'address' => '123 Farm Road',
            'barangay' => 'San Jose',
            'latitude' => 13.95,
            'longitude' => 121.12,
            'varieties' => [1],
            'primary_variety' => 1,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('establishments', ['name' => 'Sunrise Farm']);
    }

    public function test_cafe_requires_email_and_password_for_new_owner_account(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->post('/admin/map', [
            'name' => 'Brew Café',
            'type' => 'cafe',
            'address' => 'Main Avenue',
            'barangay' => 'Lipa',
            'latitude' => 13.96,
            'longitude' => 121.13,
            'varieties' => [1],
            'primary_variety' => 1,
        ]);

        $response->assertSessionHasErrors(['email', 'owner_password']);
    }

    public function test_invalid_contact_number_is_rejected(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->post('/admin/map', [
            'name' => 'Brew Café',
            'type' => 'cafe',
            'email' => 'owner@example.com',
            'owner_password' => 'password123',
            'address' => 'Main Avenue',
            'barangay' => 'Lipa',
            'contact_number' => '12345',
            'latitude' => 13.96,
            'longitude' => 121.13,
            'varieties' => [1],
            'primary_variety' => 1,
        ]);

        $response->assertSessionHasErrors(['contact_number']);
    }
}

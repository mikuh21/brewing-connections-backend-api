<?php

namespace Tests\Feature;

use App\Models\Establishment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstablishmentSelfRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_farm_owner_can_register_and_get_an_owned_unpinned_establishment(): void
    {
        $response = $this->post('/register', [
            'owner_name' => 'Test Farmer',
            'establishment_name' => 'Green Valley Farm',
            'establishment_type' => 'farm',
            'email' => 'farmer@example.com',
            'password' => 'Coffee@123',
            'password_confirmation' => 'Coffee@123',
            'contact_number' => '09171234567',
            'address' => '123 Farm Road',
            'barangay' => 'San Jose',
            'terms_agreed' => '1',
        ]);

        $user = User::query()->where('email', 'farmer@example.com')->firstOrFail();

        $response->assertRedirect(route('farm-owner.map'));
        $this->assertAuthenticatedAs($user);
        $this->assertSame('farm_owner', $user->role);
        $this->assertDatabaseHas('establishments', [
            'owner_id' => $user->id,
            'name' => 'Green Valley Farm',
            'type' => 'farm',
            'latitude' => null,
            'longitude' => null,
        ]);
    }

    public function test_owner_can_pin_only_their_own_establishment_within_lipa_bounds(): void
    {
        $owner = User::factory()->create(['role' => 'farm_owner', 'status' => 'active']);
        $otherOwner = User::factory()->create(['role' => 'farm_owner', 'status' => 'active']);

        $ownEstablishment = Establishment::query()->create([
            'owner_id' => $owner->id,
            'name' => 'My Farm',
            'type' => 'farm',
        ]);

        $otherEstablishment = Establishment::query()->create([
            'owner_id' => $otherOwner->id,
            'name' => 'Other Farm',
            'type' => 'farm',
        ]);

        $response = $this->actingAs($owner)->postJson('/api/my-establishment/location', [
            'latitude' => 13.9411,
            'longitude' => 121.1634,
        ]);

        $response->assertOk()->assertJsonPath('establishment.id', $ownEstablishment->id);

        $this->assertDatabaseHas('establishments', [
            'id' => $ownEstablishment->id,
            'latitude' => 13.9411,
            'longitude' => 121.1634,
        ]);
        $this->assertDatabaseHas('establishments', [
            'id' => $otherEstablishment->id,
            'latitude' => null,
            'longitude' => null,
        ]);
    }

    public function test_owner_cannot_pin_outside_the_lipa_map_bounds(): void
    {
        $owner = User::factory()->create(['role' => 'farm_owner', 'status' => 'active']);
        Establishment::query()->create([
            'owner_id' => $owner->id,
            'name' => 'My Farm',
            'type' => 'farm',
        ]);

        $this->actingAs($owner)
            ->postJson('/api/my-establishment/location', [
                'latitude' => 15.0,
                'longitude' => 121.1634,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['latitude']);
    }
}

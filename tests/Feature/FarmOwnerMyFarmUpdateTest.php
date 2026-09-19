<?php

namespace Tests\Feature;

use App\Models\Establishment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FarmOwnerMyFarmUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_my_farm_saves_operating_hours_and_activities_from_the_profile_form(): void
    {
        $user = User::factory()->create([
            'role' => 'farm_owner',
            'status' => 'active',
        ]);

        $farm = Establishment::create([
            'owner_id' => $user->id,
            'name' => 'Test Farm',
            'type' => 'farm',
            'visit_hours' => 'Mon-Fri, 8:00 AM - 5:00 PM',
            'activities' => 'Farm tour',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch(route('farm-owner.my-farm.update'), [
                'farm_id' => $farm->id,
                'name' => 'Test Farm',
                'visit_hours' => 'Mon-Sat, 7:00 AM - 6:00 PM',
                'activities' => 'Farm tour, Cupping',
                'varieties' => [],
            ]);

        $response
            ->assertRedirect(route('farm-owner.my-farm', ['farm_id' => $farm->id]))
            ->assertSessionHas('status', 'Farm profile updated successfully.');

        $this->assertDatabaseHas('establishments', [
            'id' => $farm->id,
            'visit_hours' => 'Mon-Sat, 7:00 AM - 6:00 PM',
            'activities' => 'Farm tour, Cupping',
        ]);
    }
}

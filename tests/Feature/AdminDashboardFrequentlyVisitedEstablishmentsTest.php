<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\DashboardController;
use App\Models\CoffeeTrail;
use App\Models\CoffeeTrailMarkerView;
use App\Models\Establishment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;
use Tests\TestCase;

class AdminDashboardFrequentlyVisitedEstablishmentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_currently_resolved_entities_appear_with_original_scores(): void
    {
        $consumer = User::factory()->create();
        $active = $this->createEstablishment('Active Cafe');
        $deleted = $this->createEstablishment('Deleted Cafe');
        $deletedId = $deleted->id;

        CoffeeTrail::create([
            'trail_data' => [
                ['id' => $active->id],
                ['establishment_id' => $active->id],
                ['id' => $deletedId],
                ['id' => 999999],
            ],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        CoffeeTrailMarkerView::create([
            'user_id' => $consumer->id,
            'establishment_id' => $active->id,
            'map_session_id' => 'active-session',
            'viewed_at' => now(),
        ]);
        CoffeeTrailMarkerView::create([
            'user_id' => $consumer->id,
            'establishment_id' => $deletedId,
            'map_session_id' => 'deleted-session',
            'viewed_at' => now(),
        ]);

        $deleted->delete();

        $rows = $this->frequentlyVisited('all');
        $activeRow = collect($rows)->firstWhere('id', $active->id);

        $this->assertNotNull($activeRow);
        $this->assertSame(7, $activeRow['popularity_score']);
        $this->assertSame(2, $activeRow['trail_destinations']);
        $this->assertSame(1, $activeRow['marker_views']);
        $this->assertCount(0, collect($rows)->whereIn('id', [$deletedId, 999999]));
        $this->assertFalse(collect($rows)->contains(fn (array $row) => in_array($row['name'], ['Unknown Establishment', 'Unknown Location'], true)));
    }

    public function test_deleted_activity_is_excluded_from_every_popularity_window(): void
    {
        $deleted = $this->createEstablishment('Deleted Cafe');
        $deletedId = $deleted->id;

        foreach ([now()->subDays(2), now()->subDays(15), now()->subDays(45)] as $createdAt) {
            CoffeeTrail::create([
                'trail_data' => [['id' => $deletedId]],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        $deleted->delete();

        foreach (['7d', '30d', 'all'] as $window) {
            $this->assertFalse(collect($this->frequentlyVisited($window))->contains('id', $deletedId));
        }
    }

    public function test_verified_reseller_activity_remains_resolved(): void
    {
        $reseller = User::factory()->create([
            'role' => 'reseller',
            'is_verified_reseller' => true,
            'barangay' => 'Reseller Barangay',
        ]);

        CoffeeTrail::create([
            'trail_data' => [['id' => $reseller->id]],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $row = collect($this->frequentlyVisited('all'))->firstWhere('id', $reseller->id);

        $this->assertNotNull($row);
        $this->assertSame($reseller->name, $row['name']);
        $this->assertSame(3, $row['popularity_score']);
    }

    private function frequentlyVisited(string $window): array
    {
        $method = new ReflectionMethod(DashboardController::class, 'getFrequentlyVisitedEstablishments');
        $method->setAccessible(true);

        return $method->invoke(app(DashboardController::class), $window);
    }

    private function createEstablishment(string $name): Establishment
    {
        return Establishment::create([
            'owner_id' => User::factory()->create()->id,
            'name' => $name,
            'type' => 'cafe',
            'address' => 'Test Address',
            'barangay' => 'Test Barangay',
            'latitude' => 13.95,
            'longitude' => 121.12,
        ]);
    }
}
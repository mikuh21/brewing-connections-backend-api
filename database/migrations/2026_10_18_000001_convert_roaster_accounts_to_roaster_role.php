<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Convert roaster establishment owners to the dedicated roaster role.
     *
     * Coffee-shop owners remain cafe_owner. Existing roaster products are also
     * relabeled so they continue to appear in the marketplace after the role
     * split (cafe_owner products are reserved for coffee-shop menu items).
     */
    public function up(): void
    {
        if (!Schema::hasTable('users') || !Schema::hasTable('establishments')) {
            return;
        }

        $establishmentColumns = Schema::getColumnListing('establishments');
        $roasterEstablishments = DB::table('establishments')
            ->where('type', 'roaster')
            ->get(array_values(array_intersect(
                ['id', 'owner_id', 'user_id'],
                $establishmentColumns
            )));

        $ownerIds = $roasterEstablishments
            ->flatMap(fn ($establishment) => [
                $establishment->owner_id ?? null,
                $establishment->user_id ?? null,
            ])
            ->filter(fn ($id) => $id !== null)
            ->unique()
            ->values();

        if ($ownerIds->isNotEmpty()) {
            DB::table('users')
                ->whereIn('id', $ownerIds)
                ->where('role', 'cafe_owner')
                ->update(['role' => 'roaster']);
        }

        if (
            Schema::hasTable('products')
            && Schema::hasColumn('products', 'seller_type')
            && Schema::hasColumn('products', 'establishment_id')
        ) {
            $roasterEstablishmentIds = $roasterEstablishments
                ->pluck('id')
                ->filter(fn ($id) => $id !== null)
                ->unique()
                ->values();

            if ($roasterEstablishmentIds->isNotEmpty()) {
                DB::table('products')
                    ->whereIn('establishment_id', $roasterEstablishmentIds)
                    ->where('seller_type', 'cafe_owner')
                    ->update(['seller_type' => 'roaster']);
            }
        }
    }

    /**
     * Restore the legacy role and seller type if this migration is rolled back.
     */
    public function down(): void
    {
        if (!Schema::hasTable('users') || !Schema::hasTable('establishments')) {
            return;
        }

        $establishmentColumns = Schema::getColumnListing('establishments');
        $roasterEstablishments = DB::table('establishments')
            ->where('type', 'roaster')
            ->get(array_values(array_intersect(
                ['id', 'owner_id', 'user_id'],
                $establishmentColumns
            )));

        $ownerIds = $roasterEstablishments
            ->flatMap(fn ($establishment) => [
                $establishment->owner_id ?? null,
                $establishment->user_id ?? null,
            ])
            ->filter(fn ($id) => $id !== null)
            ->unique()
            ->values();

        if ($ownerIds->isNotEmpty()) {
            DB::table('users')
                ->whereIn('id', $ownerIds)
                ->where('role', 'roaster')
                ->update(['role' => 'cafe_owner']);
        }

        if (
            Schema::hasTable('products')
            && Schema::hasColumn('products', 'seller_type')
            && Schema::hasColumn('products', 'establishment_id')
        ) {
            $roasterEstablishmentIds = $roasterEstablishments
                ->pluck('id')
                ->filter(fn ($id) => $id !== null)
                ->unique()
                ->values();

            if ($roasterEstablishmentIds->isNotEmpty()) {
                DB::table('products')
                    ->whereIn('establishment_id', $roasterEstablishmentIds)
                    ->where('seller_type', 'roaster')
                    ->update(['seller_type' => 'cafe_owner']);
            }
        }
    }
};

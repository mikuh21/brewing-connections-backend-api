<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reconcile roaster owners created before or after the initial role split.
     * Genuine coffee-shop owners remain cafe_owner.
     */
    public function up(): void
    {
        if (!Schema::hasTable('users')
            || !Schema::hasColumn('users', 'role')
            || !Schema::hasTable('establishments')
            || !Schema::hasColumn('establishments', 'type')) {
            return;
        }

        $establishmentColumns = Schema::getColumnListing('establishments');
        $ownerColumns = array_values(array_intersect(['owner_id', 'user_id'], $establishmentColumns));

        if ($ownerColumns === []) {
            return;
        }

        $roasterQuery = DB::table('establishments')->where('type', 'roaster');
        if (in_array('deleted_at', $establishmentColumns, true)) {
            $roasterQuery->whereNull('deleted_at');
        }

        $roasterEstablishments = $roasterQuery->get(array_values(array_unique(array_merge(['id'], $ownerColumns))));
        $ownerIds = $roasterEstablishments
            ->flatMap(function ($establishment) use ($ownerColumns) {
                return collect($ownerColumns)->map(fn ($column) => $establishment->{$column} ?? null);
            })
            ->filter(fn ($id) => $id !== null)
            ->unique()
            ->values();

        if ($ownerIds->isNotEmpty()) {
            DB::table('users')
                ->whereIn('id', $ownerIds)
                ->where('role', 'cafe_owner')
                ->update(['role' => 'roaster']);
        }

        if (Schema::hasTable('products')
            && Schema::hasColumn('products', 'seller_type')
            && Schema::hasColumn('products', 'establishment_id')) {
            $establishmentIds = $roasterEstablishments
                ->pluck('id')
                ->filter(fn ($id) => $id !== null)
                ->unique()
                ->values();

            if ($establishmentIds->isNotEmpty()) {
                DB::table('products')
                    ->whereIn('establishment_id', $establishmentIds)
                    ->where('seller_type', 'cafe_owner')
                    ->update(['seller_type' => 'roaster']);
            }
        }
    }

    /**
     * Do not demote roaster accounts on rollback; the role split is intentional.
     */
    public function down(): void
    {
        // Intentionally irreversible data correction.
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Give roaster establishments a distinct user role without changing cafe owners.
     */
    public function up(): void
    {
        if (!Schema::hasTable('users') || !Schema::hasTable('establishments')) {
            return;
        }

        $ownerIds = collect();

        if (Schema::hasColumn('establishments', 'owner_id')) {
            $ownerIds = $ownerIds->merge(
                DB::table('establishments')
                    ->where('type', 'roaster')
                    ->whereNotNull('owner_id')
                    ->pluck('owner_id')
            );
        }

        if (Schema::hasColumn('establishments', 'user_id')) {
            $ownerIds = $ownerIds->merge(
                DB::table('establishments')
                    ->where('type', 'roaster')
                    ->whereNotNull('user_id')
                    ->pluck('user_id')
            );
        }

        $ownerIds = $ownerIds->map(fn ($id) => (int) $id)->unique()->values();

        if ($ownerIds->isNotEmpty()) {
            DB::table('users')
                ->where('role', 'cafe_owner')
                ->whereIn('id', $ownerIds)
                ->update(['role' => 'roaster']);
        }
    }

    /**
     * Restore the previous role only for users still linked to a roaster.
     */
    public function down(): void
    {
        if (!Schema::hasTable('users') || !Schema::hasTable('establishments')) {
            return;
        }

        $ownerIds = collect();

        if (Schema::hasColumn('establishments', 'owner_id')) {
            $ownerIds = $ownerIds->merge(
                DB::table('establishments')
                    ->where('type', 'roaster')
                    ->whereNotNull('owner_id')
                    ->pluck('owner_id')
            );
        }

        if (Schema::hasColumn('establishments', 'user_id')) {
            $ownerIds = $ownerIds->merge(
                DB::table('establishments')
                    ->where('type', 'roaster')
                    ->whereNotNull('user_id')
                    ->pluck('user_id')
            );
        }

        $ownerIds = $ownerIds->map(fn ($id) => (int) $id)->unique()->values();

        if ($ownerIds->isNotEmpty()) {
            DB::table('users')
                ->where('role', 'roaster')
                ->whereIn('id', $ownerIds)
                ->update(['role' => 'cafe_owner']);
        }
    }
};

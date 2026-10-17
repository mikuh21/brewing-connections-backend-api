<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Give accounts linked to roaster establishments their own role.
     * Actual cafe owners remain cafe_owner.
     */
    public function up(): void
    {
        if (!Schema::hasTable('users')
            || !Schema::hasColumn('users', 'role')
            || !Schema::hasTable('establishments')
            || !Schema::hasColumn('establishments', 'type')) {
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

        $ownerIds = $ownerIds->filter()->unique()->values();

        if ($ownerIds->isNotEmpty()) {
            DB::table('users')
                ->where('role', 'cafe_owner')
                ->whereIn('id', $ownerIds)
                ->update([
                    'role' => 'roaster',
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * Roll back only accounts that still own a roaster establishment.
     */
    public function down(): void
    {
        if (!Schema::hasTable('users')
            || !Schema::hasColumn('users', 'role')
            || !Schema::hasTable('establishments')
            || !Schema::hasColumn('establishments', 'type')) {
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

        $ownerIds = $ownerIds->filter()->unique()->values();

        if ($ownerIds->isNotEmpty()) {
            DB::table('users')
                ->where('role', 'roaster')
                ->whereIn('id', $ownerIds)
                ->update([
                    'role' => 'cafe_owner',
                    'updated_at' => now(),
                ]);
        }
    }
};

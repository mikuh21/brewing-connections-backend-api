<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        if (Schema::hasColumn('users', 'latitude')) {
            DB::statement('ALTER TABLE users ALTER COLUMN latitude TYPE NUMERIC(10,8) USING latitude::numeric');
        }

        if (Schema::hasColumn('users', 'longitude')) {
            DB::statement('ALTER TABLE users ALTER COLUMN longitude TYPE NUMERIC(11,8) USING longitude::numeric');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        if (Schema::hasColumn('users', 'latitude')) {
            DB::statement('ALTER TABLE users ALTER COLUMN latitude TYPE NUMERIC(10,7) USING latitude::numeric');
        }

        if (Schema::hasColumn('users', 'longitude')) {
            DB::statement('ALTER TABLE users ALTER COLUMN longitude TYPE NUMERIC(10,7) USING longitude::numeric');
        }
    }
};

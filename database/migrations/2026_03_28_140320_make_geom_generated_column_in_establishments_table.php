<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the existing geom column
        Schema::table('establishments', function (Blueprint $table) {
            $table->dropColumn('geom');
        });

        // Add the generated column
        DB::statement('ALTER TABLE establishments ADD COLUMN geom geometry(Point, 4326) GENERATED ALWAYS AS (ST_MakePoint(longitude, latitude)) STORED');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the generated column
        DB::statement('ALTER TABLE establishments DROP COLUMN geom');

        // Add back the regular column
        Schema::table('establishments', function (Blueprint $table) {
            $table->geometry('geom', 'POINT')->nullable();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('coffee_trails', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->decimal('origin_lat', 10, 7)->nullable()->after('user_id');
            $table->decimal('origin_lng', 10, 7)->nullable()->after('origin_lat');
            $table->json('preferences')->nullable()->after('origin_lng');
            $table->json('trail_data')->nullable()->after('preferences');
            $table->text('route_geometry')->nullable()->after('trail_data');
            $table->decimal('total_distance_km', 8, 3)->nullable()->after('route_geometry');
            $table->decimal('total_duration_min', 8, 1)->nullable()->after('total_distance_km');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coffee_trails', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn([
                'origin_lat',
                'origin_lng',
                'preferences',
                'trail_data',
                'route_geometry',
                'total_distance_km',
                'total_duration_min',
            ]);
        });
    }
};

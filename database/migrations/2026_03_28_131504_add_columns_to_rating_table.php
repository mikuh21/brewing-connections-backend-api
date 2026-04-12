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
        Schema::table('rating', function (Blueprint $table) {
            if (!Schema::hasColumn('rating', 'user_id')) {
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            }
            if (!Schema::hasColumn('rating', 'establishment_id')) {
                $table->foreignId('establishment_id')->constrained('establishments')->onDelete('cascade');
            }
            if (!Schema::hasColumn('rating', 'taste_rating')) {
                $table->tinyInteger('taste_rating');
            }
            if (!Schema::hasColumn('rating', 'environment_rating')) {
                $table->tinyInteger('environment_rating');
            }
            if (!Schema::hasColumn('rating', 'cleanliness_rating')) {
                $table->tinyInteger('cleanliness_rating');
            }
            if (!Schema::hasColumn('rating', 'service_rating')) {
                $table->tinyInteger('service_rating');
            }
            if (!Schema::hasColumn('rating', 'image')) {
                $table->string('image')->nullable();
            }
            if (!Schema::hasColumn('rating', 'owner_response')) {
                $table->text('owner_response')->nullable();
            }
        });

        // Add the generated overall_rating column
        DB::statement('ALTER TABLE rating ADD COLUMN overall_rating numeric(3,2) GENERATED ALWAYS AS (ROUND((taste_rating + environment_rating + cleanliness_rating + service_rating)::numeric / 4, 2)) STORED');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the generated column first
        DB::statement('ALTER TABLE rating DROP COLUMN overall_rating');

        Schema::table('rating', function (Blueprint $table) {
            $table->dropForeign(['user_id', 'establishment_id']);
            $table->dropColumn(['user_id', 'establishment_id', 'taste_rating', 'environment_rating', 'cleanliness_rating', 'service_rating', 'image', 'owner_response']);
        });
    }
};

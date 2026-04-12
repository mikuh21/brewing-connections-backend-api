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
        // Drop the existing overall_rating column
        Schema::table('rating', function (Blueprint $table) {
            $table->dropColumn('overall_rating');
        });

        // Add the generated column
        DB::statement('ALTER TABLE rating ADD COLUMN overall_rating numeric(3,2) GENERATED ALWAYS AS (ROUND((taste_rating + environment_rating + cleanliness_rating + service_rating)::numeric / 4, 2)) STORED');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the generated column
        DB::statement('ALTER TABLE rating DROP COLUMN overall_rating');

        // Add back the regular column
        Schema::table('rating', function (Blueprint $table) {
            $table->decimal('overall_rating', 3, 2)->nullable();
        });
    }
};

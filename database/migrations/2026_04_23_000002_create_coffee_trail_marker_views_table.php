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
        Schema::create('coffee_trail_marker_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('establishment_id')->constrained('establishments')->cascadeOnDelete();
            $table->string('map_session_id', 120)->nullable()->index();
            $table->timestamp('viewed_at')->index();
            $table->timestamps();

            $table->index(['establishment_id', 'viewed_at']);
            $table->index(['user_id', 'establishment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coffee_trail_marker_views');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('coffee_trail_location_checks')) {
            return;
        }

        Schema::create('coffee_trail_location_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('coffee_trail_id')->constrained('coffee_trails')->cascadeOnDelete();
            $table->foreignId('establishment_id')->nullable()->constrained('establishments')->nullOnDelete();
            $table->unsignedSmallInteger('stop_sequence');
            $table->string('response', 10);
            $table->string('establishment_name_snapshot')->nullable();
            $table->string('establishment_address_snapshot')->nullable();
            $table->string('review_status', 20)->default('pending');
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(
                ['user_id', 'coffee_trail_id', 'stop_sequence'],
                'coffee_trail_location_checks_user_trail_stop_unique'
            );
            $table->index(['response', 'review_status']);
            $table->index(['establishment_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coffee_trail_location_checks');
    }
};

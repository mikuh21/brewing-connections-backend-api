<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendation_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establishment_id')->constrained('establishments')->onDelete('cascade');
            $table->unsignedInteger('review_count')->default(0);
            $table->timestamp('generated_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('recommendation_snapshot_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recommendation_snapshot_id')->constrained('recommendation_snapshots')->onDelete('cascade');
            $table->enum('category', ['taste', 'environment', 'cleanliness', 'service']);
            $table->enum('priority', ['high', 'medium', 'low']);
            $table->decimal('average_score', 4, 2)->default(0);
            $table->text('insight');
            $table->text('suggested_action');
            $table->decimal('impact_score', 3, 2);
            $table->unsignedInteger('based_on_reviews')->default(0);
            $table->timestamp('generated_at')->nullable()->index();
            $table->timestamps();

            $table->index(['recommendation_snapshot_id', 'category'], 'snapshot_items_snapshot_category_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendation_snapshot_items');
        Schema::dropIfExists('recommendation_snapshots');
    }
};
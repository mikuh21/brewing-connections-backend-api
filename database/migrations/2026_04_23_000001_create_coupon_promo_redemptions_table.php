<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupon_promo_redemptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('coupon_promo_id')->constrained('coupon_promos')->cascadeOnDelete();
            $table->foreignId('consumer_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('establishment_id')->constrained('establishments')->cascadeOnDelete();
            $table->foreignId('scanned_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('redeemed_at');
            $table->timestamps();

            $table->unique(['coupon_promo_id', 'consumer_user_id'], 'coupon_promo_consumer_unique');
            $table->index(['establishment_id', 'redeemed_at'], 'coupon_promo_redemption_establishment_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_promo_redemptions');
    }
};
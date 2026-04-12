<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupon_promos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('establishment_id')->constrained('establishments')->cascadeOnDelete();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('discount_type', 20)->default('percentage');
            $table->decimal('discount_value', 8, 2);
            $table->string('qr_code_token', 255)->unique();
            $table->date('valid_from');
            $table->date('valid_until');
            $table->integer('max_usage')->default(100);
            $table->integer('used_count')->default(0);
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('establishment_id');
            $table->index('status');
            $table->index('valid_until');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_promos');
    }
};
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
        Schema::table('products', function (Blueprint $table) {
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category');
            $table->string('roast_level')->nullable();
            $table->string('grind_type')->nullable();
            $table->decimal('price_per_unit', 10, 2);
            $table->string('unit')->default('kg');
            $table->integer('moq')->default(1);
            $table->string('image_url')->nullable();
            $table->string('seller_type');
            $table->unsignedBigInteger('seller_id');
            $table->unsignedBigInteger('establishment_id')->nullable();
            $table->boolean('is_active')->default(true);

            $table->foreign('seller_id')->references('id')->on('users');
            $table->foreign('establishment_id')->references('id')->on('establishments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['seller_id']);
            $table->dropForeign(['establishment_id']);
            $table->dropColumn([
                'name',
                'description',
                'category',
                'roast_level',
                'grind_type',
                'price_per_unit',
                'unit',
                'moq',
                'image_url',
                'seller_type',
                'seller_id',
                'establishment_id',
                'is_active'
            ]);
        });
    }
};

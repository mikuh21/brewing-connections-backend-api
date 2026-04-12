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
        Schema::table('reseller_products', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('reseller_id');
            $table->decimal('reseller_price', 10, 2);
            $table->integer('stock_quantity')->default(0);
            $table->boolean('is_available')->default(true);

            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('reseller_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reseller_products', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['reseller_id']);
            $table->dropColumn([
                'product_id',
                'reseller_id',
                'reseller_price',
                'stock_quantity',
                'is_available'
            ]);
        });
    }
};

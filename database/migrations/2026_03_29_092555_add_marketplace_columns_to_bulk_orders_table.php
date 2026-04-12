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
        Schema::table('bulk_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('reseller_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('quantity_kg', 8, 2);
            $table->decimal('total_price', 10, 2);
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->date('delivery_date')->nullable();
            $table->text('notes')->nullable();

            $table->foreign('reseller_id')->references('id')->on('users');
            $table->foreign('product_id')->references('id')->on('products');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bulk_orders', function (Blueprint $table) {
            $table->dropForeign(['reseller_id']);
            $table->dropForeign(['product_id']);
            $table->dropColumn([
                'reseller_id',
                'product_id',
                'quantity_kg',
                'total_price',
                'status',
                'delivery_date',
                'notes'
            ]);
        });
    }
};

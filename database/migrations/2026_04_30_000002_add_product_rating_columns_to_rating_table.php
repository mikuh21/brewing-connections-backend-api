<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('rating', 'establishment_id')) {
            DB::statement('ALTER TABLE rating ALTER COLUMN establishment_id DROP NOT NULL');
        }

        Schema::table('rating', function (Blueprint $table) {
            if (!Schema::hasColumn('rating', 'product_id')) {
                $table->foreignId('product_id')->nullable()->after('establishment_id')->constrained('products')->nullOnDelete();
            }

            if (!Schema::hasColumn('rating', 'order_id')) {
                $table->foreignId('order_id')->nullable()->after('product_id')->constrained('orders')->nullOnDelete();
                $table->unique('order_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rating', function (Blueprint $table) {
            if (Schema::hasColumn('rating', 'order_id')) {
                $table->dropUnique(['order_id']);
                $table->dropConstrainedForeignId('order_id');
            }

            if (Schema::hasColumn('rating', 'product_id')) {
                $table->dropConstrainedForeignId('product_id');
            }
        });
    }
};
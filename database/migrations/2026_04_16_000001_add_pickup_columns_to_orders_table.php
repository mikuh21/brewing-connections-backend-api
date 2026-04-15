<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'pickup_date')) {
                $table->date('pickup_date')->nullable()->after('notes');
            }

            if (!Schema::hasColumn('orders', 'pickup_time')) {
                $table->string('pickup_time', 20)->nullable()->after('pickup_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'pickup_time')) {
                $table->dropColumn('pickup_time');
            }

            if (Schema::hasColumn('orders', 'pickup_date')) {
                $table->dropColumn('pickup_date');
            }
        });
    }
};

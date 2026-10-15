<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The original status column was declared as an enum with only the
        // initial statuses. Use a string so Ready for Pickup and Shipped can
        // be stored on every supported database engine.
        Schema::table('orders', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });
    }

    public function down(): void
    {
        // Keep this column as a string when rolling back: existing orders may
        // already contain the newer fulfilment statuses.
    }
};

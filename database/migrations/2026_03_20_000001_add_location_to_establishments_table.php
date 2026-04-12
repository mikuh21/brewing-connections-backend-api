<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // geom column is handled as a generated column via raw SQL
        // location column is not needed - using geom instead
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // nothing to reverse
    }
};

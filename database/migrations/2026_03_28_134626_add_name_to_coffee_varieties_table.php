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
        Schema::table('coffee_varieties', function (Blueprint $table) {
            if (!Schema::hasColumn('coffee_varieties', 'name')) {
                $table->string('name')->after('id');
            }
            if (!Schema::hasColumn('coffee_varieties', 'color')) {
                $table->string('color')->nullable()->after('name');
            }
            if (!Schema::hasColumn('coffee_varieties', 'description')) {
                $table->text('description')->nullable()->after('color');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coffee_varieties', function (Blueprint $table) {
            $table->dropColumn(['name', 'color', 'description']);
        });
    }
};

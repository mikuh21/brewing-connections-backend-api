<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('establishments') && !Schema::hasColumn('establishments', 'trivia_history')) {
            Schema::table('establishments', function (Blueprint $table) {
                $table->text('trivia_history')->nullable();
            });
        }

        // Reseller profiles are user-backed in the current application, while
        // farms, coffee shops, and roasters use the establishments table.
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'trivia_history')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('trivia_history')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('establishments') && Schema::hasColumn('establishments', 'trivia_history')) {
            Schema::table('establishments', function (Blueprint $table) {
                $table->dropColumn('trivia_history');
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'trivia_history')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('trivia_history');
            });
        }
    }
};

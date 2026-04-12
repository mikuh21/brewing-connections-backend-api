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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'profile_focus_x')) {
                $table->unsignedTinyInteger('profile_focus_x')->nullable()->after('image_url');
            }

            if (!Schema::hasColumn('users', 'profile_focus_y')) {
                $table->unsignedTinyInteger('profile_focus_y')->nullable()->after('profile_focus_x');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columnsToDrop = [];

            if (Schema::hasColumn('users', 'profile_focus_y')) {
                $columnsToDrop[] = 'profile_focus_y';
            }

            if (Schema::hasColumn('users', 'profile_focus_x')) {
                $columnsToDrop[] = 'profile_focus_x';
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};

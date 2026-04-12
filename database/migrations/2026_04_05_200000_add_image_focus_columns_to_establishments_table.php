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
        Schema::table('establishments', function (Blueprint $table) {
            if (!Schema::hasColumn('establishments', 'banner_focus_x')) {
                $table->unsignedTinyInteger('banner_focus_x')->default(50);
            }

            if (!Schema::hasColumn('establishments', 'banner_focus_y')) {
                $table->unsignedTinyInteger('banner_focus_y')->default(50);
            }

            if (!Schema::hasColumn('establishments', 'profile_focus_x')) {
                $table->unsignedTinyInteger('profile_focus_x')->default(50);
            }

            if (!Schema::hasColumn('establishments', 'profile_focus_y')) {
                $table->unsignedTinyInteger('profile_focus_y')->default(50);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('establishments', function (Blueprint $table) {
            $dropColumns = [];

            if (Schema::hasColumn('establishments', 'banner_focus_x')) {
                $dropColumns[] = 'banner_focus_x';
            }

            if (Schema::hasColumn('establishments', 'banner_focus_y')) {
                $dropColumns[] = 'banner_focus_y';
            }

            if (Schema::hasColumn('establishments', 'profile_focus_x')) {
                $dropColumns[] = 'profile_focus_x';
            }

            if (Schema::hasColumn('establishments', 'profile_focus_y')) {
                $dropColumns[] = 'profile_focus_y';
            }

            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};

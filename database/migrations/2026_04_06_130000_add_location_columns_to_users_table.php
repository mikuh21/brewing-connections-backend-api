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
            if (!Schema::hasColumn('users', 'address')) {
                $table->string('address')->nullable()->after('image_url');
            }

            if (!Schema::hasColumn('users', 'barangay')) {
                $table->string('barangay')->nullable()->after('address');
            }

            if (!Schema::hasColumn('users', 'contact_number')) {
                $table->string('contact_number')->nullable()->after('barangay');
            }

            if (!Schema::hasColumn('users', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->after('contact_number');
            }

            if (!Schema::hasColumn('users', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
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

            if (Schema::hasColumn('users', 'longitude')) {
                $columnsToDrop[] = 'longitude';
            }

            if (Schema::hasColumn('users', 'latitude')) {
                $columnsToDrop[] = 'latitude';
            }

            if (Schema::hasColumn('users', 'barangay')) {
                $columnsToDrop[] = 'barangay';
            }

            if (Schema::hasColumn('users', 'contact_number')) {
                $columnsToDrop[] = 'contact_number';
            }

            if (Schema::hasColumn('users', 'address')) {
                $columnsToDrop[] = 'address';
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};

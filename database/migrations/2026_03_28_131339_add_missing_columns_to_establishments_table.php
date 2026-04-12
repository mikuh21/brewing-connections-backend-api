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
            if (!Schema::hasColumn('establishments', 'owner_id')) {
                $table->bigInteger('owner_id')->nullable();
            }
            if (!Schema::hasColumn('establishments', 'name')) {
                $table->string('name')->nullable();
            }
            if (!Schema::hasColumn('establishments', 'type')) {
                $table->string('type', 255)->nullable();
            }
            if (!Schema::hasColumn('establishments', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('establishments', 'address')) {
                $table->string('address', 255)->nullable();
            }
            if (!Schema::hasColumn('establishments', 'barangay')) {
                $table->string('barangay', 255)->nullable();
            }
            if (!Schema::hasColumn('establishments', 'contact_number')) {
                $table->string('contact_number', 255)->nullable();
            }
            if (!Schema::hasColumn('establishments', 'email')) {
                $table->string('email', 255)->nullable();
            }
            if (!Schema::hasColumn('establishments', 'website')) {
                $table->string('website', 255)->nullable();
            }
            if (!Schema::hasColumn('establishments', 'image')) {
                $table->string('image', 255)->nullable();
            }
            if (!Schema::hasColumn('establishments', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable();
            }
            if (!Schema::hasColumn('establishments', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('establishments', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
            $table->dropColumn(['owner_id', 'name', 'type', 'description', 'address', 'barangay', 'contact_number', 'email', 'website', 'image']);
        });
    }
};

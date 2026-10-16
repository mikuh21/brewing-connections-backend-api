<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('establishments')) {
            return;
        }

        Schema::table('establishments', function (Blueprint $table) {
            if (!Schema::hasColumn('establishments', 'location_notice_email_sent_at')) {
                $table->timestamp('location_notice_email_sent_at')->nullable();
            }
            if (!Schema::hasColumn('establishments', 'location_notice_email_sent_to')) {
                $table->string('location_notice_email_sent_to')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('establishments')) {
            return;
        }

        Schema::table('establishments', function (Blueprint $table) {
            if (Schema::hasColumn('establishments', 'location_notice_email_sent_to')) {
                $table->dropColumn('location_notice_email_sent_to');
            }
            if (Schema::hasColumn('establishments', 'location_notice_email_sent_at')) {
                $table->dropColumn('location_notice_email_sent_at');
            }
        });
    }
};

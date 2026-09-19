<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('conversation_participants')) {
            Schema::table('conversation_participants', function (Blueprint $table) {
                $table->index(
                    ['user_id', 'conversation_id'],
                    'conversation_participants_user_conversation_idx'
                );
            });
        }

        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->index(
                    ['conversation_id', 'created_at'],
                    'messages_conversation_created_idx'
                );
                $table->index(
                    ['conversation_id', 'sender_id', 'created_at'],
                    'messages_conversation_sender_created_idx'
                );
            });
        }

        if (Schema::hasTable('recommendations')) {
            Schema::table('recommendations', function (Blueprint $table) {
                $table->index(
                    ['establishment_id', 'category'],
                    'recommendations_establishment_category_idx'
                );
                $table->index(
                    ['establishment_id', 'generated_at'],
                    'recommendations_establishment_generated_idx'
                );
            });
        }

        if (Schema::hasTable('recommendation_snapshots')) {
            Schema::table('recommendation_snapshots', function (Blueprint $table) {
                $table->index(
                    ['establishment_id', 'generated_at'],
                    'recommendation_snapshots_establishment_generated_idx'
                );
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('recommendation_snapshots')) {
            Schema::table('recommendation_snapshots', function (Blueprint $table) {
                $table->dropIndex('recommendation_snapshots_establishment_generated_idx');
            });
        }

        if (Schema::hasTable('recommendations')) {
            Schema::table('recommendations', function (Blueprint $table) {
                $table->dropIndex('recommendations_establishment_category_idx');
                $table->dropIndex('recommendations_establishment_generated_idx');
            });
        }

        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropIndex('messages_conversation_created_idx');
                $table->dropIndex('messages_conversation_sender_created_idx');
            });
        }

        if (Schema::hasTable('conversation_participants')) {
            Schema::table('conversation_participants', function (Blueprint $table) {
                $table->dropIndex('conversation_participants_user_conversation_idx');
            });
        }
    }
};

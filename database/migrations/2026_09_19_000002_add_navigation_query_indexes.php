<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('establishments')) {
            Schema::table('establishments', function (Blueprint $table) {
                if (Schema::hasColumn('establishments', 'owner_id')) {
                    $table->index('owner_id', 'establishments_owner_id_idx');
                }

                if (Schema::hasColumn('establishments', 'user_id')) {
                    $table->index('user_id', 'establishments_user_id_idx');
                }

                if (Schema::hasColumn('establishments', 'type')) {
                    $table->index('type', 'establishments_type_idx');
                }

                if (Schema::hasColumn('establishments', 'updated_at')) {
                    $table->index('updated_at', 'establishments_updated_at_idx');
                }
            });
        }

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (Schema::hasColumn('products', 'establishment_id')) {
                    $table->index(
                        ['establishment_id', 'is_active'],
                        'products_establishment_active_idx'
                    );
                }

                if (Schema::hasColumn('products', 'seller_id')) {
                    $table->index('seller_id', 'products_seller_id_idx');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (Schema::hasColumn('products', 'seller_id')) {
                    $table->dropIndex('products_seller_id_idx');
                }

                if (Schema::hasColumn('products', 'establishment_id')) {
                    $table->dropIndex('products_establishment_active_idx');
                }
            });
        }

        if (Schema::hasTable('establishments')) {
            Schema::table('establishments', function (Blueprint $table) {
                if (Schema::hasColumn('establishments', 'updated_at')) {
                    $table->dropIndex('establishments_updated_at_idx');
                }

                if (Schema::hasColumn('establishments', 'type')) {
                    $table->dropIndex('establishments_type_idx');
                }

                if (Schema::hasColumn('establishments', 'user_id')) {
                    $table->dropIndex('establishments_user_id_idx');
                }

                if (Schema::hasColumn('establishments', 'owner_id')) {
                    $table->dropIndex('establishments_owner_id_idx');
                }
            });
        }
    }
};

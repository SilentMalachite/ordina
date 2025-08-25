<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 商品テーブルに同期用カラムを追加
        Schema::table('products', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->after('id');
            $table->timestamp('last_synced_at')->nullable()->after('updated_at');
            $table->boolean('is_dirty')->default(false)->after('last_synced_at');
            $table->index('is_dirty');
        });

        // 顧客テーブルに同期用カラムを追加
        Schema::table('customers', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->after('id');
            $table->timestamp('last_synced_at')->nullable()->after('updated_at');
            $table->boolean('is_dirty')->default(false)->after('last_synced_at');
            $table->index('is_dirty');
        });

        // 取引テーブルに同期用カラムを追加
        Schema::table('transactions', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->after('id');
            $table->timestamp('last_synced_at')->nullable()->after('updated_at');
            $table->boolean('is_dirty')->default(false)->after('last_synced_at');
            $table->index('is_dirty');
        });

        // 在庫調整テーブルに同期用カラムを追加
        Schema::table('inventory_adjustments', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->after('id');
            $table->timestamp('last_synced_at')->nullable()->after('updated_at');
            $table->boolean('is_dirty')->default(false)->after('last_synced_at');
            $table->index('is_dirty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_uuid_unique');
            $table->dropIndex('products_is_dirty_index');
            $table->dropColumn(['uuid', 'last_synced_at', 'is_dirty']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique('customers_uuid_unique');
            $table->dropIndex('customers_is_dirty_index');
            $table->dropColumn(['uuid', 'last_synced_at', 'is_dirty']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropUnique('transactions_uuid_unique');
            $table->dropIndex('transactions_is_dirty_index');
            $table->dropColumn(['uuid', 'last_synced_at', 'is_dirty']);
        });

        Schema::table('inventory_adjustments', function (Blueprint $table) {
            $table->dropUnique('inventory_adjustments_uuid_unique');
            $table->dropIndex('inventory_adjustments_is_dirty_index');
            $table->dropColumn(['uuid', 'last_synced_at', 'is_dirty']);
        });
    }
};
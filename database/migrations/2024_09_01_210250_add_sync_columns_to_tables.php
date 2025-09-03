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
            $table->dropColumn(['uuid', 'last_synced_at', 'is_dirty']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['uuid', 'last_synced_at', 'is_dirty']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['uuid', 'last_synced_at', 'is_dirty']);
        });

        Schema::table('inventory_adjustments', function (Blueprint $table) {
            $table->dropColumn(['uuid', 'last_synced_at', 'is_dirty']);
        });
    }
};
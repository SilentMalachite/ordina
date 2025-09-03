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
        Schema::create('sync_conflicts', function (Blueprint $table) {
            $table->id();
            $table->string('table_name'); // 競合が発生したテーブル名
            $table->uuid('record_uuid'); // 競合したレコードのUUID
            $table->json('local_data'); // ローカルのデータ
            $table->json('server_data'); // サーバーのデータ
            $table->string('resolution_strategy')->nullable(); // 解決戦略
            $table->enum('status', ['pending', 'resolved', 'ignored'])->default('pending'); // 競合の状態
            $table->unsignedBigInteger('user_id')->nullable(); // 競合を解決するユーザー
            $table->timestamp('resolved_at')->nullable(); // 解決日時
            $table->text('conflict_reason')->nullable(); // 競合の理由
            $table->timestamps();

            $table->index(['table_name', 'status']);
            $table->index('record_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_conflicts');
    }
};

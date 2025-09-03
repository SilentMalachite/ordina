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
        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // トークンの名前（識別用）
            $table->string('token', 64)->unique(); // ハッシュ化されたトークン
            $table->json('abilities')->nullable(); // トークンの権限
            $table->timestamp('expires_at')->nullable(); // 有効期限
            $table->timestamp('last_used_at')->nullable(); // 最終使用日時
            $table->unsignedBigInteger('user_id'); // トークン所有者
            $table->boolean('revoked')->default(false); // 取り消しフラグ
            $table->timestamps();

            $table->index(['user_id', 'revoked']);
            $table->index('token');
            $table->index('expires_at');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_tokens');
    }
};

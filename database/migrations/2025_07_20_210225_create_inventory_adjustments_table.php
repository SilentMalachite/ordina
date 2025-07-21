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
        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            $table->foreignId('user_id')->constrained()->onDelete('restrict');
            $table->enum('adjustment_type', ['increase', 'decrease']);
            $table->integer('quantity');
            $table->integer('previous_quantity');
            $table->integer('new_quantity');
            $table->string('reason');
            $table->timestamps();
            
            $table->index(['product_id', 'created_at']);
            $table->index('adjustment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_adjustments');
    }
};

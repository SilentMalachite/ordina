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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_code')->unique();
            $table->string('name');
            $table->integer('stock_quantity')->default(0);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('selling_price', 10, 2);
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index('product_code');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

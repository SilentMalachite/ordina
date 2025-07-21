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
        Schema::table('closing_dates', function (Blueprint $table) {
            $table->string('description', 500)->nullable()->after('day_of_month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('closing_dates', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};

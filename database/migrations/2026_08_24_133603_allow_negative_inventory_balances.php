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
        Schema::table('branch_food_stocks', function (Blueprint $table) {
            $table->decimal('quantity_in_kg', 12, 3)->default(0)->change();
        });

        Schema::table('food_stock_movements', function (Blueprint $table) {
            $table->decimal('balance_before_kg', 12, 3)->change();
            $table->decimal('balance_after_kg', 12, 3)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch_food_stocks', function (Blueprint $table) {
            $table->decimal('quantity_in_kg', 12, 3)->unsigned()->default(0)->change();
        });

        Schema::table('food_stock_movements', function (Blueprint $table) {
            $table->decimal('balance_before_kg', 12, 3)->unsigned()->change();
            $table->decimal('balance_after_kg', 12, 3)->unsigned()->change();
        });
    }
};

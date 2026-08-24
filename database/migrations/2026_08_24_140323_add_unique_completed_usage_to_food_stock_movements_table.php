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
        Schema::table('food_stock_movements', function (Blueprint $table) {
            $table->unique(
                ['bill_detail_id', 'type'],
                'food_stock_movements_bill_detail_type_unique',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('food_stock_movements', function (Blueprint $table) {
            $table->dropUnique('food_stock_movements_bill_detail_type_unique');
        });
    }
};

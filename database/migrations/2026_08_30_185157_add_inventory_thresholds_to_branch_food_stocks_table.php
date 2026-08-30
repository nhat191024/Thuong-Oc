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
            $table->decimal('red_threshold_kg', 12, 3)
                ->unsigned()
                ->default(10)
                ->after('quantity_in_kg');
            $table->decimal('yellow_threshold_kg', 12, 3)
                ->unsigned()
                ->default(20)
                ->after('red_threshold_kg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch_food_stocks', function (Blueprint $table) {
            $table->dropColumn([
                'red_threshold_kg',
                'yellow_threshold_kg',
            ]);
        });
    }
};

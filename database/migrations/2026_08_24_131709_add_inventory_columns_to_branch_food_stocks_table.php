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
            $table->boolean('is_inventory_tracked')
                ->default(false)
                ->after('is_out_of_stock');
            $table->decimal('quantity_in_kg', 12, 3)
                ->default(0)
                ->after('is_inventory_tracked');
            $table->timestamp('last_restocked_at')
                ->nullable()
                ->after('quantity_in_kg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch_food_stocks', function (Blueprint $table) {
            $table->dropColumn([
                'is_inventory_tracked',
                'quantity_in_kg',
                'last_restocked_at',
            ]);
        });
    }
};

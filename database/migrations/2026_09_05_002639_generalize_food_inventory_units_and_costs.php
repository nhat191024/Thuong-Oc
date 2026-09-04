<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $kilogramId = DB::table('inventory_units')->where('symbol', 'kg')->value('id');

        Schema::table('foods', function (Blueprint $table) {
            $table->foreignId('inventory_unit_id')
                ->nullable()
                ->after('category_id')
                ->constrained('inventory_units')
                ->restrictOnDelete();
            $table->unsignedBigInteger('purchase_price')->nullable()->after('price');
        });

        DB::table('foods')->update(['inventory_unit_id' => $kilogramId]);

        Schema::table('dishes', function (Blueprint $table) {
            $table->renameColumn('ingredient_quantity_kg', 'ingredient_quantity');
        });

        Schema::table('branch_food_stocks', function (Blueprint $table) {
            $table->renameColumn('quantity_in_kg', 'quantity');
            $table->renameColumn('red_threshold_kg', 'red_threshold');
            $table->renameColumn('yellow_threshold_kg', 'yellow_threshold');
        });

        Schema::table('food_stock_movements', function (Blueprint $table) {
            $table->renameColumn('quantity_in_kg', 'quantity');
            $table->renameColumn('balance_before_kg', 'balance_before');
            $table->renameColumn('balance_after_kg', 'balance_after');
        });

        Schema::table('food_stock_movements', function (Blueprint $table) {
            $table->string('unit_name_snapshot')->nullable()->after('type');
            $table->string('unit_symbol_snapshot', 20)->nullable()->after('unit_name_snapshot');
            $table->unsignedBigInteger('unit_price')->nullable()->after('balance_after');
            $table->unsignedBigInteger('total_cost')->nullable()->after('unit_price');
        });

        DB::table('food_stock_movements')->update([
            'unit_name_snapshot' => 'Kilogram',
            'unit_symbol_snapshot' => 'kg',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('food_stock_movements', function (Blueprint $table) {
            $table->dropColumn([
                'unit_name_snapshot',
                'unit_symbol_snapshot',
                'unit_price',
                'total_cost',
            ]);
            $table->renameColumn('quantity', 'quantity_in_kg');
            $table->renameColumn('balance_before', 'balance_before_kg');
            $table->renameColumn('balance_after', 'balance_after_kg');
        });

        Schema::table('branch_food_stocks', function (Blueprint $table) {
            $table->renameColumn('quantity', 'quantity_in_kg');
            $table->renameColumn('red_threshold', 'red_threshold_kg');
            $table->renameColumn('yellow_threshold', 'yellow_threshold_kg');
        });

        Schema::table('dishes', function (Blueprint $table) {
            $table->renameColumn('ingredient_quantity', 'ingredient_quantity_kg');
        });

        Schema::table('foods', function (Blueprint $table) {
            $table->dropConstrainedForeignId('inventory_unit_id');
            $table->dropColumn('purchase_price');
        });
    }
};

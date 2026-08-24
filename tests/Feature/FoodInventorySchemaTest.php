<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('stores inventory balances per food and branch', function () {
    expect(Schema::hasColumns('branch_food_stocks', [
        'is_inventory_tracked',
        'quantity_in_kg',
        'last_restocked_at',
    ]))->toBeTrue();

    $branchId = DB::table('branches')->insertGetId([
        'name' => 'Chi nhánh A',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $foodId = DB::table('foods')->insertGetId([
        'category_id' => 1,
        'name' => 'Ốc hương',
        'price' => 100000,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $stockId = DB::table('branch_food_stocks')->insertGetId([
        'branch_id' => $branchId,
        'food_id' => $foodId,
        'is_inventory_tracked' => true,
        'quantity_in_kg' => 300,
        'last_restocked_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect((float) DB::table('branch_food_stocks')->where('id', $stockId)->value('quantity_in_kg'))
        ->toBe(300.0);
});

it('stores an auditable history of stock movements', function () {
    expect(Schema::hasColumns('food_stock_movements', [
        'branch_food_stock_id',
        'type',
        'quantity_in_kg',
        'balance_before_kg',
        'balance_after_kg',
        'bill_detail_id',
        'created_by_user_id',
        'note',
        'occurred_at',
    ]))->toBeTrue();

    $branchId = DB::table('branches')->insertGetId([
        'name' => 'Chi nhánh B',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $foodId = DB::table('foods')->insertGetId([
        'category_id' => 1,
        'name' => 'Ốc móng tay',
        'price' => 80000,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $stockId = DB::table('branch_food_stocks')->insertGetId([
        'branch_id' => $branchId,
        'food_id' => $foodId,
        'is_inventory_tracked' => true,
        'quantity_in_kg' => 250,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('food_stock_movements')->insert([
        'branch_food_stock_id' => $stockId,
        'type' => 'usage',
        'quantity_in_kg' => 50,
        'balance_before_kg' => 300,
        'balance_after_kg' => 250,
        'note' => 'Đã bán 100 phần, mỗi phần 0.5 kg.',
        'occurred_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $movement = DB::table('food_stock_movements')->first();

    expect($movement->type)->toBe('usage')
        ->and((float) $movement->quantity_in_kg)->toBe(50.0)
        ->and((float) $movement->balance_after_kg)->toBe(250.0);
});

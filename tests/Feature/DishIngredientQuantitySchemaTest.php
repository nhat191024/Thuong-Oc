<?php

use App\Models\Dish;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('stores the ingredient quantity in kilograms for each dish', function () {
    expect(Schema::hasColumn('dishes', 'ingredient_quantity_kg'))->toBeTrue();

    $dishId = DB::table('dishes')->insertGetId([
        'food_id' => 1,
        'cooking_method_id' => 1,
        'additional_price' => 0,
        'ingredient_quantity_kg' => 0.5,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $quantityInKg = DB::table('dishes')
        ->where('id', $dishId)
        ->value('ingredient_quantity_kg');

    expect((float) $quantityInKg)->toBe(0.5);
});

it('allows a dish ingredient quantity to be configured or left empty', function () {
    $configuredDish = Dish::create([
        'food_id' => 1,
        'cooking_method_id' => 1,
        'additional_price' => 0,
        'ingredient_quantity_kg' => 0.5,
    ]);

    $unconfiguredDish = Dish::create([
        'food_id' => 1,
        'cooking_method_id' => 2,
        'additional_price' => 0,
    ]);

    expect($configuredDish->ingredient_quantity_kg)->toBe('0.500')
        ->and($unconfiguredDish->ingredient_quantity_kg)->toBeNull();
});

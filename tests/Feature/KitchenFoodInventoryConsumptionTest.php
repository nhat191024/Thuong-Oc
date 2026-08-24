<?php

use App\Enums\BillDetailStatus;
use App\Enums\Role;
use App\Models\Bill;
use App\Models\BillDetail;
use App\Models\Branch;
use App\Models\BranchFoodStock;
use App\Models\Category;
use App\Models\CookingMethod;
use App\Models\Dish;
use App\Models\Food;
use App\Models\FoodStockMovement;
use App\Models\Table;
use App\Models\User;
use App\Services\KitchenPrintJobService;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role as PermissionRole;

function createKitchenInventoryUser(Branch $branch): User
{
    PermissionRole::firstOrCreate(['name' => Role::KITCHEN->value, 'guard_name' => 'web']);
    $user = User::create([
        'username' => fake()->unique()->userName(),
        'password' => Hash::make('password'),
        'branch_id' => $branch->id,
    ]);
    $user->assignRole(Role::KITCHEN->value);

    return $user;
}

/** @return array{food: Food, dish: Dish, billDetail: BillDetail} */
function createKitchenInventoryOrder(Branch $branch, ?float $quantityPerPortion, int $portions = 100): array
{
    $category = Category::create(['name' => fake()->unique()->word(), 'order' => 1]);
    $food = Food::create([
        'category_id' => $category->id,
        'name' => fake()->unique()->word(),
        'price' => 100000,
        'order' => 1,
    ]);
    $method = CookingMethod::create(['name' => fake()->unique()->word()]);
    $dish = Dish::create([
        'food_id' => $food->id,
        'cooking_method_id' => $method->id,
        'additional_price' => 0,
        'ingredient_quantity_kg' => $quantityPerPortion,
    ]);
    $table = Table::create([
        'name' => 'Bàn bếp',
        'table_number' => 1,
        'branch_id' => $branch->id,
    ]);
    $bill = Bill::create(['table_id' => $table->id, 'branch_id' => $branch->id]);
    $billDetail = BillDetail::create([
        'bill_id' => $bill->id,
        'dish_id' => $dish->id,
        'quantity' => $portions,
        'price' => 100000,
        'status' => BillDetailStatus::WAITING,
    ]);

    return compact('food', 'dish', 'billDetail');
}

it('deducts ingredient stock only when the kitchen completes the dish', function () {
    $branch = Branch::create(['name' => 'Chi nhánh A']);
    $user = createKitchenInventoryUser($branch);
    $order = createKitchenInventoryOrder($branch, 0.5, 100);
    $stock = BranchFoodStock::create([
        'branch_id' => $branch->id,
        'food_id' => $order['food']->id,
        'is_out_of_stock' => false,
        'is_inventory_tracked' => true,
        'quantity_in_kg' => 300,
    ]);

    $this->mock(KitchenPrintJobService::class)
        ->shouldReceive('request')
        ->once()
        ->andReturn([]);

    $this->actingAs($user)->post(route('kitchen.bill-detail.update-status', $order['billDetail']), [
        'status' => BillDetailStatus::DONE->value,
    ])->assertRedirect();

    $stock->refresh();

    expect($stock->quantity_in_kg)->toBe('250.000')
        ->and($stock->is_out_of_stock)->toBeFalse();

    $this->assertDatabaseHas('food_stock_movements', [
        'branch_food_stock_id' => $stock->id,
        'type' => FoodStockMovement::TYPE_USAGE,
        'quantity_in_kg' => 50,
        'balance_before_kg' => 300,
        'balance_after_kg' => 250,
        'bill_detail_id' => $order['billDetail']->id,
        'created_by_user_id' => $user->id,
    ]);
});

it('does not deduct the same completed bill detail twice', function () {
    $branch = Branch::create(['name' => 'Chi nhánh A']);
    $user = createKitchenInventoryUser($branch);
    $order = createKitchenInventoryOrder($branch, 0.5, 2);
    $stock = BranchFoodStock::create([
        'branch_id' => $branch->id,
        'food_id' => $order['food']->id,
        'is_inventory_tracked' => true,
        'quantity_in_kg' => 10,
    ]);

    $this->mock(KitchenPrintJobService::class)
        ->shouldReceive('request')
        ->once()
        ->andReturn([]);

    $payload = ['status' => BillDetailStatus::DONE->value];
    $this->actingAs($user)->post(route('kitchen.bill-detail.update-status', $order['billDetail']), $payload)->assertRedirect();
    $this->actingAs($user)->post(route('kitchen.bill-detail.update-status', $order['billDetail']), $payload)->assertRedirect();

    expect($stock->fresh()->quantity_in_kg)->toBe('9.000')
        ->and(FoodStockMovement::whereBillDetailId($order['billDetail']->id)->count())->toBe(1);
});

it('allows completion to take stock below zero without turning the food off', function () {
    $branch = Branch::create(['name' => 'Chi nhánh A']);
    $user = createKitchenInventoryUser($branch);
    $order = createKitchenInventoryOrder($branch, 0.5, 5);
    $this->mock(KitchenPrintJobService::class)->shouldReceive('request')->once()->andReturn([]);

    $this->actingAs($user)->post(route('kitchen.bill-detail.update-status', $order['billDetail']), [
        'status' => BillDetailStatus::DONE->value,
    ])->assertRedirect();

    $stock = BranchFoodStock::whereBranchId($branch->id)->whereFoodId($order['food']->id)->firstOrFail();

    expect($stock->quantity_in_kg)->toBe('-2.500')
        ->and($stock->is_out_of_stock)->toBeFalse();
});

it('does not change inventory when a dish has no kilogram configuration', function () {
    $branch = Branch::create(['name' => 'Chi nhánh A']);
    $user = createKitchenInventoryUser($branch);
    $order = createKitchenInventoryOrder($branch, null, 5);
    $this->mock(KitchenPrintJobService::class)->shouldReceive('request')->once()->andReturn([]);

    $this->actingAs($user)->post(route('kitchen.bill-detail.update-status', $order['billDetail']), [
        'status' => BillDetailStatus::DONE->value,
    ])->assertRedirect();

    expect(BranchFoodStock::whereFoodId($order['food']->id)->exists())->toBeFalse()
        ->and(FoodStockMovement::whereBillDetailId($order['billDetail']->id)->exists())->toBeFalse();
});

it('does not deduct inventory when the kitchen cancels instead of completing the dish', function () {
    $branch = Branch::create(['name' => 'Chi nhánh A']);
    $user = createKitchenInventoryUser($branch);
    $order = createKitchenInventoryOrder($branch, 0.5, 5);
    $stock = BranchFoodStock::create([
        'branch_id' => $branch->id,
        'food_id' => $order['food']->id,
        'is_inventory_tracked' => true,
        'quantity_in_kg' => 10,
    ]);

    $this->actingAs($user)->post(route('kitchen.bill-detail.update-status', $order['billDetail']), [
        'status' => BillDetailStatus::CANCELLED->value,
    ])->assertRedirect();

    expect($stock->fresh()->quantity_in_kg)->toBe('10.000')
        ->and(FoodStockMovement::whereBillDetailId($order['billDetail']->id)->exists())->toBeFalse();
});

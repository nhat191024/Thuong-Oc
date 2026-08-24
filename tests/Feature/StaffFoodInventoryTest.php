<?php

use App\Enums\Role;
use App\Models\Branch;
use App\Models\BranchFoodStock;
use App\Models\Category;
use App\Models\CookingMethod;
use App\Models\Dish;
use App\Models\Food;
use App\Models\FoodStockMovement;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role as PermissionRole;

function createInventoryStaff(Branch $branch): User
{
    PermissionRole::firstOrCreate(['name' => Role::STAFF->value, 'guard_name' => 'web']);
    $user = User::create([
        'username' => fake()->unique()->userName(),
        'password' => Hash::make('password'),
        'branch_id' => $branch->id,
    ]);
    $user->assignRole(Role::STAFF->value);

    return $user;
}

function createInventoryFood(string $name): Food
{
    $category = Category::create(['name' => "Danh mục {$name}", 'order' => 1]);
    $food = Food::create(['category_id' => $category->id, 'name' => $name, 'price' => 100000, 'order' => 1]);
    $method = CookingMethod::create(['name' => "Hấp {$name}"]);
    Dish::create(['food_id' => $food->id, 'cooking_method_id' => $method->id, 'additional_price' => 0, 'ingredient_quantity_kg' => 0.5]);

    return $food;
}

it('adds stock and records the movement for the staff branch', function () {
    $branch = Branch::create(['name' => 'Chi nhánh A']);
    $staff = createInventoryStaff($branch);
    $food = createInventoryFood('Ốc hương');

    $this->actingAs($staff)->post(route('staff.inventory.movements.store', $food), [
        'type' => 'restock',
        'quantity_in_kg' => 300,
        'note' => 'Nhập đầu ngày',
    ])->assertRedirect();

    $stock = BranchFoodStock::whereBranchId($branch->id)->whereFoodId($food->id)->firstOrFail();

    expect($stock->is_inventory_tracked)->toBeTrue()
        ->and($stock->quantity_in_kg)->toBe('300.000')
        ->and($stock->is_out_of_stock)->toBeFalse();

    $this->assertDatabaseHas('food_stock_movements', [
        'branch_food_stock_id' => $stock->id,
        'type' => FoodStockMovement::TYPE_RESTOCK,
        'quantity_in_kg' => 300,
        'balance_before_kg' => 0,
        'balance_after_kg' => 300,
        'created_by_user_id' => $staff->id,
    ]);
});

it('allows a negative actual balance without marking the food out of stock', function () {
    $branch = Branch::create(['name' => 'Chi nhánh A']);
    $staff = createInventoryStaff($branch);
    $food = createInventoryFood('Ốc móng tay');
    $stock = BranchFoodStock::create([
        'branch_id' => $branch->id,
        'food_id' => $food->id,
        'is_out_of_stock' => false,
        'is_inventory_tracked' => true,
        'quantity_in_kg' => 10,
    ]);

    $this->actingAs($staff)->post(route('staff.inventory.movements.store', $food), [
        'type' => 'adjustment',
        'quantity_in_kg' => -2.5,
        'note' => 'Chênh lệch khi kiểm kê',
    ])->assertRedirect();

    $stock->refresh();

    expect($stock->quantity_in_kg)->toBe('-2.500')
        ->and($stock->is_out_of_stock)->toBeFalse();

    $this->assertDatabaseHas('food_stock_movements', [
        'branch_food_stock_id' => $stock->id,
        'type' => FoodStockMovement::TYPE_ADJUSTMENT,
        'quantity_in_kg' => 12.5,
        'balance_before_kg' => 10,
        'balance_after_kg' => -2.5,
    ]);
});

it('shows current inventory and branch movement history in Vue pages', function () {
    $branch = Branch::create(['name' => 'Chi nhánh A']);
    $staff = createInventoryStaff($branch);
    $food = createInventoryFood('Ốc len');
    $stock = BranchFoodStock::create([
        'branch_id' => $branch->id,
        'food_id' => $food->id,
        'is_inventory_tracked' => true,
        'quantity_in_kg' => -1.25,
    ]);
    FoodStockMovement::create([
        'branch_food_stock_id' => $stock->id,
        'type' => FoodStockMovement::TYPE_ADJUSTMENT,
        'quantity_in_kg' => 1.25,
        'balance_before_kg' => 0,
        'balance_after_kg' => -1.25,
        'created_by_user_id' => $staff->id,
        'occurred_at' => now(),
    ]);

    $this->actingAs($staff)->get(route('staff.inventory.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('staff/inventory')
            ->where('foods.0.quantity_in_kg', -1.25)
            ->where('foods.0.is_out_of_stock', false));

    $this->actingAs($staff)->get(route('staff.inventory.movements.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('staff/inventoryHistory')
            ->where('movements.data.0.food_name', 'Ốc len')
            ->where('movements.data.0.balance_after_kg', -1.25));
});

it('keeps the sold-out page separate from kilogram inventory data', function () {
    $branch = Branch::create(['name' => 'Chi nhánh A']);
    $staff = createInventoryStaff($branch);
    $food = createInventoryFood('Ốc cà na');
    BranchFoodStock::create([
        'branch_id' => $branch->id,
        'food_id' => $food->id,
        'is_out_of_stock' => true,
        'is_inventory_tracked' => true,
        'quantity_in_kg' => 25,
    ]);

    $this->actingAs($staff)->get(route('staff.stock.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('staff/stock')
            ->where('foods.0.is_out_of_stock', true)
            ->missing('foods.0.quantity_in_kg'));
});

it('orders inventory foods by ascending id', function () {
    $branch = Branch::create(['name' => 'Chi nhánh A']);
    $staff = createInventoryStaff($branch);
    $firstFood = createInventoryFood('Ốc tạo trước');
    $secondFood = createInventoryFood('Ốc tạo sau');

    $firstFood->update(['order' => 99]);
    $secondFood->update(['order' => 1]);

    $this->actingAs($staff)->get(route('staff.inventory.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('foods.0.id', $firstFood->id)
            ->where('foods.1.id', $secondFood->id));
});

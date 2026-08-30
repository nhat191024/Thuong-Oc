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

it('updates inventory thresholds only for the staff branch', function () {
    $staffBranch = Branch::create(['name' => 'Chi nhánh nhân viên']);
    $otherBranch = Branch::create(['name' => 'Chi nhánh khác']);
    $staff = createInventoryStaff($staffBranch);
    $food = createInventoryFood('Ốc ngưỡng riêng');
    $staffStock = BranchFoodStock::create([
        'branch_id' => $staffBranch->id,
        'food_id' => $food->id,
        'is_inventory_tracked' => true,
        'quantity_in_kg' => 15,
    ]);
    $otherStock = BranchFoodStock::create([
        'branch_id' => $otherBranch->id,
        'food_id' => $food->id,
        'is_inventory_tracked' => true,
        'quantity_in_kg' => 15,
    ]);

    $this->actingAs($staff)->patch(route('staff.inventory.thresholds.update', $food), [
        'red_threshold_kg' => 12.5,
        'yellow_threshold_kg' => 25,
    ])->assertRedirect();

    expect($staffStock->fresh()->red_threshold_kg)->toBe('12.500')
        ->and($staffStock->fresh()->yellow_threshold_kg)->toBe('25.000')
        ->and($otherStock->fresh()->red_threshold_kg)->toBe('10.000')
        ->and($otherStock->fresh()->yellow_threshold_kg)->toBe('20.000')
        ->and(FoodStockMovement::query()->count())->toBe(0);
});

it('validates inventory threshold values', function (array $thresholds, array $errors) {
    $branch = Branch::create(['name' => 'Chi nhánh validation']);
    $staff = createInventoryStaff($branch);
    $food = createInventoryFood('Ốc validation '.fake()->unique()->word());

    $this->actingAs($staff)
        ->from(route('staff.inventory.index'))
        ->patch(route('staff.inventory.thresholds.update', $food), $thresholds)
        ->assertRedirect(route('staff.inventory.index'))
        ->assertSessionHasErrors($errors);
})->with([
    'negative red threshold' => [
        ['red_threshold_kg' => -1, 'yellow_threshold_kg' => 20],
        ['red_threshold_kg'],
    ],
    'equal thresholds' => [
        ['red_threshold_kg' => 10, 'yellow_threshold_kg' => 10],
        ['yellow_threshold_kg'],
    ],
    'yellow below red' => [
        ['red_threshold_kg' => 20, 'yellow_threshold_kg' => 10],
        ['yellow_threshold_kg'],
    ],
]);

it('returns per-food thresholds and counts only tracked red inventory for the staff branch', function () {
    $branch = Branch::create(['name' => 'Chi nhánh cảnh báo']);
    $otherBranch = Branch::create(['name' => 'Chi nhánh không cảnh báo']);
    $staff = createInventoryStaff($branch);
    $redFood = createInventoryFood('Ốc đỏ');
    $yellowFood = createInventoryFood('Ốc vàng');
    $greenFood = createInventoryFood('Ốc xanh');
    $untrackedFood = createInventoryFood('Ốc chưa theo dõi');

    foreach ([
        [$redFood, true, 9.999],
        [$yellowFood, true, 10],
        [$greenFood, true, 20],
        [$untrackedFood, false, -5],
    ] as [$food, $tracked, $quantity]) {
        BranchFoodStock::create([
            'branch_id' => $branch->id,
            'food_id' => $food->id,
            'is_inventory_tracked' => $tracked,
            'quantity_in_kg' => $quantity,
            'red_threshold_kg' => 10,
            'yellow_threshold_kg' => 20,
        ]);
    }

    BranchFoodStock::create([
        'branch_id' => $otherBranch->id,
        'food_id' => $greenFood->id,
        'is_inventory_tracked' => true,
        'quantity_in_kg' => -10,
    ]);

    $this->actingAs($staff)->get(route('staff.tables'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('staff/index')
            ->where('redInventoryCount', 1));

    $this->actingAs($staff)->get(route('staff.inventory.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('staff/inventory')
            ->where('foods.0.red_threshold_kg', 10)
            ->where('foods.0.yellow_threshold_kg', 20));
});

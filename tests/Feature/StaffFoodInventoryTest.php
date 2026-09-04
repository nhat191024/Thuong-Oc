<?php

use App\Enums\Role;
use App\Models\Branch;
use App\Models\BranchFoodStock;
use App\Models\Category;
use App\Models\CookingMethod;
use App\Models\Dish;
use App\Models\Food;
use App\Models\FoodStockMovement;
use App\Models\InventoryUnit;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;
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
    $unit = InventoryUnit::where('symbol', 'kg')->firstOrFail();
    $food = Food::create([
        'category_id' => $category->id,
        'inventory_unit_id' => $unit->id,
        'name' => $name,
        'price' => 100000,
        'purchase_price' => 50000,
        'order' => 1,
    ]);
    $method = CookingMethod::create(['name' => "Hấp {$name}"]);
    Dish::create(['food_id' => $food->id, 'cooking_method_id' => $method->id, 'additional_price' => 0, 'ingredient_quantity' => 0.5]);

    return $food;
}

it('adds stock and records the movement for the staff branch', function () {
    $branch = Branch::create(['name' => 'Chi nhánh A']);
    $staff = createInventoryStaff($branch);
    $food = createInventoryFood('Ốc hương');

    $this->actingAs($staff)->post(route('staff.inventory.movements.store', $food), [
        'type' => 'restock',
        'quantity' => 300,
        'note' => 'Nhập đầu ngày',
    ])->assertRedirect();

    $stock = BranchFoodStock::whereBranchId($branch->id)->whereFoodId($food->id)->firstOrFail();

    expect($stock->is_inventory_tracked)->toBeTrue()
        ->and($stock->quantity)->toBe('300.000')
        ->and($stock->is_out_of_stock)->toBeFalse();

    $this->assertDatabaseHas('food_stock_movements', [
        'branch_food_stock_id' => $stock->id,
        'type' => FoodStockMovement::TYPE_RESTOCK,
        'quantity' => 300,
        'balance_before' => 0,
        'balance_after' => 300,
        'created_by_user_id' => $staff->id,
        'unit_name_snapshot' => 'Kilogram',
        'unit_symbol_snapshot' => 'kg',
        'unit_price' => 50000,
        'total_cost' => 15000000,
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
        'quantity' => 10,
    ]);

    $this->actingAs($staff)->post(route('staff.inventory.movements.store', $food), [
        'type' => 'adjustment',
        'quantity' => -2.5,
        'note' => 'Chênh lệch khi kiểm kê',
    ])->assertRedirect();

    $stock->refresh();

    expect($stock->quantity)->toBe('-2.500')
        ->and($stock->is_out_of_stock)->toBeFalse();

    $this->assertDatabaseHas('food_stock_movements', [
        'branch_food_stock_id' => $stock->id,
        'type' => FoodStockMovement::TYPE_ADJUSTMENT,
        'quantity' => 12.5,
        'balance_before' => 10,
        'balance_after' => -2.5,
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
        'quantity' => -1.25,
    ]);
    FoodStockMovement::create([
        'branch_food_stock_id' => $stock->id,
        'type' => FoodStockMovement::TYPE_ADJUSTMENT,
        'quantity' => 1.25,
        'balance_before' => 0,
        'balance_after' => -1.25,
        'created_by_user_id' => $staff->id,
        'occurred_at' => now(),
    ]);

    $this->actingAs($staff)->get(route('staff.inventory.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('staff/inventory')
            ->where('foods.0.quantity', -1.25)
            ->where('foods.0.unit_symbol', 'kg')
            ->where('foods.0.is_out_of_stock', false));

    $this->actingAs($staff)->get(route('staff.inventory.movements.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('staff/inventoryHistory')
            ->where('movements.data.0.food_name', 'Ốc len')
            ->where('movements.data.0.balance_after', -1.25));
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
        'quantity' => 25,
    ]);

    $this->actingAs($staff)->get(route('staff.stock.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('staff/stock')
            ->where('foods.0.is_out_of_stock', true)
            ->missing('foods.0.quantity'));
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
        'quantity' => 15,
    ]);
    $otherStock = BranchFoodStock::create([
        'branch_id' => $otherBranch->id,
        'food_id' => $food->id,
        'is_inventory_tracked' => true,
        'quantity' => 15,
    ]);

    $this->actingAs($staff)->patch(route('staff.inventory.thresholds.update', $food), [
        'red_threshold' => 12.5,
        'yellow_threshold' => 25,
    ])->assertRedirect();

    expect($staffStock->fresh()->red_threshold)->toBe('12.500')
        ->and($staffStock->fresh()->yellow_threshold)->toBe('25.000')
        ->and($otherStock->fresh()->red_threshold)->toBe('10.000')
        ->and($otherStock->fresh()->yellow_threshold)->toBe('20.000')
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
        ['red_threshold' => -1, 'yellow_threshold' => 20],
        ['red_threshold'],
    ],
    'equal thresholds' => [
        ['red_threshold' => 10, 'yellow_threshold' => 10],
        ['yellow_threshold'],
    ],
    'yellow below red' => [
        ['red_threshold' => 20, 'yellow_threshold' => 10],
        ['yellow_threshold'],
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
            'quantity' => $quantity,
            'red_threshold' => 10,
            'yellow_threshold' => 20,
        ]);
    }

    BranchFoodStock::create([
        'branch_id' => $otherBranch->id,
        'food_id' => $greenFood->id,
        'is_inventory_tracked' => true,
        'quantity' => -10,
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
            ->where('foods.0.red_threshold', 10)
            ->where('foods.0.yellow_threshold', 20));
});

it('blocks restocking when the food purchase configuration is missing', function () {
    $branch = Branch::create(['name' => 'Chi nhánh thiếu cấu hình']);
    $staff = createInventoryStaff($branch);
    $food = createInventoryFood('Ốc thiếu giá');
    $food->update(['purchase_price' => null]);

    $this->actingAs($staff)
        ->from(route('staff.inventory.index'))
        ->post(route('staff.inventory.movements.store', $food), [
            'type' => 'restock',
            'quantity' => 5,
        ])
        ->assertRedirect(route('staff.inventory.index'))
        ->assertSessionHasErrors(['quantity']);

    expect(BranchFoodStock::whereFoodId($food->id)->exists())->toBeFalse();
});

it('accepts a zero purchase price and keeps immutable restock snapshots', function () {
    $branch = Branch::create(['name' => 'Chi nhánh snapshot']);
    $staff = createInventoryStaff($branch);
    $food = createInventoryFood('Ốc hàng tặng');
    $unit = $food->inventoryUnit()->firstOrFail();
    $food->update(['purchase_price' => 0]);

    $this->actingAs($staff)->post(route('staff.inventory.movements.store', $food), [
        'type' => 'restock',
        'quantity' => 2.5,
        'unit_price' => 999999,
        'total_cost' => 999999,
    ])->assertRedirect();

    $movement = FoodStockMovement::whereType(FoodStockMovement::TYPE_RESTOCK)->firstOrFail();

    expect($movement->unit_name_snapshot)->toBe('Kilogram')
        ->and($movement->unit_symbol_snapshot)->toBe('kg')
        ->and($movement->unit_price)->toBe(0)
        ->and($movement->total_cost)->toBe(0);

    $unit->update(['name' => 'Kilogram mới', 'symbol' => 'kg-new']);
    $food->update(['purchase_price' => 70000]);
    $movement->refresh();

    expect($movement->unit_name_snapshot)->toBe('Kilogram')
        ->and($movement->unit_symbol_snapshot)->toBe('kg')
        ->and($movement->unit_price)->toBe(0)
        ->and($movement->total_cost)->toBe(0);
});

it('exports all filtered stock history for only the staff branch', function () {
    $staffBranch = Branch::create(['name' => 'Chi nhánh Xuất Excel']);
    $otherBranch = Branch::create(['name' => 'Chi nhánh khác']);
    $staff = createInventoryStaff($staffBranch);
    $matchingFood = createInventoryFood('Ốc hương xuất file');
    $otherFood = createInventoryFood('Ốc len');

    $matchingStock = BranchFoodStock::create([
        'branch_id' => $staffBranch->id,
        'food_id' => $matchingFood->id,
        'is_inventory_tracked' => true,
        'quantity' => 75,
    ]);
    $otherFoodStock = BranchFoodStock::create([
        'branch_id' => $staffBranch->id,
        'food_id' => $otherFood->id,
        'is_inventory_tracked' => true,
        'quantity' => 10,
    ]);
    $otherBranchStock = BranchFoodStock::create([
        'branch_id' => $otherBranch->id,
        'food_id' => $matchingFood->id,
        'is_inventory_tracked' => true,
        'quantity' => 100,
    ]);

    foreach (range(1, 51) as $index) {
        FoodStockMovement::create([
            'branch_food_stock_id' => $matchingStock->id,
            'type' => FoodStockMovement::TYPE_RESTOCK,
            'unit_name_snapshot' => 'Kilogram',
            'unit_symbol_snapshot' => 'kg',
            'quantity' => 1.5,
            'balance_before' => $index,
            'balance_after' => $index + 1.5,
            'unit_price' => 50000,
            'total_cost' => 75000,
            'created_by_user_id' => $staff->id,
            'note' => "Lần nhập {$index}",
            'occurred_at' => now()->addSeconds($index),
        ]);
    }

    foreach ([
        [$otherFoodStock, FoodStockMovement::TYPE_RESTOCK],
        [$matchingStock, FoodStockMovement::TYPE_USAGE],
        [$otherBranchStock, FoodStockMovement::TYPE_RESTOCK],
    ] as [$stock, $type]) {
        FoodStockMovement::create([
            'branch_food_stock_id' => $stock->id,
            'type' => $type,
            'quantity' => 1,
            'balance_before' => 1,
            'balance_after' => $type === FoodStockMovement::TYPE_USAGE ? 0 : 2,
            'occurred_at' => now(),
        ]);
    }

    $response = $this->actingAs($staff)->get(route('staff.inventory.movements.export', [
        'search' => 'hương',
        'type' => FoodStockMovement::TYPE_RESTOCK,
    ]));

    $response->assertOk()
        ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    $spreadsheet = IOFactory::load($response->baseResponse->getFile()->getPathname());
    $sheet = $spreadsheet->getActiveSheet();

    expect($sheet->getHighestDataRow())->toBe(52)
        ->and($sheet->getCell('A1')->getValue())->toBe('Thời gian')
        ->and($sheet->getCell('B2')->getValue())->toBe('Ốc hương xuất file')
        ->and($sheet->getCell('C2')->getValue())->toBe('Nhập kho')
        ->and($sheet->getCell('D2')->getValue())->toBe(1.5)
        ->and($sheet->getCell('E2')->getValue())->toBe('Kilogram (kg)')
        ->and($sheet->getCell('H2')->getValue())->toBe(50000)
        ->and($sheet->getCell('I2')->getValue())->toBe(75000);
});

it('validates stock history export filters', function () {
    $branch = Branch::create(['name' => 'Chi nhánh lọc Excel']);
    $staff = createInventoryStaff($branch);

    $this->actingAs($staff)
        ->getJson(route('staff.inventory.movements.export', ['type' => 'khong-hop-le']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('type');
});

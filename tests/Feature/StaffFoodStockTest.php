<?php

use App\Enums\Role;
use App\Models\Branch;
use App\Models\BranchFoodStock;
use App\Models\Category;
use App\Models\CookingMethod;
use App\Models\Dish;
use App\Models\Food;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role as PermissionRole;

function createStaffUserForBranch(Branch $branch): User
{
    PermissionRole::firstOrCreate([
        'name' => Role::STAFF->value,
        'guard_name' => 'web',
    ]);

    $user = User::factory()->create([
        'username' => fake()->unique()->userName(),
        'branch_id' => $branch->id,
    ]);

    $user->assignRole(Role::STAFF->value);

    return $user;
}

function createFoodWithDish(string $name, int $price = 100000): Food
{
    $category = Category::create([
        'name' => 'Món chính',
        'order' => 1,
    ]);

    $food = Food::create([
        'category_id' => $category->id,
        'name' => $name,
        'price' => $price,
        'order' => 1,
    ]);

    $cookingMethod = CookingMethod::create([
        'name' => 'Mặc định',
    ]);

    Dish::create([
        'food_id' => $food->id,
        'cooking_method_id' => $cookingMethod->id,
        'additional_price' => 0,
    ]);

    return $food;
}

it('allows staff to view stock for their branch', function () {
    $branch = Branch::create(['name' => 'Chi nhánh A']);
    $user = createStaffUserForBranch($branch);
    $food = createFoodWithDish('Bún bò', 75000);

    BranchFoodStock::create([
        'branch_id' => $branch->id,
        'food_id' => $food->id,
        'is_out_of_stock' => true,
    ]);

    $this->actingAs($user)
        ->get(route('staff.stock.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('staff/stock')
            ->where('branchName', 'Chi nhánh A')
            ->has('foods', 1)
            ->where('foods.0.name', 'Bún bò')
            ->where('foods.0.category_name', 'Món chính')
            ->where('foods.0.price', 75000)
            ->where('foods.0.is_out_of_stock', true));
});

it('updates stock only for the authenticated staff branch', function () {
    $staffBranch = Branch::create(['name' => 'Chi nhánh A']);
    $otherBranch = Branch::create(['name' => 'Chi nhánh B']);
    $user = createStaffUserForBranch($staffBranch);
    $food = createFoodWithDish('Ốc hương');

    $this->actingAs($user)
        ->patch(route('staff.stock.update', $food), [
            'branch_id' => $otherBranch->id,
            'is_out_of_stock' => true,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('branch_food_stocks', [
        'branch_id' => $staffBranch->id,
        'food_id' => $food->id,
        'is_out_of_stock' => true,
    ]);

    $this->assertDatabaseMissing('branch_food_stocks', [
        'branch_id' => $otherBranch->id,
        'food_id' => $food->id,
    ]);
});

it('can mark an out of stock food as available again', function () {
    $branch = Branch::create(['name' => 'Chi nhánh A']);
    $user = createStaffUserForBranch($branch);
    $food = createFoodWithDish('Sò điệp');

    BranchFoodStock::create([
        'branch_id' => $branch->id,
        'food_id' => $food->id,
        'is_out_of_stock' => true,
    ]);

    $this->actingAs($user)
        ->patch(route('staff.stock.update', $food), [
            'is_out_of_stock' => false,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('branch_food_stocks', [
        'branch_id' => $branch->id,
        'food_id' => $food->id,
        'is_out_of_stock' => false,
    ]);
});

it('returns not found when updating missing food', function () {
    $branch = Branch::create(['name' => 'Chi nhánh A']);
    $user = createStaffUserForBranch($branch);

    $this->actingAs($user)
        ->patch(route('staff.stock.update', 999999), [
            'is_out_of_stock' => true,
        ])
        ->assertNotFound();
});

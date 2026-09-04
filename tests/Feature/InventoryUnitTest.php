<?php

use App\Enums\Role;
use App\Filament\Resources\InventoryUnits\Pages\CreateInventoryUnit;
use App\Filament\Resources\InventoryUnits\Pages\EditInventoryUnit;
use App\Models\Category;
use App\Models\Food;
use App\Models\InventoryUnit;
use App\Models\User;
use App\Services\InventoryUnitService;
use Filament\Actions\DeleteAction;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Role as PermissionRole;

function createInventoryUnitAdmin(): User
{
    PermissionRole::firstOrCreate(['name' => Role::ADMIN->value, 'guard_name' => 'web']);
    $admin = User::create([
        'username' => 'inventory-unit-admin',
        'password' => Hash::make('password'),
    ]);
    $admin->assignRole(Role::ADMIN->value);

    return $admin;
}

it('creates the kilogram unit and stores unit data', function () {
    expect(Schema::hasColumns('inventory_units', ['name', 'symbol']))->toBeTrue()
        ->and(InventoryUnit::where('name', 'Kilogram')->where('symbol', 'kg')->exists())->toBeTrue();
});

it('caches units forever and invalidates the cache when units change', function () {
    Cache::forget(InventoryUnitService::CACHE_KEY);

    expect(InventoryUnitService::options())->not->toBeEmpty()
        ->and(Cache::has(InventoryUnitService::CACHE_KEY))->toBeTrue();

    $unit = InventoryUnit::create(['name' => 'Thùng', 'symbol' => 'thùng']);

    expect(Cache::has(InventoryUnitService::CACHE_KEY))->toBeFalse()
        ->and(InventoryUnitService::options())->toHaveKey($unit->id)
        ->and(Cache::has(InventoryUnitService::CACHE_KEY))->toBeTrue();

    $unit->update(['symbol' => 'th']);
    expect(Cache::has(InventoryUnitService::CACHE_KEY))->toBeFalse();

    InventoryUnitService::options();
    expect(Cache::has(InventoryUnitService::CACHE_KEY))->toBeTrue();

    $unit->delete();
    expect(Cache::has(InventoryUnitService::CACHE_KEY))->toBeFalse();
});

it('requires unique unit names and symbols', function () {
    InventoryUnit::create(['name' => 'Bao', 'symbol' => 'bao']);

    expect(fn () => InventoryUnit::create(['name' => 'Bao', 'symbol' => 'b']))
        ->toThrow(QueryException::class)
        ->and(fn () => InventoryUnit::create(['name' => 'Bao khác', 'symbol' => 'bao']))
        ->toThrow(QueryException::class);
});

it('prevents deleting a unit used by a food', function () {
    $unit = InventoryUnit::create(['name' => 'Con', 'symbol' => 'con']);
    $category = Category::create(['name' => 'Hải sản', 'order' => 1]);
    Food::create([
        'category_id' => $category->id,
        'inventory_unit_id' => $unit->id,
        'name' => 'Cua',
        'price' => 100000,
        'purchase_price' => 50000,
        'order' => 1,
    ]);

    expect(fn () => $unit->delete())->toThrow(QueryException::class);
});

it('manages inventory units through filament and prevents deleting used units', function () {
    $this->actingAs(createInventoryUnitAdmin());

    Livewire::test(CreateInventoryUnit::class)
        ->fillForm(['name' => 'Sọt', 'symbol' => 'sọt'])
        ->call('create')
        ->assertHasNoFormErrors();

    $unit = InventoryUnit::where('symbol', 'sọt')->firstOrFail();

    Livewire::test(EditInventoryUnit::class, ['record' => $unit->id])
        ->fillForm(['name' => 'Sọt lớn', 'symbol' => 'sọt'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($unit->fresh()->name)->toBe('Sọt lớn');

    $category = Category::create(['name' => 'Món theo sọt', 'order' => 1]);
    Food::create([
        'category_id' => $category->id,
        'inventory_unit_id' => $unit->id,
        'name' => 'Ốc theo sọt',
        'price' => 100000,
        'purchase_price' => 40000,
        'order' => 1,
    ]);

    Livewire::test(EditInventoryUnit::class, ['record' => $unit->id])
        ->assertActionDisabled(DeleteAction::class);
});

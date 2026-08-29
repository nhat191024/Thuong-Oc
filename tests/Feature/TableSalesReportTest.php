<?php

use App\Enums\Role;
use App\Filament\Pages\TableSalesReport;
use App\Models\Branch;
use App\Models\TableSalesSummary;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role as PermissionRole;

it('shows the table sales report to an administrator with default filters', function () {
    Carbon::setTestNow('2026-08-29 10:00:00');
    $firstBranch = Branch::create(['name' => 'Cơ sở đầu tiên']);
    $secondBranch = Branch::create(['name' => 'Cơ sở thứ hai']);
    PermissionRole::firstOrCreate(['name' => Role::ADMIN->value, 'guard_name' => 'web']);
    $admin = User::create([
        'username' => 'report-admin',
        'password' => Hash::make('password'),
        'branch_id' => $firstBranch->id,
    ]);
    $admin->assignRole(Role::ADMIN->value);

    $visibleSummary = TableSalesSummary::create([
        'summary_date' => '2026-08-28',
        'branch_id' => $firstBranch->id,
        'table_id' => fake()->uuid(),
        'table_number' => 1,
        'table_name' => 'Sân trước',
        'bill_count' => 2,
        'gross_revenue' => 300000,
        'discount_amount' => 60000,
        'net_revenue' => 240000,
        'last_paid_at' => '2026-08-28 21:15:00',
        'calculated_at' => now(),
    ]);
    $hiddenSummary = TableSalesSummary::create([
        'summary_date' => '2026-08-28',
        'branch_id' => $secondBranch->id,
        'table_id' => fake()->uuid(),
        'table_number' => 2,
        'table_name' => 'Tầng hai',
        'bill_count' => 1,
        'gross_revenue' => 100000,
        'discount_amount' => 0,
        'net_revenue' => 100000,
        'last_paid_at' => '2026-08-28 20:00:00',
        'calculated_at' => now(),
    ]);

    $this->actingAs($admin);

    Livewire::test(TableSalesReport::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$visibleSummary])
        ->assertCanNotSeeTableRecords([$hiddenSummary])
        ->assertSee('Sân trước')
        ->assertSee('240.000');
});

<?php

use App\Enums\PayStatus;
use App\Models\Bill;
use App\Models\Branch;
use App\Models\Table as DiningTable;
use App\Models\TableSalesSummary;
use Illuminate\Support\Carbon;

function createSalesTable(Branch $branch, int $number, ?string $name = null): DiningTable
{
    return DiningTable::create([
        'branch_id' => $branch->id,
        'table_number' => $number,
        'name' => $name,
    ]);
}

function createSalesBill(DiningTable $table, array $attributes = []): Bill
{
    return Bill::create(array_merge([
        'table_id' => $table->id,
        'branch_id' => $table->branch_id,
        'time_in' => '2026-08-28 18:00:00',
        'time_out' => '2026-08-28 19:00:00',
        'total' => 100000,
        'final_total' => 90000,
        'pay_status' => PayStatus::PAID->value,
    ], $attributes));
}

it('aggregates paid bills by branch and table', function () {
    $firstBranch = Branch::create(['name' => 'Cơ sở 1']);
    $secondBranch = Branch::create(['name' => 'Cơ sở 2']);
    $firstTable = createSalesTable($firstBranch, 1, 'Sân trước');
    $secondTable = createSalesTable($secondBranch, 1, 'Tầng hai');

    createSalesBill($firstTable);
    createSalesBill($firstTable, [
        'time_out' => '2026-08-28 21:15:00',
        'total' => 200000,
        'final_total' => 150000,
    ]);
    createSalesBill($secondTable, ['total' => 80000, 'final_total' => 80000]);

    $this->artisan('reports:refresh-table-sales', ['--date' => '2026-08-28'])
        ->expectsOutput('Refreshed 2 table sales summaries for 2026-08-28.')
        ->assertSuccessful();

    $firstSummary = TableSalesSummary::query()->where('table_id', $firstTable->id)->firstOrFail();

    expect($firstSummary)
        ->bill_count->toBe(2)
        ->gross_revenue->toBe(300000)
        ->discount_amount->toBe(60000)
        ->net_revenue->toBe(240000)
        ->table_number->toBe(1)
        ->table_name->toBe('Sân trước')
        ->and($firstSummary->last_paid_at?->format('Y-m-d H:i:s'))->toBe('2026-08-28 21:15:00');
});

it('excludes ineligible bills and never records a negative discount', function () {
    $branch = Branch::create(['name' => 'Cơ sở']);
    $table = createSalesTable($branch, 1);

    createSalesBill($table, ['total' => 90000, 'final_total' => 100000]);
    createSalesBill($table, ['pay_status' => PayStatus::UNPAID->value]);
    createSalesBill($table, ['pay_status' => PayStatus::CANCELLED->value]);
    createSalesBill($table, ['time_out' => '2026-08-27 23:59:59']);
    createSalesBill($table)->delete();

    $this->artisan('reports:refresh-table-sales', ['--date' => '2026-08-28'])->assertSuccessful();

    $summary = TableSalesSummary::query()->sole();

    expect($summary)
        ->bill_count->toBe(1)
        ->gross_revenue->toBe(90000)
        ->discount_amount->toBe(0)
        ->net_revenue->toBe(100000);
});

it('replaces the selected date without duplicating summaries', function () {
    $branch = Branch::create(['name' => 'Cơ sở']);
    $table = createSalesTable($branch, 1);
    $bill = createSalesBill($table);

    $this->artisan('reports:refresh-table-sales', ['--date' => '2026-08-28'])->assertSuccessful();

    $bill->update(['total' => 120000, 'final_total' => 110000]);
    $this->artisan('reports:refresh-table-sales', ['--date' => '2026-08-28'])->assertSuccessful();

    expect(TableSalesSummary::query()->count())->toBe(1)
        ->and(TableSalesSummary::query()->sole()->net_revenue)->toBe(110000);
});

it('rejects an invalid date without changing summaries', function () {
    $branch = Branch::create(['name' => 'Cơ sở']);
    $table = createSalesTable($branch, 1);
    createTableSummary($branch, $table, '2026-08-28');

    $this->artisan('reports:refresh-table-sales', ['--date' => 'not-a-date'])
        ->expectsOutput('The --date option must be a valid date.')
        ->assertFailed();

    expect(TableSalesSummary::query()->count())->toBe(1);
});

it('keeps table snapshots after the source table changes or is deleted', function () {
    $branch = Branch::create(['name' => 'Cơ sở']);
    $table = createSalesTable($branch, 7, 'Phòng riêng');
    createSalesBill($table);

    $this->artisan('reports:refresh-table-sales', ['--date' => '2026-08-28'])->assertSuccessful();

    $table->update(['table_number' => 8, 'name' => 'Tên mới']);
    $table->delete();

    $summary = TableSalesSummary::query()->sole();

    expect($summary->table_number)->toBe(7)
        ->and($summary->table_name)->toBe('Phòng riêng');
});

it('cleans summaries older than fourteen days', function () {
    Carbon::setTestNow('2026-08-29 01:45:00');
    $branch = Branch::create(['name' => 'Cơ sở']);
    $table = createSalesTable($branch, 1);

    foreach (['2026-08-14', '2026-08-15', '2026-08-28'] as $date) {
        createTableSummary($branch, $table, $date);
    }

    $this->artisan('reports:cleanup-table-sales')
        ->expectsOutput('Deleted 1 table sales summaries older than 2026-08-15.')
        ->assertSuccessful();

    expect(TableSalesSummary::query()->pluck('summary_date')->map->toDateString()->all())
        ->toBe(['2026-08-15', '2026-08-28']);
});

function createTableSummary(Branch $branch, DiningTable $table, string $date): TableSalesSummary
{
    return TableSalesSummary::create([
        'summary_date' => $date,
        'branch_id' => $branch->id,
        'table_id' => $table->id,
        'table_number' => $table->table_number,
        'bill_count' => 1,
        'gross_revenue' => 100000,
        'discount_amount' => 0,
        'net_revenue' => 100000,
        'calculated_at' => now(),
    ]);
}

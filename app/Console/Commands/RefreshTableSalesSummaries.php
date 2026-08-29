<?php

namespace App\Console\Commands;

use App\Enums\PayStatus;
use App\Models\Bill;
use App\Models\TableSalesSummary;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

class RefreshTableSalesSummaries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:refresh-table-sales {--date= : The sales date to refresh. Defaults to yesterday.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh aggregated table sales summaries.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $summaryDate = $this->summaryDate();

        if (! $summaryDate) {
            return self::FAILURE;
        }

        $startedAt = microtime(true);
        $date = $summaryDate->toDateString();

        Log::info('Table sales summary refresh started.', ['summary_date' => $date]);

        try {
            $summaryCount = $this->refreshSummaries($summaryDate);

            Log::info('Table sales summary refresh completed.', [
                'summary_date' => $date,
                'summary_count' => $summaryCount,
                'branch_count' => TableSalesSummary::query()
                    ->whereDate('summary_date', $summaryDate)
                    ->distinct()
                    ->count('branch_id'),
                'duration_seconds' => round(microtime(true) - $startedAt, 2),
            ]);

            $this->info("Refreshed {$summaryCount} table sales summaries for {$date}.");

            return self::SUCCESS;
        } catch (Throwable $exception) {
            Log::error('Table sales summary refresh failed.', [
                'summary_date' => $date,
                'duration_seconds' => round(microtime(true) - $startedAt, 2),
                'exception' => $exception,
            ]);

            $this->error("Failed to refresh table sales summaries for {$date}: {$exception->getMessage()}");

            return self::FAILURE;
        }
    }

    private function refreshSummaries(Carbon $summaryDate): int
    {
        $calculatedAt = now();
        $summaries = Bill::query()
            ->join('tables', 'tables.id', '=', 'bills.table_id')
            ->where('bills.pay_status', PayStatus::PAID->value)
            ->whereBetween('bills.time_out', [
                $summaryDate->copy()->startOfDay(),
                $summaryDate->copy()->endOfDay(),
            ])
            ->groupBy('bills.branch_id', 'bills.table_id', 'tables.table_number', 'tables.name')
            ->select([
                'bills.branch_id',
                'bills.table_id',
                'tables.table_number',
                'tables.name as table_name',
            ])
            ->selectRaw('COUNT(bills.id) as bill_count')
            ->selectRaw('SUM(bills.total) as gross_revenue')
            ->selectRaw('SUM(CASE WHEN bills.total > bills.final_total THEN bills.total - bills.final_total ELSE 0 END) as discount_amount')
            ->selectRaw('SUM(bills.final_total) as net_revenue')
            ->selectRaw('MAX(bills.time_out) as last_paid_at')
            ->get();

        TableSalesSummary::query()->getConnection()->transaction(function () use ($summaries, $summaryDate, $calculatedAt): void {
            TableSalesSummary::query()->whereDate('summary_date', $summaryDate)->delete();

            $rows = $summaries->map(fn ($summary): array => [
                'summary_date' => $summaryDate->toDateString(),
                'branch_id' => $summary->branch_id,
                'table_id' => $summary->table_id,
                'table_number' => (int) $summary->table_number,
                'table_name' => $summary->table_name,
                'bill_count' => (int) $summary->bill_count,
                'gross_revenue' => (int) $summary->gross_revenue,
                'discount_amount' => (int) $summary->discount_amount,
                'net_revenue' => (int) $summary->net_revenue,
                'last_paid_at' => $summary->last_paid_at,
                'calculated_at' => $calculatedAt,
                'created_at' => $calculatedAt,
                'updated_at' => $calculatedAt,
            ]);

            $rows->chunk(500)->each(fn ($chunk) => TableSalesSummary::query()->upsert(
                $chunk->all(),
                ['summary_date', 'branch_id', 'table_id'],
                [
                    'table_number',
                    'table_name',
                    'bill_count',
                    'gross_revenue',
                    'discount_amount',
                    'net_revenue',
                    'last_paid_at',
                    'calculated_at',
                    'updated_at',
                ],
            ));
        });

        return $summaries->count();
    }

    private function summaryDate(): ?Carbon
    {
        $dateOption = $this->option('date');

        if ($dateOption === null || $dateOption === '') {
            return Carbon::yesterday()->startOfDay();
        }

        try {
            return Carbon::parse($dateOption)->startOfDay();
        } catch (Throwable $exception) {
            Log::warning('Invalid date supplied for table sales summary refresh.', [
                'date_option' => $dateOption,
                'exception' => $exception,
            ]);

            $this->error('The --date option must be a valid date.');

            return null;
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Models\TableSalesSummary;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class CleanupTableSalesSummaries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:cleanup-table-sales';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete table sales summaries older than two weeks.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $startedAt = microtime(true);
        $cutoffDate = today()->subDays(14)->toDateString();

        Log::info('Table sales summary cleanup started.', ['cutoff_date' => $cutoffDate]);

        try {
            $deletedCount = TableSalesSummary::query()
                ->whereDate('summary_date', '<', $cutoffDate)
                ->delete();

            Log::info('Table sales summary cleanup completed.', [
                'cutoff_date' => $cutoffDate,
                'deleted_count' => $deletedCount,
                'duration_seconds' => round(microtime(true) - $startedAt, 2),
            ]);

            $this->info("Deleted {$deletedCount} table sales summaries older than {$cutoffDate}.");

            return self::SUCCESS;
        } catch (Throwable $exception) {
            Log::error('Table sales summary cleanup failed.', [
                'cutoff_date' => $cutoffDate,
                'duration_seconds' => round(microtime(true) - $startedAt, 2),
                'exception' => $exception,
            ]);

            $this->error("Failed to clean up table sales summaries: {$exception->getMessage()}");

            return self::FAILURE;
        }
    }
}

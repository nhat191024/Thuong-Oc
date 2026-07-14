<?php

namespace App\Console\Commands;

use App\Models\DishSalesSummary;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class CleanupDishSalesSummaries extends Command
{
    protected $signature = 'reports:cleanup-dish-sales';

    protected $description = 'Delete dish sales summaries older than two weeks.';

    public function handle(): int
    {
        $startedAt = microtime(true);
        $cutoffDate = today()->subDays(14)->toDateString();

        Log::info('Dish sales summary cleanup started.', [
            'cutoff_date' => $cutoffDate,
        ]);

        try {
            $deletedCount = DishSalesSummary::query()
                ->whereDate('summary_date', '<', $cutoffDate)
                ->delete();

            Log::info('Dish sales summary cleanup completed.', [
                'cutoff_date' => $cutoffDate,
                'deleted_count' => $deletedCount,
                'duration_seconds' => round(microtime(true) - $startedAt, 2),
            ]);

            $this->info("Deleted {$deletedCount} dish sales summaries older than {$cutoffDate}.");

            return self::SUCCESS;
        } catch (Throwable $exception) {
            Log::error('Dish sales summary cleanup failed.', [
                'cutoff_date' => $cutoffDate,
                'duration_seconds' => round(microtime(true) - $startedAt, 2),
                'exception' => $exception,
            ]);

            $this->error("Failed to clean up dish sales summaries: {$exception->getMessage()}");

            return self::FAILURE;
        }
    }
}

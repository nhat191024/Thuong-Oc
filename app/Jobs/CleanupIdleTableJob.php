<?php

namespace App\Jobs;

use App\Enums\PayStatus;
use App\Enums\TableActiveStatus;
use App\Models\Table;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CleanupIdleTableJob implements ShouldQueue
{
    use Queueable;

    public function __construct() {}

    public function handle(): void
    {
        $cutoff = now()->subMinutes(15);

        // Case 1: Active tables with a bill that has no bill details (bill created >= 15 min ago)
        $tablesWithEmptyBill = Table::query()
            ->where('is_active', TableActiveStatus::ACTIVE)
            ->whereHas('bill', function ($query) use ($cutoff) {
                $query->where('pay_status', PayStatus::UNPAID)
                    ->where('created_at', '<=', $cutoff)
                    ->whereDoesntHave('billDetails');
            })
            ->with(['bill' => function ($query) use ($cutoff) {
                $query->where('pay_status', PayStatus::UNPAID)
                    ->where('created_at', '<=', $cutoff)
                    ->whereDoesntHave('billDetails');
            }])
            ->get();

        foreach ($tablesWithEmptyBill as $table) {
            if ($table->bill) {
                $table->bill->delete();
            }
            $table->is_active = TableActiveStatus::INACTIVE;
            $table->save();
        }

        // Case 2: Active tables with no bill at all
        Table::query()
            ->where('is_active', TableActiveStatus::ACTIVE)
            ->whereDoesntHave('bill', function ($query) {
                $query->where('pay_status', PayStatus::UNPAID);
            })
            ->update(['is_active' => TableActiveStatus::INACTIVE]);
    }
}

<?php

namespace App\Console\Commands;

use App\Enums\BillDetailStatus;
use App\Models\Bill;
use Illuminate\Console\Command;

class fixDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-database {--limit= : The maximum number of newest bills to fix}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $limit = $this->option('limit');

        if ($limit !== null && (! ctype_digit((string) $limit) || (int) $limit < 1)) {
            $this->error('The --limit option must be a positive integer.');

            return self::FAILURE;
        }

        $limit = $limit !== null ? (int) $limit : null;
        $totalBills = Bill::query()->count('id');
        $billsToFix = $limit !== null ? min($limit, $totalBills) : $totalBills;

        $this->info('Fixing database...');
        $this->info('Found '.$billsToFix.' bills to fix.');

        Bill::query()
            ->when($limit !== null, fn ($query) => $query->limit($limit))
            ->chunkByIdDesc(100, function ($bills): void {
                foreach ($bills as $bill) {
                    $this->info('Fixing bill id: '.$bill->id);

                    $totalPrice = (int) $bill->billDetails()
                        ->where('status', '!=', BillDetailStatus::CANCELLED->value)
                        ->selectRaw('COALESCE(SUM(price * quantity), 0) as total_price')
                        ->value('total_price');

                    $bill->total = $totalPrice;
                    $bill->final_total = $totalPrice - ($bill->discount ?? 0);
                    $bill->save();
                }
            });

        $this->info('Done!');

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Enums\BillDetailStatus;
use App\Enums\PayStatus;
use App\Models\BillDetail;
use App\Models\DishSalesSummary;
use Illuminate\Console\Command;

class RefreshDishSalesSummaries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:refresh-dish-sales';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh aggregated dish sales revenue and quantity summaries.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $calculatedAt = now();

        $summaries = BillDetail::query()
            ->join('bills', 'bills.id', '=', 'bill_details.bill_id')
            ->join('dishes', 'dishes.id', '=', 'bill_details.dish_id')
            ->join('foods', 'foods.id', '=', 'dishes.food_id')
            ->join('cooking_methods', 'cooking_methods.id', '=', 'dishes.cooking_method_id')
            ->where('bills.pay_status', PayStatus::PAID->value)
            ->where('bill_details.status', '!=', BillDetailStatus::CANCELLED->value)
            ->whereNotNull('bill_details.dish_id')
            ->groupBy(
                'bill_details.dish_id',
                'dishes.food_id',
                'dishes.cooking_method_id',
                'foods.name',
                'cooking_methods.name',
            )
            ->select([
                'bill_details.dish_id',
                'dishes.food_id',
                'dishes.cooking_method_id',
                'foods.name as food_name',
                'cooking_methods.name as cooking_method_name',
            ])
            ->selectRaw('SUM(bill_details.quantity) as total_quantity')
            ->selectRaw('SUM(bill_details.quantity * bill_details.price) as total_revenue')
            ->selectRaw('MAX(bills.time_out) as last_ordered_at')
            ->get();

        DishSalesSummary::query()
            ->getConnection()
            ->transaction(function () use ($summaries, $calculatedAt): void {
                DishSalesSummary::query()->delete();

                $summaries
                    ->map(fn ($summary): array => [
                        'dish_id' => $summary->dish_id,
                        'food_id' => $summary->food_id,
                        'cooking_method_id' => $summary->cooking_method_id,
                        'food_name' => $summary->food_name,
                        'cooking_method_name' => $summary->cooking_method_name,
                        'total_quantity' => (int) $summary->total_quantity,
                        'total_revenue' => (int) $summary->total_revenue,
                        'last_ordered_at' => $summary->last_ordered_at,
                        'calculated_at' => $calculatedAt,
                        'created_at' => $calculatedAt,
                        'updated_at' => $calculatedAt,
                    ])
                    ->chunk(500)
                    ->each(fn ($rows) => DishSalesSummary::query()->insert($rows->all()));
            });

        $this->info("Refreshed {$summaries->count()} dish sales summaries.");

        return self::SUCCESS;
    }
}

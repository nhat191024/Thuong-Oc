<?php

namespace App\Services;

use App\Events\PrintJobRequested;
use App\Models\BillDetail;
use Illuminate\Support\Str;

class KitchenPrintJobService
{
    /**
     * @return array<string, mixed>
     */
    public function request(BillDetail $billDetail): array
    {
        $billDetail->loadMissing(['dish.food', 'dish.cookingMethod', 'bill.table']);

        $job = $this->buildJob($billDetail);

        PrintJobRequested::dispatch($billDetail->bill->branch_id, $job);

        return $job;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildJob(BillDetail $billDetail): array
    {
        $tableNumber = $billDetail->bill->table->table_number ?? 'N/A';
        $dishName = $billDetail->dish?->food?->name
            ?? $billDetail->custom_dish_name
            ?? 'Món ăn';

        return [
            'request_id' => (string) Str::uuid7(),
            'table_number' => $tableNumber,
            'dish_name' => $dishName,
            'cooking_method' => $billDetail->dish?->cookingMethod?->name,
            'quantity' => $billDetail->quantity,
            'note' => $billDetail->note,
        ];
    }
}

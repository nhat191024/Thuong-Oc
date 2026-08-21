<?php

namespace App\Services;

use App\Events\PrintJobRequested;
use App\Models\BillDetail;
use App\Models\Kitchen;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class KitchenPrintJobService
{
    /**
     * @return array<string, mixed>
     */
    public function request(BillDetail $billDetail, ?Kitchen $kitchen): array
    {
        $billDetail->loadMissing(['dish.food', 'dish.cookingMethod', 'bill.table']);

        $job = $this->buildJob($billDetail);
        $branchId = $billDetail->bill->branch_id;
        $channels = [
            "private-print-stations.{$branchId}",
        ];

        if ($kitchen !== null) {
            $channels[] = "private-print-stations.{$branchId}.kitchens.{$kitchen->id}";
        }

        Log::info('Kitchen print event dispatching', [
            'event' => 'print.job.requested',
            'channels' => $channels,
            'request_id' => $job['request_id'],
            'branch_id' => $branchId,
            'kitchen_id' => $kitchen?->id,
            'bill_detail_id' => $billDetail->id,
        ]);

        try {
            PrintJobRequested::dispatch($branchId, $kitchen?->id, $job);
        } catch (Throwable $exception) {
            Log::error('Kitchen print event failed', [
                'event' => 'print.job.requested',
                'channels' => $channels,
                'request_id' => $job['request_id'],
                'branch_id' => $branchId,
                'kitchen_id' => $kitchen?->id,
                'bill_detail_id' => $billDetail->id,
                'exception' => $exception,
            ]);

            throw $exception;
        }

        Log::info('Kitchen print event broadcast succeeded', [
            'event' => 'print.job.requested',
            'channels' => $channels,
            'request_id' => $job['request_id'],
            'branch_id' => $branchId,
            'kitchen_id' => $kitchen?->id,
            'bill_detail_id' => $billDetail->id,
            'table_number' => $job['table_number'],
            'dish_name' => $job['dish_name'],
            'quantity' => $job['quantity'],
        ]);

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

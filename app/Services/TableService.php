<?php

namespace App\Services;

use App\Enums\PayStatus;
use App\Models\Table;
use App\Http\Resources\TableOrderResource;

class TableService
{
    public function getTableOrders(int $tableId)
    {
        $table = Table::with(
            [
                'bill' => function ($query) {
                    $query->where('pay_status', PayStatus::UNPAID);
                },
                'bill.billDetails.dish.food',
                'bill.billDetails.dish.cookingMethod',
            ]
        )->findOrFail($tableId);

        $data = $table->bill->billDetails->map(fn($detail) => [
            'table' => $table->table_number,
            'foodId' => $detail->dish->food_id,
            'dishId' => $detail->dish_id,
            'name' => $detail->dish->food->name,
            'quantity' => $detail->quantity,
            'price' => $detail->price,
            'cookingMethod' => $detail->dish->cookingMethod?->name,
            'cookingMethodId' => $detail->dish->cooking_method_id,
            'note' => $detail->note,
        ]);

        return $data;
    }
}

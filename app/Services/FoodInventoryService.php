<?php

namespace App\Services;

use App\Models\BillDetail;
use App\Models\BranchFoodStock;
use App\Models\FoodStockMovement;

class FoodInventoryService
{
    public function consumeCompletedBillDetail(
        BillDetail $billDetail,
        int $branchId,
        ?int $createdByUserId = null,
    ): ?FoodStockMovement {
        $billDetail->loadMissing('dish');

        $dish = $billDetail->dish;

        if ($dish === null || $dish->ingredient_quantity_kg === null) {
            return null;
        }

        $existingMovement = FoodStockMovement::query()
            ->where('bill_detail_id', $billDetail->id)
            ->where('type', FoodStockMovement::TYPE_USAGE)
            ->first();

        if ($existingMovement !== null) {
            return $existingMovement;
        }

        $quantityUsed = round((float) $dish->ingredient_quantity_kg * $billDetail->quantity, 3);

        if ($quantityUsed <= 0) {
            return null;
        }

        $stock = BranchFoodStock::query()
            ->where('branch_id', $branchId)
            ->where('food_id', $dish->food_id)
            ->lockForUpdate()
            ->first();

        if ($stock === null) {
            $stock = BranchFoodStock::create([
                'branch_id' => $branchId,
                'food_id' => $dish->food_id,
                'is_inventory_tracked' => true,
                'quantity_in_kg' => 0,
            ]);
        }

        $balanceBefore = (float) $stock->quantity_in_kg;
        $balanceAfter = round($balanceBefore - $quantityUsed, 3);

        $stock->is_inventory_tracked = true;
        $stock->quantity_in_kg = $balanceAfter;
        $stock->save();

        return FoodStockMovement::create([
            'branch_food_stock_id' => $stock->id,
            'type' => FoodStockMovement::TYPE_USAGE,
            'quantity_in_kg' => $quantityUsed,
            'balance_before_kg' => $balanceBefore,
            'balance_after_kg' => $balanceAfter,
            'bill_detail_id' => $billDetail->id,
            'created_by_user_id' => $createdByUserId,
            'note' => __('Tiêu hao khi bếp hoàn thành :quantity phần.', [
                'quantity' => $billDetail->quantity,
            ]),
            'occurred_at' => now(),
        ]);
    }
}

<?php

namespace App\Http\Resources;

use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $billDetails = $this->billDetails
            ->groupBy(fn ($detail): string => $detail->dish_id ? "dish-{$detail->dish_id}" : "custom-{$detail->id}")
            ->map(function ($details): array {
                $detail = $details->first();
                $notes = $details
                    ->pluck('note')
                    ->filter()
                    ->unique();

                $name = $detail->custom_dish_name;
                $cookingMethod = null;

                if ($detail->dish) {
                    $name = $detail->dish->food->name;
                    $cookingMethod = $detail->dish->cookingMethod?->name;
                }

                return [
                    'id' => $detail->id,
                    'name' => $name,
                    'quantity' => $details->sum('quantity'),
                    'price' => $detail->price,
                    'total' => $details->sum(fn ($detail): int|float => $detail->quantity * $detail->price),
                    'cooking_method' => $cookingMethod,
                    'note' => $notes->isNotEmpty() ? $notes->implode(', ') : null,
                ];
            })
            ->values();

        $totalAmount = $this->billDetails->sum(function ($detail) {
            return $detail->quantity * $detail->price;
        });

        $discountPercent = 0;
        $discountAmount = 0;
        $voucherCode = null;

        if ($this->voucher_id) {
            $voucher = Voucher::whereId($this->voucher_id)->first();
            if ($voucher) {
                $discountPercent = $voucher->discountPercentage();
                $discountAmount = $voucher->getDiscountAmount($totalAmount);
                $voucherCode = $voucher->code;
            }
        }

        return [
            'id' => $this->id,
            'table_id' => $this->table_id,
            'table_number' => $this->table->table_number, // Assumes table relationship is loaded
            'time_in' => $this->time_in,
            'customer' => $this->customer ? [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'phone' => $this->customer->phone,
            ] : null,
            'details' => $billDetails,
            'total_amount' => $totalAmount,
            'discount_percent' => $discountPercent,
            'discount_amount' => $discountAmount,
            'voucher_code' => $voucherCode,
            'final_amount' => $totalAmount - $discountAmount,
            'pay_status' => $this->pay_status,
        ];
    }
}

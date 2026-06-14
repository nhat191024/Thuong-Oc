<?php

namespace App\Http\Resources;

use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use Carbon\Carbon;

class BillHistoryDetailResource extends JsonResource
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

        $voucherCode = null;

        if ($this->voucher_id) {
            $voucher = Voucher::whereId($this->voucher_id)->first();
            if ($voucher) {
                $voucherCode = $voucher->code;
            }
        }

        return [
            'id' => $this->id,
            'table_id' => $this->table_id,
            'table_number' => $this->table->table_number,
            'time_in' => $this->time_in ? Carbon::parse($this->time_in)->toDateTimeString() : null,
            'time_out' => $this->time_out ? Carbon::parse($this->time_out)->toDateTimeString() : null,
            'customer' => $this->customer ? [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'phone' => $this->customer->phone,
            ] : null,
            'details' => $billDetails,
            'total_amount' => $this->total,
            'discount_amount' => $this->discount,
            'voucher_code' => $voucherCode,
            'final_amount' => $this->final_total,
            'payment_method' => $this->payment_method,
            'pay_status' => $this->pay_status,
        ];
    }
}

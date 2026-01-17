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
        $billDetails = BillDetailResource::collection($this->billDetails);
        $totalAmount = $this->billDetails->sum(function ($detail) {
            return $detail->quantity * $detail->price;
        });

        $discountPercent = 0;
        $discountAmount = 0;
        $voucherCode = null;

        if ($this->voucher_id) {
            $voucher = Voucher::find($this->voucher_id);
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

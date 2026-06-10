<?php

namespace App\Http\Resources;

use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillHistoryDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $totalAmount = $this->billDetails->sum(fn($d) => $d->quantity * $d->price);

        $discountAmount = 0;
        $voucherCode = null;

        if ($this->voucher_id) {
            $voucher = Voucher::find($this->voucher_id);
            if ($voucher) {
                $discountAmount = $voucher->getDiscountAmount($totalAmount);
                $voucherCode = $voucher->code;
            }
        }

        return [
            'id' => $this->id,
            'table_id' => $this->table_id,
            'table_number' => $this->table->table_number,
            'time_in' => $this->time_in,
            'time_out' => $this->time_out,
            'date_in' => $this->time_in ? \Carbon\Carbon::parse($this->time_in)->toDateString() : null,
            'date_out' => $this->time_out ? \Carbon\Carbon::parse($this->time_out)->toDateString() : null,
            'customer' => $this->customer ? [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'phone' => $this->customer->phone,
            ] : null,
            'details' => BillDetailResource::collection($this->billDetails),
            'total_amount' => $totalAmount,
            'discount_amount' => $discountAmount,
            'voucher_code' => $voucherCode,
            'final_total' => $this->final_total,
            'payment_method' => $this->payment_method,
            'pay_status' => $this->pay_status,
        ];
    }
}

<?php

namespace App\Http\Resources;

use App\Enums\BillDetailStatus;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $billDetails = $this->trashed()
            ? $this->billDetails
            : $this->billDetails->reject(
                fn ($detail): bool => $detail->status === BillDetailStatus::CANCELLED,
            );

        $totalAmount = $billDetails->sum(function ($detail): int|float {
            return $detail->quantity * $detail->price;
        });

        $discountAmount = 0;

        if ($this->voucher_id) {
            $voucher = Voucher::whereId($this->voucher_id)->first();
            if ($voucher) {
                $discountAmount = $voucher->getDiscountAmount($totalAmount);
            }
        } else {
            $discountAmount = $this->discount ?? 0;
        }

        return [
            'id' => $this->id,
            'time_in' => $this->time_in ? Carbon::parse($this->time_in)->toDateTimeString() : null,
            'time_out' => $this->time_out ? Carbon::parse($this->time_out)->toDateTimeString() : null,
            'deleted_at' => $this->deleted_at?->toDateTimeString(),
            'is_deleted' => $this->trashed(),
            'final_total' => $totalAmount - $discountAmount,
            'payment_method' => $this->payment_method,
            'pay_status' => $this->pay_status,
        ];
    }
}

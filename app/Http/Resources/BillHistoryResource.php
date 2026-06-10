<?php

namespace App\Http\Resources;

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
        return [
            'id' => $this->id,
            'time_in' => $this->time_in,
            'time_out' => $this->time_out,
            'date_in' => $this->time_in ? \Carbon\Carbon::parse($this->time_in)->toDateString() : null,
            'date_out' => $this->time_out ? \Carbon\Carbon::parse($this->time_out)->toDateString() : null,
            'final_total' => $this->final_total,
            'payment_method' => $this->payment_method,
            'pay_status' => $this->pay_status,
        ];
    }
}

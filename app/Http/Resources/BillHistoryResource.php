<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

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
            'time_in' => $this->time_in ? Carbon::parse($this->time_in)->toDateTimeString() : null,
            'time_out' => $this->time_out ? Carbon::parse($this->time_out)->toDateTimeString() : null,
            'final_total' => $this->final_total,
            'payment_method' => $this->payment_method,
            'pay_status' => $this->pay_status,
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrintStationKitchenResource extends JsonResource
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
            'name' => $this->name,
            'channel' => "print-stations.{$this->branch_id}.kitchens.{$this->id}",
            'pusher_channel' => "private-print-stations.{$this->branch_id}.kitchens.{$this->id}",
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrinterResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'connection' => [
                'type' => 'network',
                'host' => $this->ip_address,
                'port' => $this->port,
                'timeout_seconds' => $this->timeout,
            ],
        ];
    }
}

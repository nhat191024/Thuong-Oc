<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrintJobRequested implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array<string, mixed>  $job
     */
    public function __construct(
        private readonly int $branchId,
        private readonly ?int $kitchenId,
        public readonly array $job,
    ) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel("print-stations.{$this->branchId}"),
        ];

        if ($this->kitchenId !== null) {
            $channels[] = new PrivateChannel("print-stations.{$this->branchId}.kitchens.{$this->kitchenId}");
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'print.job.requested';
    }

    /**
     * @return array{job: array<string, mixed>}
     */
    public function broadcastWith(): array
    {
        return ['job' => $this->job];
    }
}

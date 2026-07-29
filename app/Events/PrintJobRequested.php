<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Broadcasting\ShouldRescue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrintJobRequested implements ShouldBroadcastNow, ShouldRescue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array<string, mixed>  $job
     */
    public function __construct(
        private readonly int $branchId,
        public readonly array $job,
    ) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("print-stations.{$this->branchId}"),
        ];
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

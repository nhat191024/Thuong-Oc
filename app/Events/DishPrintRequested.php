<?php

namespace App\Events;

use App\Models\BillDetail;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DishPrintRequested implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public BillDetail $billDetail, public bool $autoPrinted = false)
    {
        $this->billDetail->loadMissing(['dish.food', 'dish.cookingMethod', 'bill.table']);
    }

    /**
     * @return array<int, \Illuminate\Broadcasting\PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('kitchens.' . $this->billDetail->bill->branch_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'dish.print.requested';
    }
}

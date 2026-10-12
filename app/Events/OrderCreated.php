<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Order $order;
    public bool $notifyKitchen;

    /**
     * Create a new event instance.
     */
    public function __construct(Order $order, bool $notifyKitchen = false)
    {
        $this->order = $order;
        $this->notifyKitchen = $notifyKitchen;
    }
}

<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderReadyForWaiter extends Notification
{
    use Queueable;

    protected Order $order;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'order_ready_waiter',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'table_display' => $this->order->getTableDisplayName(),
            'total' => $this->order->total,
            'message' => "Pedido #{$this->order->order_number} pronto para servir - {$this->order->getTableDisplayName()}",
            'created_at' => now()->toISOString(),
        ];
    }
}

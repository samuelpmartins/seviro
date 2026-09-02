<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class OrderReadyForClient extends Notification
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
        return ['database', WebPushChannel::class];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'order_ready_client',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'table_display' => $this->order->getTableDisplayName(),
            'total' => $this->order->total,
            'message' => "Seu pedido #{$this->order->order_number} está pronto! Retire no balcão.",
            'created_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Payload exibido pela notificação nativa do sistema operacional.
     */
    public function toWebPush(object $notifiable): WebPushMessage
    {
        return (new WebPushMessage())
            ->title('Seu pedido está pronto!')
            ->body("#{$this->order->order_number} - retire no balcão")
            ->tag('order-' . $this->order->id)
            ->requireInteraction()
            ->vibrate([200, 100, 200])
            ->data(['url' => route('orders.history')]);
    }
}

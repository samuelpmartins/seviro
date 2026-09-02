<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class QuickItemForWaiter extends Notification
{
    use Queueable;

    protected Order $order;
    protected array $quickItems;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, array $quickItems)
    {
        $this->order = $order;
        $this->quickItems = $quickItems;
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
        $itemNames = implode(', ', array_column($this->quickItems, 'name'));

        return [
            'type' => 'quick_item_waiter',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'table_display' => $this->order->getTableDisplayName(),
            'quick_items' => $this->quickItems,
            'message' => "Pedido #{$this->order->order_number} com item rápido: {$itemNames}",
            'created_at' => $this->order->created_at->toIso8601String(),
        ];
    }

    /**
     * Payload exibido pela notificação nativa do sistema operacional.
     */
    public function toWebPush(object $notifiable): WebPushMessage
    {
        $itemNames = implode(', ', array_column($this->quickItems, 'name'));

        return (new WebPushMessage())
            ->title("Item rápido - #{$this->order->order_number}")
            ->body($this->order->getTableDisplayName() . ' - ' . ($this->order->participant->name ?? 'Sem participante') . ': ' . $itemNames)
            ->tag('order-' . $this->order->id)
            ->requireInteraction()
            ->vibrate([200, 100, 200])
            ->data(['url' => route('waiter.dashboard')]);
    }
}

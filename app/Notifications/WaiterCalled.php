<?php

namespace App\Notifications;

use App\Models\Table;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class WaiterCalled extends Notification
{
    use Queueable;

    protected Table $table;
    protected string $participantName;

    public function __construct(Table $table, string $participantName)
    {
        $this->table = $table;
        $this->participantName = $participantName;
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
            'type' => 'waiter_called',
            'table_id' => $this->table->id,
            'table_display' => 'Mesa ' . $this->table->number,
            'participant_name' => $this->participantName,
            'message' => "Mesa {$this->table->number} - {$this->participantName} solicita o garçom",
            'created_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Payload exibido pela notificação nativa do sistema operacional.
     */
    public function toWebPush(object $notifiable): WebPushMessage
    {
        return (new WebPushMessage())
            ->title('Chamando garçom')
            ->body("Mesa {$this->table->number} - {$this->participantName} solicita o garçom")
            ->tag('waiter-call-table-' . $this->table->id)
            ->requireInteraction()
            ->vibrate([300, 100, 300, 100, 300])
            ->data(['url' => route('waiter.dashboard')]);
    }
}

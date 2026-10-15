<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PrintRequested extends Notification
{
    use Queueable;

    public function __construct(
        protected array $printOrder,
        protected string $requestedBy
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'print_request',
            'message' => "{$this->requestedBy} solicitou impressão: {$this->printOrder['OrderNumber']}",
            'content' => json_encode($this->printOrder, JSON_UNESCAPED_UNICODE),
            'copies' => 1,
            'cut_paper' => true,
            'created_at' => now()->toIso8601String(),
        ];
    }
}

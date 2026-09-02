<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Models\User;
use App\Notifications\OrderReadyForWaiter;
use App\Notifications\OrderReadyForClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\DeviceToken;
use App\Services\PushService;
use Illuminate\Support\Carbon;

class NotifyOrderFinished
{
    /**
     * Handle the event.
     */
    public function handle(OrderStatusChanged $event): void
    {
        // Só processar se o status mudou para "Finalizado"
        if ($event->newStatus !== 'Finalizado') {
            return;
        }

        $order = $event->order;
        $store = $order->store;
        // TableParticipant não possui relação "user"; o acesso abaixo sempre resulta em null.
        $order->loadMissing('user.pushSubscriptions', 'participant');

        // Buscar garçons da loja
        $waiters = User::where('store_id', $store->id)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'waiter');
            })
            ->with('pushSubscriptions')
            ->get();

        $hasWaiters = $waiters->isNotEmpty();

        if ($hasWaiters) {
            // REGRA: Pedido finalizado COM garçom → notificar garçom
            $this->notifySafely($waiters, new OrderReadyForWaiter($order));
        } else {
            // REGRA: Pedido finalizado SEM garçom → notificar cliente
            $notificationSent = false;

            // Tentar notificar o usuário que fez o pedido
            if ($order->user) {
                $this->notifySafely($order->user, new OrderReadyForClient($order));
                $notificationSent = true;
            }

            // Também notificar o participante da mesa (se houver usuário associado)
            if ($order->participant && $order->participant->user) {
                $this->notifySafely($order->participant->user, new OrderReadyForClient($order));
                $notificationSent = true;
            }

            // Se não conseguiu enviar notificação (cliente não autenticado),
            // criar notificação genérica no banco de dados
            if (!$notificationSent) {
                $this->createGuestNotification($order);
                // tentar enviar push para tokens associados diretamente ao pedido
                $guestTokens = DeviceToken::where('notifiable_type', 'App\\Models\\Order')
                    ->where('notifiable_id', $order->id)
                    ->pluck('token')
                    ->toArray();

                if (!empty($guestTokens)) {
                    try {
                        PushService::sendToTokens($guestTokens, 'Pedido pronto', "Seu pedido #{$order->order_number} está pronto!", ['order_id' => $order->id]);
                    } catch (\Exception $e) {
                        // não falhar o processamento principal por conta do push
                    }
                }
            }
        }

        // notificar via push também usuários autenticados que tenham tokens
        try {
            $tokens = [];
            if ($order->user) {
                $tokens = array_merge($tokens, DeviceToken::where('user_id', $order->user->id)->pluck('token')->toArray());
            }

            if ($order->participant && $order->participant->user) {
                $tokens = array_merge($tokens, DeviceToken::where('user_id', $order->participant->user->id)->pluck('token')->toArray());
            }

            $tokens = array_values(array_unique($tokens));
            if (!empty($tokens)) {
                PushService::sendToTokens($tokens, 'Pedido pronto', "Seu pedido #{$order->order_number} está pronto!", ['order_id' => $order->id]);
            }
        } catch (\Exception $e) {
            // silenciar erros de push
        }
    }

    /**
     * Envia a notificação sem deixar uma falha (ex.: push) reverter a transação do pedido.
     */
    private function notifySafely($notifiables, $notification): void
    {
        try {
            Notification::send($notifiables, $notification);
        } catch (\Throwable $e) {
            Log::error('Falha ao enviar notificação de pedido', [
                'notification' => get_class($notification),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Cria notificação para clientes não autenticados (guests)
     * Usa um usuário fictício para armazenar a notificação
     */
    private function createGuestNotification($order)
    {
        // Criar notificação diretamente no banco de dados
        $notificationData = [
            'type' => 'order_ready_client',
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'table_display' => $order->getTableDisplayName(),
            'total' => $order->total,
            'message' => "Seu pedido #{$order->order_number} está pronto! Retire no balcão.",
            'created_at' => Carbon::now()->toIso8601String(),
        ];

        DB::table('notifications')->insert([
            'id' => Str::uuid()->toString(),
            'type' => 'App\\Notifications\\OrderReadyForClient',
            'notifiable_type' => 'App\\Models\\Order',
            'notifiable_id' => $order->id,
            'data' => json_encode($notificationData),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

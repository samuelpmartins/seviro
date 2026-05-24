<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Models\User;
use App\Notifications\OrderReadyForWaiter;
use App\Notifications\OrderReadyForClient;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
        
        // Buscar garçons da loja
        $waiters = User::where('store_id', $store->id)
            ->whereHas('roles', function($query) {
                $query->where('name', 'waiter');
            })
            ->get();
        
        $hasWaiters = $waiters->isNotEmpty();
        
        if ($hasWaiters) {
            // REGRA: Pedido finalizado COM garçom → notificar garçom
            Notification::send($waiters, new OrderReadyForWaiter($order));
        } else {
            // REGRA: Pedido finalizado SEM garçom → notificar cliente
            $notificationSent = false;
            
            // Tentar notificar o usuário que fez o pedido
            if ($order->user) {
                $order->user->notify(new OrderReadyForClient($order));
                $notificationSent = true;
            }
            
            // Também notificar o participante da mesa (se houver usuário associado)
            if ($order->participant && $order->participant->user) {
                $order->participant->user->notify(new OrderReadyForClient($order));
                $notificationSent = true;
            }
            
            // Se não conseguiu enviar notificação (cliente não autenticado),
            // criar notificação genérica no banco de dados
            if (!$notificationSent) {
                $this->createGuestNotification($order);
            }
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
            'created_at' => now()->toISOString(),
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

<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Events\OrderPaid;
use App\Models\User;
use App\Notifications\NewOrderForKitchen;
use App\Notifications\QuickItemForWaiter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class NotifyOrderCreated
{
    /**
     * Handle the event.
     */
    public function handle(OrderCreated|OrderPaid $event): void
    {
        $order = $event->order;
        $store = $order->store;

        // Carregar os itens do pedido com os produtos, e o participante (usado nas notificações)
        $order->load('items.product', 'participant');

        // Analisar os itens do pedido
        $quickItems = [];
        $hasNonQuickItems = false;

        foreach ($order->items as $item) {
            if ($item->product && $item->product->is_quick_item) {
                $quickItems[] = [
                    'name' => $item->product->name,
                    'quantity' => $item->quantity,
                ];
            } else {
                $hasNonQuickItems = true;
            }
        }

        // Verificar se o pedido contém APENAS itens rápidos
        $onlyQuickItems = !empty($quickItems) && !$hasNonQuickItems;

        // Buscar garçons da loja
        $waiters = User::where('store_id', $store->id)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'waiter');
            })
            ->with('pushSubscriptions')
            ->get();

        $hasWaiters = $waiters->isNotEmpty();

        // REGRA: Se o pedido contém APENAS itens rápidos
        if ($onlyQuickItems) {
            // Notificar garçom que o pedido está pronto para ser entregue
            if ($hasWaiters) {
                $this->notifySafely($waiters, new QuickItemForWaiter($order, $quickItems));
            }

            // Não notifica a cozinha
            return;
        }

        // REGRA: Item rápido sempre notifica garçom (se houver) quando tem outros itens também
        if (!empty($quickItems) && $hasWaiters) {
            $this->notifySafely($waiters, new QuickItemForWaiter($order, $quickItems));
        }

        // REGRA: Notificar cozinha para pedidos com itens não-rápidos
        $shouldNotifyKitchen = false;

        // Pedido PAGO sem garçom (e contém itens não-rápidos)
        if ($order->isPaid() && !$hasWaiters) {
            $shouldNotifyKitchen = true;
        }
        // Pedido NÃO PAGO com garçom (e contém itens não-rápidos)
        else if (!$order->isPaid() && $hasWaiters) {
            $shouldNotifyKitchen = true;
        }

        if ($shouldNotifyKitchen) {
            $kitchenStaff = User::where('store_id', $store->id)
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'kitchen');
                })
                ->with('pushSubscriptions')
                ->get();

            if ($kitchenStaff->isNotEmpty()) {
                $this->notifySafely($kitchenStaff, new NewOrderForKitchen($order));
            }
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
}

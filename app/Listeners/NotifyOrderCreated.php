<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Events\OrderPaid;
use App\Models\User;
use App\Notifications\NewOrderForKitchen;
use App\Notifications\QuickItemForWaiter;
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
        
        // Carregar os itens do pedido com os produtos
        $order->load('items.product');
        
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
            ->whereHas('roles', function($query) {
                $query->where('name', 'waiter');
            })
            ->get();
        
        $hasWaiters = $waiters->isNotEmpty();
        
        // REGRA: Se o pedido contém APENAS itens rápidos
        if ($onlyQuickItems) {
            // Notificar garçom que o pedido está pronto para ser entregue
            if ($hasWaiters) {
                Notification::send($waiters, new QuickItemForWaiter($order, $quickItems));
            }
            
            // Não notifica a cozinha
            return;
        }
        
        // REGRA: Item rápido sempre notifica garçom (se houver) quando tem outros itens também
        if (!empty($quickItems) && $hasWaiters) {
            Notification::send($waiters, new QuickItemForWaiter($order, $quickItems));
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
                ->whereHas('roles', function($query) {
                    $query->where('name', 'kitchen');
                })
                ->get();
                
            if ($kitchenStaff->isNotEmpty()) {
                Notification::send($kitchenStaff, new NewOrderForKitchen($order));
            }
        }
    }
}

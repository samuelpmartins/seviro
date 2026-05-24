<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Events\OrderStatusChanged;

class TestNotificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:notification {order_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa o sistema de notificações disparando manualmente o evento OrderStatusChanged';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orderId = $this->argument('order_id');
        
        $order = Order::find($orderId);
        
        if (!$order) {
            $this->error("Pedido #{$orderId} não encontrado!");
            return 1;
        }
        
        $this->info("Pedido encontrado: #{$order->order_number}");
        $this->info("Status atual: {$order->status}");
        $this->info("User ID: {$order->user_id}");
        $this->info("Participant ID: {$order->participant_id}");
        
        $this->info("\nDisparando evento OrderStatusChanged...");
        
        $oldStatus = $order->status;
        event(new OrderStatusChanged($order, $oldStatus, 'Finalizado'));
        
        $this->info("Evento disparado com sucesso!");
        $this->info("\nVerifique o arquivo storage/logs/laravel.log para ver os logs.");
        
        return 0;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Table;
use App\Models\Order;
use Illuminate\Console\Command;

class CheckTablesStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tables:check-status {table_number?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica o status das mesas e desocupa automaticamente as que têm todos os pedidos pagos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tableNumber = $this->argument('table_number');

        if ($tableNumber) {
            // Verificar mesa específica
            $table = Table::where('number', $tableNumber)->first();
            
            if (!$table) {
                $this->error("Mesa {$tableNumber} não encontrada!");
                return 1;
            }

            $this->checkTable($table);
        } else {
            // Verificar todas as mesas ocupadas
            $tables = Table::where('occupied', true)->get();
            
            if ($tables->isEmpty()) {
                $this->info('Nenhuma mesa ocupada no momento.');
                return 0;
            }

            $this->info("Verificando {$tables->count()} mesa(s) ocupada(s)...\n");

            foreach ($tables as $table) {
                $this->checkTable($table);
                $this->line('---');
            }
        }

        return 0;
    }

    /**
     * Verifica uma mesa específica
     */
    private function checkTable(Table $table)
    {
        $this->line("Mesa: {$table->number}");
        $this->line("Status: " . ($table->occupied ? 'OCUPADA' : 'LIVRE'));
        
        // Contar participantes
        $participantsCount = $table->participants()->count();
        $this->line("Participantes ativos: {$participantsCount}");
        
        if ($participantsCount > 0) {
            $participants = $table->participants()->get();
            foreach ($participants as $participant) {
                $this->line("  - {$participant->name} " . ($participant->is_owner ? '(Owner)' : ''));
            }
        }

        // Contar pedidos
        $totalOrders = $table->orders()->count();
        $pendingOrders = $table->orders()
            ->where('payment_status', Order::PAYMENT_STATUS_PENDING)
            ->count();
        $paidOrders = $table->orders()
            ->where('payment_status', Order::PAYMENT_STATUS_PAID)
            ->count();

        $this->line("Total de pedidos: {$totalOrders}");
        $this->line("Pedidos pendentes: {$pendingOrders}");
        $this->line("Pedidos pagos: {$paidOrders}");

        // Verificar pedidos sem participante
        $ordersWithoutParticipant = $table->orders()
            ->whereNull('participant_id')
            ->count();
        
        if ($ordersWithoutParticipant > 0) {
            $pendingWithoutParticipant = $table->orders()
                ->whereNull('participant_id')
                ->where('payment_status', Order::PAYMENT_STATUS_PENDING)
                ->count();
            
            $this->warn("Pedidos sem participante: {$ordersWithoutParticipant} (pendentes: {$pendingWithoutParticipant})");
        }

        // Verificar se deve desocupar
        if ($table->occupied && $pendingOrders === 0) {
            $this->info("\n✓ Todos os pedidos foram pagos!");
            
            if ($this->confirm('Deseja desocupar esta mesa?', true)) {
                $table->checkAndClearIfFullyPaid();
                $table->refresh();
                
                if (!$table->occupied) {
                    $this->info("✓ Mesa {$table->number} desocupada com sucesso!");
                } else {
                    $this->warn("⚠ Não foi possível desocupar a mesa.");
                }
            }
        } elseif ($pendingOrders > 0) {
            $this->warn("\n⚠ Ainda há {$pendingOrders} pedido(s) pendente(s) de pagamento.");
            
            // Mostrar detalhes dos pedidos pendentes
            $pending = $table->orders()
                ->where('payment_status', Order::PAYMENT_STATUS_PENDING)
                ->with('participant')
                ->get();
            
            foreach ($pending as $order) {
                $participantName = $order->participant ? $order->participant->name : 'Sem participante';
                $this->line("  - Pedido #{$order->order_number} - {$participantName} - R$ {$order->total}");
            }
        }
    }
}

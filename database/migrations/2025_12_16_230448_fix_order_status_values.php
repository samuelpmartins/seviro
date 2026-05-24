<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Corrigir status em inglês para português
        $statusMap = [
            'pending' => 'Aguardando pagamento',
            'confirmed' => 'Aguardando pagamento',
            'preparing' => 'Em produção',
            'in_production' => 'Em produção',
            'ready' => 'Finalizado',
            'completed' => 'Finalizado',
            'done' => 'Finalizado',
            'canceled' => 'Cancelado',
            'cancelled' => 'Cancelado',
            'paid' => 'Pago',
        ];

        foreach ($statusMap as $oldStatus => $newStatus) {
            DB::table('orders')
                ->where('status', $oldStatus)
                ->update(['status' => $newStatus]);
        }

        // Qualquer outro status que não seja válido, define como "Aguardando pagamento"
        DB::table('orders')
            ->whereNotIn('status', [
                'Aguardando pagamento',
                'Em produção',
                'Finalizado',
                'Cancelado',
                'Pago'
            ])
            ->update(['status' => 'Aguardando pagamento']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Não há necessidade de reverter
    }
};

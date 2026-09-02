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
        // Primeiro, garantir que todos os pedidos existentes sem order_number recebam um
        $ordersWithoutNumber = DB::table('orders')->whereNull('order_number')->get();

        foreach ($ordersWithoutNumber as $order) {
            if ($order->table_id) {
                $table = DB::table('tables')->where('id', $order->table_id)->first();
                if ($table) {
                    // Conta quantos pedidos já existem para essa mesa
                    $orderCount = DB::table('orders')->where('table_id', $order->table_id)
                        ->whereNotNull('order_number')
                        ->count() + 1;

                    // Formata o número da mesa com 2 dígitos
                    $tableNumber = str_pad($table->number, 2, '0', STR_PAD_LEFT);

                    // Formata a ordem do pedido com 2 dígitos
                    $orderSequence = str_pad($orderCount, 2, '0', STR_PAD_LEFT);

                    // Gera o número no formato: 06A02
                    $orderNumber = $tableNumber . 'A' . $orderSequence;

                    DB::table('orders')->where('id', $order->id)->update([
                        'order_number' => $orderNumber,
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Agora torna o campo obrigatório (NOT NULL)
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_number')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_number')->nullable()->change();
        });
    }
};

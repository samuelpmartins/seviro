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
        // Adiciona a coluna como nullable primeiro (se não existir)
        if (!Schema::hasColumn('orders', 'order_number')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('order_number')->nullable()->after('id');
            });
        }

        // Gera números para os pedidos existentes
        $orders = DB::table('orders')->get();

        // Agrupa pedidos por mesa
        $ordersByTable = $orders->groupBy('table_id');

        foreach ($ordersByTable as $tableId => $tableOrders) {
            $table = DB::table('tables')->where('id', $tableId)->first();
            if (!$table) continue;

            $tableNumber = str_pad($table->number, 2, '0', STR_PAD_LEFT);

            // Ordena por created_at para manter a ordem cronológica
            $sortedOrders = $tableOrders->sortBy('created_at');

            $sequence = 1;
            foreach ($sortedOrders as $order) {
                $orderSequence = str_pad($sequence, 2, '0', STR_PAD_LEFT);
                $orderNumber = $tableNumber . 'A' . $orderSequence;

                DB::table('orders')->where('id', $order->id)->update([
                    'order_number' => $orderNumber,
                    'updated_at' => now(),
                ]);
                $sequence++;
            }
        }

        // Torna o campo obrigatório (NOT NULL) e único
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_number')->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('order_number');
        });
    }
};

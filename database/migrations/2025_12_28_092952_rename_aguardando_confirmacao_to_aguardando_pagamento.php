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
        // Renomear status "Aguardando confirmação" para "Aguardando pagamento"
        DB::table('orders')
            ->where('status', 'Aguardando confirmação')
            ->update(['status' => 'Aguardando pagamento']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter: renomear "Aguardando pagamento" para "Aguardando confirmação"
        DB::table('orders')
            ->where('status', 'Aguardando pagamento')
            ->update(['status' => 'Aguardando confirmação']);
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            
            // Valores
            $table->decimal('amount', 10, 2); // Valor bruto solicitado
            $table->decimal('commission_amount', 10, 2)->default(0); // Valor da comissão
            $table->decimal('commission_percentage', 5, 2)->default(0); // Percentual aplicado
            $table->decimal('net_amount', 10, 2); // Valor líquido
            
            // Status e datas
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed', 'cancelled'])->default('pending');
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            // Dados bancários usados
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->string('pix_key_used')->nullable();
            $table->json('bank_data_used')->nullable();
            
            // Aprovação e observações
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            
            $table->timestamps();
            
            // Índices para buscas rápidas
            $table->index(['store_id', 'status']);
            $table->index('status');
            $table->index('requested_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};

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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('table_id')->constrained()->cascadeOnDelete();
            $table->string('stripe_payment_id')->nullable();
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('payment_method'); // card, pix, cash
            $table->decimal('amount', 10, 2);
            $table->string('status'); // pending, processing, succeeded, failed, canceled
            $table->json('order_ids'); // Array dos IDs dos pedidos pagos
            $table->foreignId('paid_by_participant_id')
                  ->nullable()
                  ->constrained('table_participants')
                  ->nullOnDelete();
            $table->foreignId('marked_by_user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete(); // Para pagamento em dinheiro (garçom)
            $table->decimal('cash_received', 10, 2)->nullable(); // Valor recebido em dinheiro
            $table->decimal('change_given', 10, 2)->nullable(); // Troco dado
            $table->text('notes')->nullable();
            $table->string('pix_qr_code')->nullable(); // QR Code do PIX
            $table->string('pix_code')->nullable(); // Código copia e cola do PIX
            $table->timestamp('expires_at')->nullable(); // Expiração do PIX
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};










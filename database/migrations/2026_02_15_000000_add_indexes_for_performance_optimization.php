<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adiciona índices para otimizar performance e reduzir número de conexões ao banco.
     * Estes índices melhoram significativamente a velocidade de queries frequentes.
     */
    public function up(): void
    {
        // Índices para a tabela orders
        Schema::table('orders', function (Blueprint $table) {
            // Índice composto para queries comuns (table_id + payment_status)
            $table->index(['table_id', 'payment_status'], 'idx_orders_table_payment');
            
            // Índice para participant_id (usado frequentemente em joins)
            $table->index('participant_id', 'idx_orders_participant');
            
            // Índice para created_at (ordenação temporal)
            $table->index('created_at', 'idx_orders_created');
            
            // Índice para order_number (buscas específicas)
            $table->index('order_number', 'idx_orders_number');
        });

        // Índices para a tabela tables
        Schema::table('tables', function (Blueprint $table) {
            // Índice para qr_code (busca muito frequente)
            $table->index('qr_code', 'idx_tables_qr');
            
            // Índice para store_id (joins frequentes)
            $table->index('store_id', 'idx_tables_store');
        });

        // Índices para a tabela stores
        Schema::table('stores', function (Blueprint $table) {
            // Índice para counter_qr_code (busca de QR de balcão)
            $table->index('counter_qr_code', 'idx_stores_counter_qr');
        });

        // Índices para a tabela table_participants
        Schema::table('table_participants', function (Blueprint $table) {
            // Índice para table_id (joins frequentes)
            $table->index('table_id', 'idx_participants_table');
        });

        // Índices para a tabela payments
        Schema::table('payments', function (Blueprint $table) {
            // Índice para stripe_payment_intent_id (buscas em webhooks)
            $table->index('stripe_payment_intent_id', 'idx_payments_stripe_intent');
            
            // Índice para table_id
            $table->index('table_id', 'idx_payments_table');
            
            // Índice para status
            $table->index('status', 'idx_payments_status');
        });

        // Índices para a tabela products
        Schema::table('products', function (Blueprint $table) {
            // Índice composto para category_id + active
            $table->index(['category_id', 'active'], 'idx_products_category_active');
        });

        // Índices para a tabela order_items
        Schema::table('order_items', function (Blueprint $table) {
            // Índice para order_id (joins muito frequentes)
            $table->index('order_id', 'idx_order_items_order');
            
            // Índice para product_id
            $table->index('product_id', 'idx_order_items_product');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover índices da tabela orders
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_table_payment');
            $table->dropIndex('idx_orders_participant');
            $table->dropIndex('idx_orders_created');
            $table->dropIndex('idx_orders_number');
        });

        // Remover índices da tabela tables
        Schema::table('tables', function (Blueprint $table) {
            $table->dropIndex('idx_tables_qr');
            $table->dropIndex('idx_tables_store');
        });

        // Remover índices da tabela stores
        Schema::table('stores', function (Blueprint $table) {
            $table->dropIndex('idx_stores_counter_qr');
        });

        // Remover índices da tabela table_participants
        Schema::table('table_participants', function (Blueprint $table) {
            $table->dropIndex('idx_participants_table');
        });

        // Remover índices da tabela payments
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('idx_payments_stripe_intent');
            $table->dropIndex('idx_payments_table');
            $table->dropIndex('idx_payments_status');
        });

        // Remover índices da tabela products
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_category_active');
        });

        // Remover índices da tabela order_items
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('idx_order_items_order');
            $table->dropIndex('idx_order_items_product');
        });
    }
};

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
        Schema::table('orders', function (Blueprint $table) {
            // Remover a chave estrangeira existente
            $table->dropForeign(['table_id']);
            
            // Modificar a coluna para ser nullable
            $table->foreignId('table_id')->nullable()->change();
            
            // Recriar a chave estrangeira
            $table->foreign('table_id')
                  ->references('id')
                  ->on('tables')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Remover a chave estrangeira
            $table->dropForeign(['table_id']);
            
            // Reverter para não nullable
            $table->foreignId('table_id')->nullable(false)->change();
            
            // Recriar a chave estrangeira
            $table->foreign('table_id')
                  ->references('id')
                  ->on('tables')
                  ->onDelete('cascade');
        });
    }
};

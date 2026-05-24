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
        Schema::table('payments', function (Blueprint $table) {
            // Remover a foreign key constraint existente
            $table->dropForeign(['table_id']);
            
            // Modificar a coluna para aceitar null
            $table->foreignId('table_id')->nullable()->change();
            
            // Adicionar novamente a foreign key constraint com nullable
            $table->foreign('table_id')
                  ->references('id')
                  ->on('tables')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Remover a foreign key constraint
            $table->dropForeign(['table_id']);
            
            // Reverter para NOT NULL
            $table->foreignId('table_id')->nullable(false)->change();
            
            // Adicionar novamente a foreign key constraint
            $table->foreign('table_id')
                  ->references('id')
                  ->on('tables')
                  ->cascadeOnDelete();
        });
    }
};

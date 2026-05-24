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
        // Adicionar campo password na tabela tables
        Schema::table('tables', function (Blueprint $table) {
            $table->string('password', 4)->nullable()->after('qr_code');
        });

        // Criar tabela table_participants
        Schema::create('table_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->boolean('is_owner')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_participants');
        
        Schema::table('tables', function (Blueprint $table) {
            $table->dropColumn('password');
        });
    }
};

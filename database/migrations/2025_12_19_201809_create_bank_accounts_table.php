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
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->unique()->constrained()->cascadeOnDelete();
            
            // Dados PIX
            $table->string('pix_key')->nullable();
            $table->enum('pix_key_type', ['cpf', 'cnpj', 'email', 'phone', 'random'])->nullable();
            
            // Dados bancários
            $table->string('bank_code')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('agency')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_digit')->nullable();
            $table->enum('account_type', ['checking', 'savings'])->nullable();
            $table->string('account_holder_name')->nullable();
            $table->string('account_holder_document')->nullable();
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};

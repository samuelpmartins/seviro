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
        // Renomear a tabela de product_ingredient para product_ingredients
        Schema::rename('product_ingredient', 'product_ingredients');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter o rename
        Schema::rename('product_ingredients', 'product_ingredient');
    }
};

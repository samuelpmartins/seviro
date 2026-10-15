<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id')->index();
            $table->unsignedBigInteger('table_id')->index();
            $table->unsignedBigInteger('payment_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('type', 20);
            $table->decimal('amount', 10, 2);
            $table->decimal('base_total', 10, 2);
            $table->decimal('adjusted_total', 10, 2);
            $table->json('order_ids');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_adjustments');
    }
};

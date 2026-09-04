<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('table_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('participant_id')->nullable()->constrained('table_participants')->nullOnDelete();
            $table->foreignId('waiter_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['store_id', 'waiter_id']);
            $table->index(['table_id', 'participant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_attendances');
    }
};

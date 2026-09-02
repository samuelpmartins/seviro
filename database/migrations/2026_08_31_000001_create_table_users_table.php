<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('table_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('table_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('service_status');
            $table->unsignedTinyInteger('assignment_type');
            $table->timestamps();

            $table->index(['store_id', 'service_status']);
            $table->index(['user_id', 'service_status']);
            $table->index(['table_id', 'service_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('table_users');
    }
};

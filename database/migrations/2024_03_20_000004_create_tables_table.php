<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->string('number');
            $table->string('qr_code')->unique();
            $table->boolean('occupied')->default(false);
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->timestamp('last_activity')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
}; 
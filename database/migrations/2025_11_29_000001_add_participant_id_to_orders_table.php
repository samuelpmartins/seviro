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
            $table->foreignId('participant_id')
                  ->nullable()
                  ->after('user_id')
                  ->constrained('table_participants')
                  ->nullOnDelete();
            
            $table->string('payment_status')->default('pending')->after('status');
            $table->string('payment_method')->nullable()->after('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['participant_id']);
            $table->dropColumn(['participant_id', 'payment_status', 'payment_method']);
        });
    }
};










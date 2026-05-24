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
        Schema::table('tables', function (Blueprint $table) {
            $table->foreignId('current_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('current_user_name')->nullable();
            $table->timestamp('occupied_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropForeign(['current_user_id']);
            $table->dropColumn(['current_user_id', 'current_user_name', 'occupied_at']);
        });
    }
};

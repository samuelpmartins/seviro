<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->boolean('service_fee_included')->default(false)->after('amount');
            $table->decimal('service_fee_percentage', 5, 2)->default(0)->after('service_fee_included');
            $table->decimal('service_fee', 10, 2)->default(0)->after('service_fee_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['service_fee_included', 'service_fee_percentage', 'service_fee']);
        });
    }
};

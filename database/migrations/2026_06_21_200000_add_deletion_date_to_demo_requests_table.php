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
        if (Schema::hasTable('demo_requests') && ! Schema::hasColumn('demo_requests', 'DeletionDate')) {
            Schema::table('demo_requests', function (Blueprint $table) {
                $table->timestamp('DeletionDate')->nullable()->after('updated_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('demo_requests') && Schema::hasColumn('demo_requests', 'DeletionDate')) {
            Schema::table('demo_requests', function (Blueprint $table) {
                $table->dropColumn('DeletionDate');
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('table_users') && !Schema::hasColumn('table_users', 'DeletionDate')) {
            Schema::table('table_users', function (Blueprint $table) {
                $table->timestamp('DeletionDate')->nullable()->after('updated_at');
                $table->index('DeletionDate');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('table_users', 'DeletionDate')) {
            Schema::table('table_users', function (Blueprint $table) {
                $table->dropIndex(['DeletionDate']);
                $table->dropColumn('DeletionDate');
            });
        }
    }
};

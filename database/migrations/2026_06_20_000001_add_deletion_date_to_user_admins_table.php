<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_admins', function (Blueprint $table) {
            if (!Schema::hasColumn('user_admins', 'DeletionDate')) {
                $table->softDeletesTz('DeletionDate')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_admins', function (Blueprint $table) {
            if (Schema::hasColumn('user_admins', 'DeletionDate')) {
                $table->dropSoftDeletes('DeletionDate');
            }
        });
    }
};

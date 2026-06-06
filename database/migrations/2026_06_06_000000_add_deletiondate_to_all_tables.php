<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tables = [
            'users',
            'stores',
            'tables',
            'table_participants',
            'bank_accounts',
            'categories',
            'orders',
            'order_items',
            'payments',
            'products',
            'product_ingredients',
            'withdrawals',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'DeletionDate')) {
                Schema::table($table, function (Blueprint $tableBlueprint) {
                    $tableBlueprint->timestamp('DeletionDate')->nullable()->after('updated_at');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tables = [
            'users',
            'stores',
            'tables',
            'table_participants',
            'bank_accounts',
            'categories',
            'orders',
            'order_items',
            'payments',
            'products',
            'product_ingredients',
            'withdrawals',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'DeletionDate')) {
                Schema::table($table, function (Blueprint $tableBlueprint) {
                    $tableBlueprint->dropColumn('DeletionDate');
                });
            }
        }
    }
};

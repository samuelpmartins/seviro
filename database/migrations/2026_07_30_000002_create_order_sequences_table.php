<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_sequences', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('table_id')->nullable()->unique();
            $table->unsignedInteger('last_sequence')->default(0);
            $table->timestamps();
        });

        // Seed sequences based on existing orders
        // For portability across DB drivers (MySQL/Postgres), compute in PHP
        $orders = DB::table('orders')->select('table_id', 'order_number')->get();

        $maxByTable = [];
        foreach ($orders as $o) {
            $tableIdKey = is_null($o->table_id) ? 0 : intval($o->table_id);
            $orderNumber = $o->order_number;
            if (empty($orderNumber)) continue;

            $parts = explode('A', $orderNumber);
            $seqPart = end($parts);
            $seq = intval(preg_replace('/[^0-9]/', '', $seqPart));

            if (!isset($maxByTable[$tableIdKey]) || $seq > $maxByTable[$tableIdKey]) {
                $maxByTable[$tableIdKey] = $seq;
            }
        }

        foreach ($maxByTable as $tableKey => $seq) {
            DB::table('order_sequences')->insert([
                'table_id' => $tableKey === 0 ? null : $tableKey,
                'last_sequence' => intval($seq),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_sequences');
    }
};

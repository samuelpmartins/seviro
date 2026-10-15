<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('price_to_negotiate')->default(false);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('negotiated_total', 10, 2)->nullable();
        });

        Schema::create('order_item_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id')->index();
            $table->unsignedBigInteger('order_id')->index();
            $table->unsignedBigInteger('order_item_id')->index();
            $table->unsignedBigInteger('table_id')->nullable()->index();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->decimal('previous_amount', 10, 2)->default(0);
            $table->decimal('amount', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_item_values');

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('negotiated_total');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('price_to_negotiate');
        });
    }
};

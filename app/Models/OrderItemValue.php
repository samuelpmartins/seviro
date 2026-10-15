<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemValue extends Model
{
    protected $fillable = [
        'store_id',
        'order_id',
        'order_item_id',
        'table_id',
        'product_id',
        'user_id',
        'previous_amount',
        'amount',
    ];

    protected $casts = [
        'previous_amount' => 'decimal:2',
        'amount' => 'decimal:2',
    ];
}

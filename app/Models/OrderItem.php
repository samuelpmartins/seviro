<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory, SoftDeletes;

    const DELETED_AT = 'DeletionDate';

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'negotiated_total',
        'notes'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'negotiated_total' => 'decimal:2'
    ];

    public function getLineTotalAttribute(): float
    {
        return $this->negotiated_total !== null
            ? (float) $this->negotiated_total
            : (float) $this->price * $this->quantity;
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

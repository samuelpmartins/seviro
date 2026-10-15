<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentAdjustment extends Model
{
    const TYPE_DISCOUNT = 'discount';
    const TYPE_SURCHARGE = 'surcharge';

    protected $fillable = [
        'store_id',
        'table_id',
        'payment_id',
        'user_id',
        'type',
        'amount',
        'base_total',
        'adjusted_total',
        'order_ids',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'base_total' => 'decimal:2',
        'adjusted_total' => 'decimal:2',
        'order_ids' => 'array',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}

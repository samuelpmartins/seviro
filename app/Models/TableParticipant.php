<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TableParticipant extends Model
{
    use HasFactory, SoftDeletes;

    const DELETED_AT = 'DeletionDate';

    protected $fillable = [
        'table_id',
        'name',
        'is_owner',
    ];

    protected $casts = [
        'is_owner' => 'boolean',
    ];

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'participant_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'paid_by_participant_id');
    }

    /**
     * Retorna o total de pedidos pendentes do participante
     */
    public function getUnpaidTotalAttribute(): float
    {
        return $this->orders()
            ->where('payment_status', Order::PAYMENT_STATUS_PENDING)
            ->sum('total');
    }

    /**
     * Retorna os pedidos pendentes do participante
     */
    public function getUnpaidOrdersAttribute()
    {
        return $this->orders()
            ->where('payment_status', Order::PAYMENT_STATUS_PENDING)
            ->get();
    }
}

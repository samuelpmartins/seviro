<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'order_id',
        'table_id',
        'participant_id',
        'waiter_id',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(TableParticipant::class, 'participant_id')->withTrashed();
    }

    public function waiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'waiter_id')->withTrashed();
    }
}

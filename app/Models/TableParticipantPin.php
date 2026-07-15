<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TableParticipantPin extends Model
{
    use HasFactory, SoftDeletes;

    const DELETED_AT = 'DeletionDate';

    protected $fillable = [
        'table_participant_id',
        'pin',
        'status',
        'next_validate',
    ];

    protected $casts = [
        'next_validate' => 'datetime',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(TableParticipant::class);
    }
}

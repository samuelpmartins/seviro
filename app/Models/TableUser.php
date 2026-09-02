<?php

namespace App\Models;

use App\Enums\TableAssignmentType;
use App\Enums\TableServiceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TableUser extends Model
{
    use HasFactory, SoftDeletes;

    const DELETED_AT = 'DeletionDate';

    protected $fillable = [
        'store_id',
        'table_id',
        'user_id',
        'service_status',
        'assignment_type',
    ];

    protected $casts = [
        'service_status' => TableServiceStatus::class,
        'assignment_type' => TableAssignmentType::class,
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

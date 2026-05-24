<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image',
        'ingredients',
        'price',
        'active',
        'is_quick_item',
        'category_id',
        'store_id',
        'order'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'active' => 'boolean',
        'is_quick_item' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
} 
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'document',
        'cover_image',
        'logo',
        'user_id',
        'counter_qr_code'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function tables(): HasMany
    {
        return $this->hasMany(Table::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function bankAccount(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(BankAccount::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    /**
     * Verifica se a loja tem garçons cadastrados
     */
    public function hasWaiters(): bool
    {
        return User::where('store_id', $this->id)
            ->whereHas('roles', function($query) {
                $query->where('name', 'waiter');
            })
            ->exists();
    }
} 
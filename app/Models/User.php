<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    const DELETED_AT = 'DeletionDate';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'store_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Loja que o usuário possui (para lojistas)
     */
    public function store(): HasOne
    {
        return $this->hasOne(Store::class);
    }

    /**
     * Loja onde o funcionário trabalha
     */
    public function workplace(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Verifica se o usuário é funcionário de cozinha
     */
    public function isKitchen(): bool
    {
        return $this->hasRole('kitchen');
    }

    /**
     * Verifica se o usuário é garçom
     */
    public function isWaiter(): bool
    {
        return $this->hasRole('waiter');
    }

    /**
     * Verifica se o usuário é funcionário (cozinha ou garçom)
     */
    public function isEmployee(): bool
    {
        return $this->hasAnyRole(['kitchen', 'waiter']);
    }
}

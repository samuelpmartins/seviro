<?php

namespace App\Models;

/**
 * @method \Illuminate\Database\Eloquent\Relations\MorphMany notifications()
 * @method \Illuminate\Database\Eloquent\Relations\MorphMany unreadNotifications()
 */

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Notifications\DatabaseNotification as DatabaseNotification;
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
        'first_access',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'first_access' => 'boolean',
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
     * Relação das notificações armazenadas no banco (database notifications).
     * Adicionada para satisfazer analisadores estáticos e fornecer tipagem.
     *
     * @return MorphMany
     */
    public function notifications(): MorphMany
    {
        return $this->morphMany(DatabaseNotification::class, 'notifiable')->orderBy('created_at', 'desc');
    }

    /**
     * Notificações não lidas (helper para queries).
     *
     * @return MorphMany
     */
    public function unreadNotifications(): MorphMany
    {
        return $this->notifications()->whereNull('read_at');
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

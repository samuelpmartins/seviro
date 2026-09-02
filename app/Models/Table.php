<?php

namespace App\Models;

use App\Enums\TableServiceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Table extends Model
{
    use HasFactory, SoftDeletes;

    const DELETED_AT = 'DeletionDate';

    protected $fillable = [
        'number',
        'qr_code',
        'password',
        'occupied',
        'store_id',
        'last_activity',
        'current_user_id',
        'current_user_name',
        'occupied_at'
    ];

    protected $casts = [
        'occupied' => 'boolean',
        'last_activity' => 'datetime',
        'occupied_at' => 'datetime'
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function currentUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_user_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(TableParticipant::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function tableUsers(): HasMany
    {
        return $this->hasMany(TableUser::class);
    }

    public function activeTableUser(): HasOne
    {
        return $this->hasOne(TableUser::class)
            ->where('service_status', TableServiceStatus::Active->value);
    }

    /**
     * Verifica se todos os pedidos da mesa foram pagos
     * Se sim, desocupa a mesa automaticamente
     * 
     * @return bool Retorna true se a mesa foi desocupada
     */
    public function checkAndClearIfFullyPaid(): bool
    {
        // Buscar todos os participantes ativos da mesa
        $activeParticipants = $this->participants()->get();

        // Se não há participantes ativos, verificar se há pedidos pendentes na mesa
        if ($activeParticipants->isEmpty()) {
            // Verificar se há algum pedido pendente na mesa (sem participante ou de participantes removidos)
            $hasPendingOrders = $this->orders()
                ->where('payment_status', Order::PAYMENT_STATUS_PENDING)
                ->exists();

            // Se não há pedidos pendentes e a mesa está ocupada, desocupar
            if (!$hasPendingOrders && $this->occupied) {
                $this->clearTable();
                return true;
            }

            return false;
        }

        // Buscar os IDs dos participantes ativos
        $activeParticipantIds = $activeParticipants->pluck('id')->toArray();

        // Verificar se há algum pedido pendente de pagamento dos participantes ativos
        $pendingOrdersFromParticipants = $this->orders()
            ->whereIn('participant_id', $activeParticipantIds)
            ->where('payment_status', Order::PAYMENT_STATUS_PENDING)
            ->exists();

        // Se ainda há pedidos pendentes dos participantes ativos, não desocupa
        if ($pendingOrdersFromParticipants) {
            return false;
        }

        // Verificar se há pedidos sem participant_id (pedidos antigos ou do sistema antigo)
        $pendingOrdersWithoutParticipant = $this->orders()
            ->whereNull('participant_id')
            ->where('payment_status', Order::PAYMENT_STATUS_PENDING)
            ->exists();

        // Se há pedidos sem participante pendentes, não desocupa
        if ($pendingOrdersWithoutParticipant) {
            return false;
        }

        // Todos os pedidos foram pagos - desocupar a mesa
        $this->clearTable();

        return true;
    }

    /**
     * Limpa a mesa, removendo participantes e marcando como desocupada
     */
    public function clearTable(): void
    {
        // Encerra a atribuição ativa e a soft-deleta, liberando a mesa do garçom por completo.
        $this->activeTableUser()->get()->each(function ($assignment) {
            $assignment->update(['service_status' => TableServiceStatus::Finished]);
            $assignment->delete();
        });

        // Remover todos os participantes
        $this->participants()->get()->each->delete();

        // Marcar mesa como desocupada e remover senha
        $this->update([
            'occupied' => false,
            'current_user_id' => null,
            'current_user_name' => null,
            'last_activity' => now(),
            'password' => null, // Remover senha ao desocupar
        ]);
    }
}

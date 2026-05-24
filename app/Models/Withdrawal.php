<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Withdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'amount',
        'commission_amount',
        'commission_percentage',
        'net_amount',
        'status',
        'requested_at',
        'approved_at',
        'completed_at',
        'bank_account_id',
        'pix_key_used',
        'bank_data_used',
        'approved_by',
        'admin_notes',
        'rejection_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
        'bank_data_used' => 'array',
    ];

    /**
     * Status disponíveis
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Relacionamento com Store
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Relacionamento com BankAccount
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    /**
     * Relacionamento com User (admin que aprovou)
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Calcula a comissão baseado na configuração
     */
    public static function calculateCommission(float $amount): array
    {
        $commissionType = config('services.withdrawal.commission_type', 'percentage');
        $commissionPercentage = config('services.withdrawal.commission_percentage', 5.0);
        $commissionFixed = config('services.withdrawal.commission_fixed', 0);

        if ($commissionType === 'fixed') {
            $commissionAmount = $commissionFixed;
            $percentage = 0;
        } else {
            $commissionAmount = ($amount * $commissionPercentage) / 100;
            $percentage = $commissionPercentage;
        }

        $netAmount = $amount - $commissionAmount;

        return [
            'commission_amount' => round($commissionAmount, 2),
            'commission_percentage' => round($percentage, 2),
            'net_amount' => round($netAmount, 2),
        ];
    }

    /**
     * Aprova o saque
     */
    public function approve(int $adminId, ?string $notes = null): bool
    {
        return DB::transaction(function () use ($adminId, $notes) {
            return $this->update([
                'status' => self::STATUS_APPROVED,
                'approved_by' => $adminId,
                'approved_at' => now(),
                'admin_notes' => $notes,
            ]);
        });
    }

    /**
     * Rejeita o saque
     */
    public function reject(int $adminId, string $reason): bool
    {
        return DB::transaction(function () use ($adminId, $reason) {
            return $this->update([
                'status' => self::STATUS_REJECTED,
                'approved_by' => $adminId,
                'rejection_reason' => $reason,
                'approved_at' => now(),
            ]);
        });
    }

    /**
     * Marca como completado
     */
    public function complete(?string $notes = null): bool
    {
        return DB::transaction(function () use ($notes) {
            $updates = [
                'status' => self::STATUS_COMPLETED,
                'completed_at' => now(),
            ];

            if ($notes) {
                $updates['admin_notes'] = $this->admin_notes 
                    ? $this->admin_notes . "\n\n" . $notes 
                    : $notes;
            }

            return $this->update($updates);
        });
    }

    /**
     * Cancela o saque
     */
    public function cancel(): bool
    {
        return $this->update(['status' => self::STATUS_CANCELLED]);
    }

    /**
     * Verifica se está pendente
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Verifica se foi aprovado
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Verifica se foi rejeitado
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Verifica se foi completado
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Retorna o nome do status formatado
     */
    public function getStatusNameAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pendente',
            self::STATUS_APPROVED => 'Aprovado',
            self::STATUS_REJECTED => 'Rejeitado',
            self::STATUS_COMPLETED => 'Completado',
            self::STATUS_CANCELLED => 'Cancelado',
            default => $this->status,
        };
    }

    /**
     * Retorna a classe CSS da badge do status
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'bg-warning text-dark',
            self::STATUS_APPROVED => 'bg-info text-white',
            self::STATUS_REJECTED => 'bg-danger',
            self::STATUS_COMPLETED => 'bg-success',
            self::STATUS_CANCELLED => 'bg-secondary',
            default => 'bg-secondary',
        };
    }

    /**
     * Scope para filtrar por status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope para filtrar pendentes
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope para filtrar aprovados
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope para filtrar completados
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }
}

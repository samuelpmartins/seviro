<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    use HasFactory, SoftDeletes;

    const DELETED_AT = 'DeletionDate';

    protected $fillable = [
        'store_id',
        'pix_key',
        'pix_key_type',
        'bank_code',
        'bank_name',
        'agency',
        'account_number',
        'account_digit',
        'account_type',
        'account_holder_name',
        'account_holder_document',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Tipos de chave PIX disponíveis
     */
    const PIX_TYPE_CPF = 'cpf';
    const PIX_TYPE_CNPJ = 'cnpj';
    const PIX_TYPE_EMAIL = 'email';
    const PIX_TYPE_PHONE = 'phone';
    const PIX_TYPE_RANDOM = 'random';

    /**
     * Tipos de conta disponíveis
     */
    const ACCOUNT_TYPE_CHECKING = 'checking';
    const ACCOUNT_TYPE_SAVINGS = 'savings';

    /**
     * Relacionamento com Store
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Relacionamento com Withdrawals
     */
    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    /**
     * Verifica se tem dados PIX cadastrados
     */
    public function hasPixData(): bool
    {
        return !empty($this->pix_key) && !empty($this->pix_key_type);
    }

    /**
     * Verifica se tem dados bancários completos
     */
    public function hasBankData(): bool
    {
        return !empty($this->bank_code)
            && !empty($this->agency)
            && !empty($this->account_number)
            && !empty($this->account_holder_name);
    }

    /**
     * Verifica se tem pelo menos um tipo de dado (PIX ou bancário)
     */
    public function hasAnyData(): bool
    {
        return $this->hasPixData() || $this->hasBankData();
    }

    /**
     * Retorna os dados bancários formatados como JSON para histórico
     */
    public function getBankDataForHistoryAttribute(): array
    {
        return [
            'pix_key' => $this->pix_key,
            'pix_key_type' => $this->pix_key_type,
            'bank_code' => $this->bank_code,
            'bank_name' => $this->bank_name,
            'agency' => $this->agency,
            'account_number' => $this->account_number,
            'account_digit' => $this->account_digit,
            'account_type' => $this->account_type,
            'account_holder_name' => $this->account_holder_name,
            'account_holder_document' => $this->account_holder_document,
        ];
    }

    /**
     * Retorna o nome do tipo de conta formatado
     */
    public function getAccountTypeNameAttribute(): string
    {
        return match ($this->account_type) {
            self::ACCOUNT_TYPE_CHECKING => 'Conta Corrente',
            self::ACCOUNT_TYPE_SAVINGS => 'Conta Poupança',
            default => '-',
        };
    }

    /**
     * Retorna o nome do tipo de chave PIX formatado
     */
    public function getPixKeyTypeNameAttribute(): string
    {
        return match ($this->pix_key_type) {
            self::PIX_TYPE_CPF => 'CPF',
            self::PIX_TYPE_CNPJ => 'CNPJ',
            self::PIX_TYPE_EMAIL => 'E-mail',
            self::PIX_TYPE_PHONE => 'Telefone',
            self::PIX_TYPE_RANDOM => 'Chave Aleatória',
            default => '-',
        };
    }
}

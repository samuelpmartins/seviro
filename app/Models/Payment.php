<?php

namespace App\Models;

use App\Events\OrderPaid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'table_id',
        'stripe_payment_id',
        'stripe_payment_intent_id',
        'payment_method',
        'amount',
        'status',
        'order_ids',
        'paid_by_participant_id',
        'marked_by_user_id',
        'cash_received',
        'change_given',
        'notes',
        'pix_qr_code',
        'pix_code',
        'expires_at',
    ];

    protected $casts = [
        'order_ids' => 'array',
        'amount' => 'decimal:2',
        'cash_received' => 'decimal:2',
        'change_given' => 'decimal:2',
        'expires_at' => 'datetime',
    ];

    /**
     * Status de pagamento disponíveis
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SUCCEEDED = 'succeeded';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELED = 'canceled';

    /**
     * Métodos de pagamento disponíveis
     */
    const METHOD_CARD = 'card';
    const METHOD_PIX = 'pix';
    const METHOD_CASH = 'cash';

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function paidByParticipant(): BelongsTo
    {
        return $this->belongsTo(TableParticipant::class, 'paid_by_participant_id');
    }

    public function markedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by_user_id');
    }

    /**
     * Retorna os pedidos associados a este pagamento
     */
    public function getOrdersAttribute()
    {
        if (empty($this->order_ids)) {
            return collect();
        }
        
        return Order::whereIn('id', $this->order_ids)->get();
    }

    /**
     * Verifica se o pagamento foi bem sucedido
     */
    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_SUCCEEDED;
    }

    /**
     * Verifica se o pagamento está pendente
     */
    public function isPending(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }

    /**
     * Verifica se o PIX expirou
     */
    public function isPixExpired(): bool
    {
        if ($this->payment_method !== self::METHOD_PIX || !$this->expires_at) {
            return false;
        }
        
        return $this->expires_at->isPast();
    }

    /**
     * Marca os pedidos associados como pagos
     * Se o pedido estiver "Aguardando pagamento", muda para "Em produção"
     */
    public function markOrdersAsPaid(): void
    {
        $orders = Order::with('store', 'items.product')->whereIn('id', $this->order_ids)->get();
        
        foreach ($orders as $order) {
            $updateData = [
                'payment_status' => Order::PAYMENT_STATUS_PAID,
                'payment_method' => $this->payment_method,
            ];
            
            // Se o status for "Aguardando pagamento", muda para "Em produção"
            if ($order->status === 'Aguardando pagamento') {
                $updateData['status'] = 'Em produção';
            }
            
            $order->update($updateData);
            
            // Disparar evento de pedido pago
            event(new OrderPaid($order));
        }
        
        // Verificar se todos os pedidos da mesa foram pagos e desocupar se necessário
        if ($this->table_id) {
            $table = Table::find($this->table_id);
            if ($table) {
                $table->checkAndClearIfFullyPaid();
            }
        }
    }
}





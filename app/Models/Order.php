<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    const DELETED_AT = 'DeletionDate';

    protected $fillable = [
        'order_number',
        'store_id',
        'table_id',
        'user_id',
        'participant_id',
        'status',
        'payment_status',
        'payment_method',
        'total',
        'notes'
    ];

    protected $casts = [
        'total' => 'decimal:2'
    ];

    /**
     * Boot do modelo - garante que order_number seja sempre gerado
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            // Se não tiver order_number, gera automaticamente
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber($order->table_id ?? 0);
            }

            // Validação final: order_number não pode ser vazio
            if (empty($order->order_number)) {
                throw new \Exception('order_number não pode ser vazio');
            }
        });
    }

    /**
     * Status de pagamento disponíveis
     */
    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_PAID = 'paid';
    const PAYMENT_STATUS_PARTIAL = 'partial';

    /**
     * Métodos de pagamento disponíveis
     */
    const PAYMENT_METHOD_CARD = 'card';
    const PAYMENT_METHOD_PIX = 'pix';
    const PAYMENT_METHOD_CASH = 'cash';

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

    public function participant(): BelongsTo
    {
        return $this->belongsTo(TableParticipant::class, 'participant_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Verifica se o pedido está pago
     */
    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_PAID;
    }

    /**
     * Verifica se o pedido está pendente de pagamento
     */
    public function isPending(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_PENDING;
    }

    /**
     * Scope para pedidos pendentes de pagamento
     */
    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_STATUS_PENDING);
    }

    /**
     * Scope para pedidos pagos
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_STATUS_PAID);
    }

    /**
     * Verifica se é um pedido de balcão (sem mesa)
     */
    public function isCounterOrder(): bool
    {
        return $this->table_id === null;
    }

    /**
     * Retorna o nome da mesa ou "Balcão"
     */
    public function getTableDisplayName(): string
    {
        if ($this->isCounterOrder()) {
            return 'Balcão';
        }

        // Acessar relacionamento de forma explícita
        $tableRelation = $this->table()->first();

        return 'Mesa ' . ($tableRelation ? $tableRelation->number : 'N/A');
    }

    /**
     * Gera o número do pedido no formato: Número da Mesa + A + Ordem do Pedido
     * Exemplo: Mesa 6, segundo pedido = 06A02
     * Para pedidos de balcão: BAL + Ordem do Pedido = BALA01
     */
    public static function generateOrderNumber($tableId): string
    {
        // Use a tabela order_sequences para gerar um contador atômico por mesa (table_id)
        // tableId null ou 0 => contador para balcão
        $seqTableId = empty($tableId) ? null : $tableId;

        // Retry loop to handle concurrent inserts safely
        $maxAttempts = 5;
        $attempt = 0;

        do {
            try {
                return DB::transaction(function () use ($seqTableId) {
                    // Try to atomically increment existing row
                    $updated = DB::table('order_sequences')->where('table_id', $seqTableId)->increment('last_sequence', 1);

                    if ($updated > 0) {
                        // fetch the new value
                        $row = DB::table('order_sequences')->where('table_id', $seqTableId)->lockForUpdate()->first();
                        $next = intval($row->last_sequence);
                    } else {
                        // No row existed - attempt to insert starting with sequence 1
                        try {
                            $id = DB::table('order_sequences')->insertGetId([
                                'table_id' => $seqTableId,
                                'last_sequence' => 1,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);

                            $row = DB::table('order_sequences')->where('id', $id)->lockForUpdate()->first();
                            $next = intval($row->last_sequence);
                        } catch (\Exception $e) {
                            // Insert conflict - another process created the row; throw to outer transaction to retry
                            throw $e;
                        }
                    }

                    // Build order number
                    if (is_null($seqTableId)) {
                        $orderSequence = str_pad($next, 2, '0', STR_PAD_LEFT);
                        return 'BAL' . 'A' . $orderSequence;
                    }

                    $table = Table::find($seqTableId);
                    if (!$table) {
                        throw new \Exception('Mesa não encontrada');
                    }

                    $tableNumber = str_pad($table->number, 2, '0', STR_PAD_LEFT);
                    $orderSequence = str_pad($next, 2, '0', STR_PAD_LEFT);
                    // Prefix with store id to make order_number unique across stores
                    $storePrefix = str_pad($table->store_id, 2, '0', STR_PAD_LEFT);
                    return $storePrefix . '-' . $tableNumber . 'A' . $orderSequence;
                });
            } catch (\Exception $e) {
                // If a unique constraint on table_id happened or other concurrency issue, retry
                $attempt++;
                if ($attempt >= $maxAttempts) {
                    throw $e;
                }
                usleep(2000 * $attempt);
                continue;
            }
        } while ($attempt < $maxAttempts);

        throw new \Exception('Falha ao gerar order_number por causa de concorrência');
    }

    /**
     * Cria um pedido com tentativas para evitar colisões de `order_number`.
     * Em caso de constraint unique violada, refaz a geração do número e tenta novamente.
     *
     * @param array $attributes
     * @param int $maxAttempts
     * @return self
     * @throws \Exception
     */
    public static function createWithRetry(array $attributes, callable $callback = null, $maxAttempts = 5)
    {
        $attempt = 0;

        do {
            if (empty($attributes['order_number'])) {
                $attributes['order_number'] = self::generateOrderNumber($attributes['table_id'] ?? 0);
            }

            try {
                return DB::transaction(function () use ($attributes, $callback) {
                    $order = self::create($attributes);

                    if (is_callable($callback)) {
                        $callback($order);
                        // refresh to ensure relations are loaded if callback changed data
                        $order->refresh();
                    }

                    return $order;
                });
            } catch (QueryException $e) {
                $isDuplicate = ($e->getCode() == '23000') || str_contains($e->getMessage(), 'Duplicate entry');

                if ($isDuplicate && $attempt < $maxAttempts - 1) {
                    // Forçar nova geração no próximo loop
                    unset($attributes['order_number']);
                    $attempt++;
                    // small sleep to reduce tight-loop collisions (only in PHP CLI/web, microseconds)
                    usleep(1000 * $attempt);
                    continue;
                }

                throw $e;
            }
        } while ($attempt < $maxAttempts);

        throw new \Exception('Não foi possível criar pedido único após várias tentativas');
    }
}

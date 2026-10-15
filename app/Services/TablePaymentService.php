<?php

namespace App\Services;

use App\Events\OrderPaid;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentAdjustment;
use App\Models\Table;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TablePaymentService
{
    public function rules(): array
    {
        return [
            'include_service_fee' => ['required', 'boolean'],
            'adjustment_type' => ['nullable', 'in:' . PaymentAdjustment::TYPE_DISCOUNT . ',' . PaymentAdjustment::TYPE_SURCHARGE],
            'adjustment_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
        ];
    }

    /**
     * @param bool $currentOccupationOnly Garçom: só a ocupação atual. Lojista: todos os pendentes da mesa.
     */
    public function unpaidOrders(Table $table, bool $currentOccupationOnly, bool $lock = false)
    {
        $query = Order::where('table_id', $table->id)
            ->where('payment_status', Order::PAYMENT_STATUS_PENDING);

        if ($currentOccupationOnly) {
            $participantIds = $table->participants()->pluck('id');
            $query->where(function ($query) use ($participantIds) {
                if ($participantIds->isNotEmpty()) {
                    $query->whereIn('participant_id', $participantIds);
                }

                $query->orWhereNull('participant_id');
            });

            if ($table->occupied_at) {
                $query->where('created_at', '>=', $table->occupied_at);
            }
        } else {
            $query->where('store_id', $table->store_id);
        }

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->get();
    }

    public function calculate($orders, array $input): array
    {
        app(NegotiatedValueService::class)->assertOrdersHaveValues($orders);

        $consumption = round($orders->sum('total'), 2);
        $type = $input['adjustment_type'] ?? null;
        $amount = round((float) ($input['adjustment_amount'] ?? 0), 2);

        if (!$type || $amount <= 0) {
            $type = null;
            $amount = 0.0;
        }

        if ($type === PaymentAdjustment::TYPE_DISCOUNT && $amount > $consumption) {
            abort(422, 'O desconto não pode ser maior que o consumo.');
        }

        $adjustment = $type === PaymentAdjustment::TYPE_DISCOUNT ? -$amount : $amount;
        $subtotal = round($consumption + $adjustment, 2);
        $serviceFee = round($subtotal * 0.10, 2);
        $included = (bool) $input['include_service_fee'];

        return [
            'consumption' => $consumption,
            'adjustment_type' => $type,
            'adjustment' => $adjustment,
            'subtotal' => $subtotal,
            'service_fee' => $serviceFee,
            'service_fee_applied' => $included ? $serviceFee : 0,
            'total' => round($subtotal + ($included ? $serviceFee : 0), 2),
        ];
    }

    public function preview(Table $table, array $input, bool $currentOccupationOnly): array
    {
        $orders = $this->unpaidOrders($table, $currentOccupationOnly)->load('items');

        return $this->calculate($orders, $input) + [
            'orders_count' => $orders->count(),
            'items_count' => (int) $orders->sum(fn($order) => $order->items->sum('quantity')),
            'table_number' => $table->number,
            'participants' => $table->participants()->pluck('name')->values(),
        ];
    }

    /**
     * Monta o conteúdo no formato PrintOrder do Agent de impressão (conta da mesa).
     */
    public function receiptPrintOrder(Table $table, array $input, bool $currentOccupationOnly): array
    {
        $orders = $this->unpaidOrders($table, $currentOccupationOnly)->load('items.product');

        if ($orders->isEmpty()) {
            abort(422, 'Nenhum pedido pendente de pagamento nesta mesa.');
        }

        $calc = $this->calculate($orders, $input);
        $money = fn(float $value) => 'R$ ' . number_format($value, 2, ',', '.');

        $items = $orders->flatMap->items
            ->groupBy(fn($item) => $item->product->name ?? 'Item sem descricao')
            ->map(fn($group, $name) => [
                'Quantity' => (int) $group->sum('quantity'),
                'Description' => Str::ascii($name),
            ])
            ->values()
            ->all();

        $lines = ['Consumo: ' . $money($calc['consumption'])];
        if ($calc['adjustment_type']) {
            $lines[] = ($calc['adjustment'] < 0 ? 'Desconto: - ' : 'Acrescimo: + ') . $money(abs($calc['adjustment']));
            $lines[] = 'Subtotal: ' . $money($calc['subtotal']);
        }
        if ($calc['service_fee_applied'] > 0) {
            $lines[] = 'Taxa de servico (10%): ' . $money($calc['service_fee_applied']);
        }
        $lines[] = 'TOTAL: ' . $money($calc['total']);

        return [
            'NameRestaurant' => Str::ascii($table->store->name ?? 'Restaurante'),
            'OrderNumber' => 'Conta - Mesa ' . Str::ascii((string) $table->number),
            'CreatedAt' => now()->toIso8601String(),
            'Items' => $items,
            'Observations' => [
                'Title' => 'Resumo da conta',
                'Description' => implode("\n", $lines),
            ],
        ];
    }

    /**
     * @param callable $guard Recebe a mesa já bloqueada e deve abortar se o usuário não puder operá-la.
     */
    public function pay(Table $table, User $user, array $input, bool $currentOccupationOnly, callable $guard): Payment
    {
        return DB::transaction(function () use ($table, $user, $input, $currentOccupationOnly, $guard) {
            $lockedTable = Table::whereKey($table->id)->lockForUpdate()->firstOrFail();
            $guard($lockedTable);
            $orders = $this->unpaidOrders($lockedTable, $currentOccupationOnly, true);

            if ($orders->isEmpty()) {
                abort(422, 'Nenhum pedido pendente de pagamento nesta mesa.');
            }

            $calc = $this->calculate($orders, $input);
            $consumption = $calc['consumption'];

            $payment = Payment::create([
                'store_id' => $lockedTable->store_id,
                'table_id' => $lockedTable->id,
                'payment_method' => Payment::METHOD_CASH,
                'amount' => $calc['total'],
                'service_fee_included' => $input['include_service_fee'],
                'service_fee' => $calc['service_fee_applied'],
                'service_fee_percentage' => $input['include_service_fee'] ? 10 : 0,
                'status' => Payment::STATUS_SUCCEEDED,
                'order_ids' => $orders->modelKeys(),
                'marked_by_user_id' => $user->id,
            ]);

            $shares = [];
            if ($calc['adjustment_type']) {
                PaymentAdjustment::create([
                    'store_id' => $lockedTable->store_id,
                    'table_id' => $lockedTable->id,
                    'payment_id' => $payment->id,
                    'user_id' => $user->id,
                    'type' => $calc['adjustment_type'],
                    'amount' => abs($calc['adjustment']),
                    'base_total' => $consumption,
                    'adjusted_total' => $calc['subtotal'],
                    'order_ids' => $orders->modelKeys(),
                ]);

                // Rateio proporcional; o último pedido absorve o resto de centavos.
                $remaining = $calc['adjustment'];
                $lastId = $orders->last()->id;
                foreach ($orders as $order) {
                    $share = $order->id === $lastId
                        ? round($remaining, 2)
                        : round($calc['adjustment'] * ((float) $order->total / $consumption), 2);
                    $remaining -= $share;
                    $shares[$order->id] = $share;
                }
            }

            foreach ($orders as $order) {
                $order->update([
                    'total' => round((float) $order->total + ($shares[$order->id] ?? 0), 2),
                    'payment_status' => Order::PAYMENT_STATUS_PAID,
                    'payment_method' => Payment::METHOD_CASH,
                    'paid_at' => now(),
                    'status' => 'Finalizado',
                ]);
                $order->load('store', 'items.product', 'participant');
                event(new OrderPaid($order));
            }

            return $payment;
        });
    }
}

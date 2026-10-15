<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemValue;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class NegotiatedValueService
{
    /**
     * Bloqueia o pagamento de pedidos com item "valor a combinar" ainda sem valor lançado.
     */
    public function assertOrdersHaveValues($orders): void
    {
        $orders->loadMissing('items.product');

        $hasMissing = $orders->contains(fn(Order $order) => $order->items->contains(
            fn(OrderItem $item) => $item->product?->price_to_negotiate && $item->line_total <= 0
        ));

        if ($hasMissing) {
            abort(422, 'Há item com valor a combinar sem valor lançado. Lance o valor antes de pagar.');
        }
    }

    /**
     * Grava o valor integral do item e reflete a diferença no total do pedido.
     */
    public function setValue(OrderItem $item, float $amount, User $user): OrderItem
    {
        return DB::transaction(function () use ($item, $amount, $user) {
            $order = Order::whereKey($item->order_id)->lockForUpdate()->firstOrFail();
            $item = OrderItem::with('product')->findOrFail($item->id);

            abort_unless($item->product?->price_to_negotiate, 422, 'Este item não é de valor a combinar.');
            abort_unless($order->payment_status === Order::PAYMENT_STATUS_PENDING, 422, 'Este pedido já foi pago.');

            $amount = round($amount, 2);
            abort_unless($amount > 0, 422, 'Informe um valor maior que zero.');

            $previous = round($item->line_total, 2);

            $item->update([
                'price' => round($amount / $item->quantity, 2),
                'negotiated_total' => $amount,
            ]);

            $order->update(['total' => round((float) $order->total - $previous + $amount, 2)]);

            OrderItemValue::create([
                'store_id' => $order->store_id,
                'order_id' => $order->id,
                'order_item_id' => $item->id,
                'table_id' => $order->table_id,
                'product_id' => $item->product_id,
                'user_id' => $user->id,
                'previous_amount' => $previous,
                'amount' => $amount,
            ]);

            return $item;
        });
    }
}

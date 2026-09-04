<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Table;
use App\Models\TableUser;
use App\Models\User;
use App\Enums\TableServiceStatus;
use App\Events\OrderCreated;
use App\Events\OrderStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'table_qr_code' => 'required|string',
            'items' => 'required|array',
            'items.*.product_id' => ['required', Rule::exists('products', 'id')->whereNull('DeletionDate')],
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        // Obter usuário
        $user = auth()->user();

        // Tentar encontrar uma mesa com o QR code
        $table = \App\Models\Table::where('qr_code', $request->table_qr_code)->first();

        if ($table) {
            // É uma mesa
            $store = $table->store;
            $tableId = $table->id;
        } else {
            // Não é uma mesa, verificar se é QR code de balcão
            $store = \App\Models\Store::where('counter_qr_code', $request->table_qr_code)->first();

            if (!$store) {
                return response()->json([
                    'message' => 'QR Code não encontrado'
                ], 404);
            }

            // É um pedido de balcão
            $table = null;
            $tableId = null;
        }

        // Obter o participant_id da sessão (se existir - apenas para mesas)
        $participantId = null;
        if ($table) {
            $participantId = $request->session()->get('table_' . $table->id . '_participant_id');
        }

        try {
            $total = 0;
            $items = [];

            foreach ($request->items as $item) {
                $product = \App\Models\Product::findOrFail($item['product_id']);
                $subtotal = $product->price * $item['quantity'];
                $total += $subtotal;

                $items[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'notes' => $item['notes'] ?? null
                ];
            }

            // Definir status inicial baseado em ter garçons ou não
            $initialStatus = $store->hasWaiters() ? 'Aguardando produção' : 'Aguardando pagamento';

            // Criar o pedido com retry em caso de colisão no campo único
            $order = \App\Models\Order::createWithRetry([
                'store_id' => $store->id,
                'table_id' => $tableId,
                'user_id' => $user ? $user->id : null,
                'participant_id' => $participantId,
                'status' => $initialStatus,
                'payment_status' => 'pending',
                'total' => $total,
                'notes' => $request->notes
            ], function ($order) use ($items, $request) {
                // Criar itens dentro da mesma transação
                foreach ($items as $item) {
                    $order->items()->create($item);
                }

                if ($order->table_id) {
                    $assignment = TableUser::where('table_id', $order->table_id)
                        ->where('service_status', TableServiceStatus::Active->value)
                        ->latest('id')
                        ->first();

                    if ($assignment) {
                        \App\Models\OrderAttendance::create([
                            'store_id' => $order->store_id,
                            'order_id' => $order->id,
                            'table_id' => $order->table_id,
                            'participant_id' => $order->participant_id,
                            'waiter_id' => $assignment->user_id,
                        ]);
                    }
                }

                // Armazenar ID do pedido na sessão para rastreamento de notificações
                $sessionOrderIds = $request->session()->get('client_order_ids', []);
                $sessionOrderIds[] = $order->id;
                $request->session()->put('client_order_ids', array_unique($sessionOrderIds));

                // Disparar evento de pedido criado
                event(new OrderCreated($order));
            });

            return response()->json([
                'message' => 'Pedido criado com sucesso!',
                'order' => $order->load('items.product')
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json([
                'message' => 'Erro ao criar pedido: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        $request->validate([
            'status' => 'required|in:Aguardando pagamento,Aguardando produção,Em produção,Finalizado,Cancelado'
        ]);

        $oldStatus = $order->status;
        $order->update(['status' => $request->status]);

        // Disparar evento de mudança de status
        event(new OrderStatusChanged($order, $oldStatus, $request->status));

        // Se for uma requisição AJAX, retorna JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message' => 'Status do pedido atualizado com sucesso!',
                'order' => $order
            ]);
        }

        // Se for um formulário normal, redireciona com mensagem
        return redirect()->back()
            ->with('success', 'Status do pedido atualizado com sucesso!');
    }

    public function cancel(Order $order)
    {
        $this->authorize('delete', $order);

        $order->delete();

        // Se for uma requisição AJAX, retorna JSON
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'message' => 'Pedido cancelado com sucesso!'
            ]);
        }

        // Se for um formulário normal, redireciona com mensagem
        return redirect()->route('store.dashboard')
            ->with('success', 'Pedido cancelado com sucesso!');
    }

    /**
     * Exibir pedidos em produção
     */
    public function production()
    {
        $store = auth()->user()->store;

        $orders = $store->orders()
            ->where('status', 'Em produção')
            ->with(['table', 'items.product'])
            ->orderBy('created_at', 'asc')
            ->get();

        return view('store.orders_production', compact('orders'));
    }

    /**
     * Exibir histórico de pedidos
     */
    public function history(Request $request)
    {
        $store = auth()->user()->store;
        $tables = $store->tables;
        $waiters = User::where('store_id', $store->id)
            ->whereHas('roles', fn($query) => $query->where('name', 'waiter')->where('guard_name', 'web'))
            ->orderBy('name')
            ->get();

        $ordersQuery = $store->orders()
            ->with(['table', 'items.product', 'participant', 'attendance.waiter', 'attendance.participant']);

        // Filtros
        if ($request->filled('status')) {
            $ordersQuery->where('status', $request->status);
        }

        if ($request->filled('table_id')) {
            $ordersQuery->where('table_id', $request->table_id);
        }

        if ($request->filled('waiter_id')) {
            $ordersQuery->whereHas('attendance', function ($query) use ($request) {
                $query->where('waiter_id', $request->waiter_id);
            });
        }

        if ($request->filled('start_date')) {
            $ordersQuery->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $ordersQuery->whereDate('created_at', '<=', $request->end_date);
        }

        $orders = $ordersQuery->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('store.orders_history', compact('orders', 'tables', 'waiters'));
    }

    /**
     * Exibir histórico de pedidos do usuário logado
     */
    public function userHistory(Request $request)
    {
        $user = auth()->user();

        $ordersQuery = Order::where('user_id', $user->id)
            ->with(['store', 'table', 'items.product']);

        // Filtros
        if ($request->filled('status')) {
            $ordersQuery->where('status', $request->status);
        }

        if ($request->filled('start_date')) {
            $ordersQuery->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $ordersQuery->whereDate('created_at', '<=', $request->end_date);
        }

        $orders = $ordersQuery->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('orders.history', compact('orders'));
    }

    /**
     * Marcar pedido como pago em dinheiro
     */
    public function markAsPaidCash(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        // Atualizar o pedido
        $order->update([
            'payment_status' => Order::PAYMENT_STATUS_PAID,
            'payment_method' => 'cash'
        ]);

        // Atualiza o status direto para finalizado
        $order->update(['status' => 'Finalizado']);

        // Se for uma requisição AJAX, retorna JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message' => 'Pedido marcado como pago!',
                'order' => $order
            ]);
        }

        // Se for um formulário normal, redireciona com mensagem
        return redirect()->back()
            ->with('success', 'Pedido marcado como pago em dinheiro!');
    }
}

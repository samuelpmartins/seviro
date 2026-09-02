<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Table;
use App\Models\TableUser;
use App\Models\Product;
use App\Enums\TableServiceStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    /**
     * Listar todos os funcionários da loja
     */
    public function index()
    {
        $store = auth()->user()->store;

        $employees = User::where('store_id', $store->id)
            ->with('roles')
            ->orderBy('name')
            ->get();

        return view('store.employees.index', compact('employees'));
    }

    /**
     * Cadastrar novo funcionário
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:kitchen,waiter'
        ], [
            'name.required' => 'O nome é obrigatório.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Digite um endereço de e-mail válido.',
            'email.unique' => 'Este e-mail já está cadastrado no sistema.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter pelo menos 6 caracteres.',
            'role.required' => 'A função é obrigatória.',
            'role.in' => 'Função inválida.',
        ]);

        $store = auth()->user()->store;

        // Criar o usuário
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'store_id' => $store->id
        ]);

        // Garantir que as roles existam
        if (!Role::where('name', 'kitchen')->exists()) {
            Role::create(['name' => 'kitchen', 'guard_name' => 'web']);
        }
        if (!Role::where('name', 'waiter')->exists()) {
            Role::create(['name' => 'waiter', 'guard_name' => 'web']);
        }

        // Atribuir a role
        $user->assignRole($request->role);

        return redirect()->route('store.employees.index')
            ->with('success', 'Funcionário cadastrado com sucesso!');
    }

    /**
     * Atualizar funcionário
     */
    public function update(Request $request, User $employee)
    {
        $store = auth()->user()->store;

        // Verificar se o funcionário pertence à loja
        if ($employee->store_id !== $store->id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $employee->id,
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:kitchen,waiter'
        ], [
            'name.required' => 'O nome é obrigatório.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Digite um endereço de e-mail válido.',
            'email.unique' => 'Este e-mail já está cadastrado no sistema.',
            'password.min' => 'A senha deve ter pelo menos 6 caracteres.',
            'role.required' => 'A função é obrigatória.',
            'role.in' => 'Função inválida.',
        ]);

        $employee->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $employee->update(['password' => Hash::make($request->password)]);
        }

        // Atualizar role
        $employee->syncRoles([$request->role]);

        return redirect()->route('store.employees.index')
            ->with('success', 'Funcionário atualizado com sucesso!');
    }

    /**
     * Excluir funcionário
     */
    public function destroy(User $employee)
    {
        $store = auth()->user()->store;

        // Verificar se o funcionário pertence à loja
        if ($employee->store_id !== $store->id) {
            abort(403);
        }

        $employee->delete();

        return redirect()->route('store.employees.index')
            ->with('success', 'Funcionário excluído com sucesso!');
    }

    // ========== TELAS DA COZINHA ==========

    /**
     * Dashboard da cozinha - pedidos em produção e aguardando
     */
    public function kitchenDashboard()
    {
        $user = auth()->user();
        $store = $user->workplace;

        if (!$store) {
            return redirect()->route('login')->with('error', 'Funcionário não vinculado a nenhuma loja.');
        }

        $orders = Order::where('store_id', $store->id)
            ->whereIn('status', ['Aguardando pagamento', 'Aguardando produção', 'Em produção'])
            ->with(['table', 'items.product', 'participant', 'user'])
            ->orderBy('created_at', 'asc')
            ->get();

        return view('kitchen.dashboard', compact('orders', 'store'));
    }

    /**
     * Retorna o HTML parcial com a grid de pedidos (usado para polling via AJAX)
     */
    public function kitchenOrdersPartial()
    {
        $user = auth()->user();
        $store = $user->workplace;

        if (!$store) {
            return response('', 204);
        }

        $orders = Order::where('store_id', $store->id)
            ->whereIn('status', ['Aguardando pagamento', 'Aguardando produção', 'Em produção'])
            ->with(['table', 'items.product', 'participant', 'user'])
            ->orderBy('created_at', 'asc')
            ->get();

        return view('kitchen._orders_grid', compact('orders'));
    }

    /**
     * Atualizar status do pedido pela cozinha
     */
    public function kitchenUpdateStatus(Request $request, Order $order)
    {
        $user = auth()->user();
        $store = $user->workplace;

        // Verificar se o pedido pertence à loja do funcionário
        if ($order->store_id !== $store->id) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:Aguardando produção,Em produção,Finalizado'
        ]);

        if ($request->status === 'Em produção' && !in_array($order->status, ['Aguardando pagamento', 'Aguardando produção'])) {
            return redirect()->back()->withErrors(['status' => 'Este pedido não está aguardando o início da produção.']);
        }

        if ($request->status === 'Finalizado' && $order->status !== 'Em produção') {
            return redirect()->back()->withErrors(['status' => 'O pedido precisa estar em produção antes de ser finalizado.']);
        }

        $oldStatus = $order->status;
        $order->update(['status' => $request->status]);

        // Disparar evento de mudança de status
        event(new \App\Events\OrderStatusChanged($order, $oldStatus, $request->status));

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Status atualizado!']);
        }

        return redirect()->back()->with('success', 'Status do pedido atualizado!');
    }

    /**
     * Atualiza os itens de um pedido enquanto a cozinha ainda não iniciou a produção.
     */
    public function waiterUpdateOrder(Request $request, Order $order)
    {
        $store = auth()->user()->workplace;

        if (!$store || $order->store_id !== $store->id) {
            abort(403);
        }

        if ($order->table_id) {
            $this->ensureWaiterCanManageTable(Table::findOrFail($order->table_id), auth()->user());
        }

        if ($order->status !== 'Aguardando produção') {
            return response()->json([
                'success' => false,
                'message' => 'Este pedido não pode mais ser editado porque a produção já foi iniciada.'
            ], 422);
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string',
            'notes' => 'nullable|string',
            'items.*.selected_ingredients' => 'nullable|array',
            'items.*.selected_ingredients.*.id' => 'required_with:items.*.selected_ingredients|integer|exists:product_ingredients,id',
            'items.*.selected_ingredients.*.selected_amount' => 'required_with:items.*.selected_ingredients|integer|min:0',
        ]);

        $products = Product::where('store_id', $store->id)
            ->where('active', true)
            ->with('additionalIngredients')
            ->whereIn('id', collect($validated['items'])->pluck('product_id'))
            ->get()
            ->keyBy('id');

        $hasUnavailableProduct = collect($validated['items'])
            ->contains(fn($item) => !$products->has($item['product_id']));

        if ($hasUnavailableProduct) {
            return response()->json([
                'success' => false,
                'message' => 'Um ou mais produtos não estão disponíveis nesta loja.'
            ], 422);
        }

        DB::transaction(function () use ($order, $validated, $products) {
            $total = 0;

            $order->items()->get()->each->delete();
            foreach ($validated['items'] as $item) {
                $product = $products->get($item['product_id']);
                $unitPrice = (float) $product->price;
                $selectedIngredients = [];

                foreach ($item['selected_ingredients'] ?? [] as $selectedIngredient) {
                    $ingredient = $product->additionalIngredients->firstWhere('id', $selectedIngredient['id']);
                    if (!$ingredient) {
                        continue;
                    }

                    $baseAmount = (int) $ingredient->amount_item;
                    $selectedAmount = (int) $selectedIngredient['selected_amount'];
                    $difference = $selectedAmount - $baseAmount;
                    if ($difference > 0) {
                        $unitPrice += $difference * (float) $ingredient->additional_price;
                    }

                    $selectedIngredients[] = [
                        'id' => $ingredient->id,
                        'name' => $ingredient->name,
                        'baseAmount' => $baseAmount,
                        'selectedAmount' => $selectedAmount,
                        'diff' => $difference,
                    ];
                }

                $total += $unitPrice * $item['quantity'];

                $itemNotes = $item['notes'] ?? null;
                if ($selectedIngredients) {
                    $added = collect($selectedIngredients)->filter(fn($ingredient) => $ingredient['diff'] > 0)
                        ->map(fn($ingredient) => ['name' => $ingredient['name'], 'diff' => $ingredient['diff']])->all();
                    $removed = collect($selectedIngredients)->filter(fn($ingredient) => $ingredient['diff'] < 0)
                        ->map(fn($ingredient) => ['name' => $ingredient['name'], 'diff' => abs($ingredient['diff'])])->all();
                    $summary = collect([
                        $added ? 'Adicionados: ' . collect($added)->map(fn($ingredient) => $ingredient['name'] . ' x' . $ingredient['diff'])->implode('; ') : null,
                        $removed ? 'Removidos: ' . collect($removed)->map(fn($ingredient) => $ingredient['name'] . ' x' . $ingredient['diff'])->implode('; ') : null,
                    ])->filter()->implode(' | ');
                    $itemNotes = collect([$itemNotes, $summary])->filter()->implode(' | ') ?: null;
                }

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $unitPrice,
                    'notes' => json_encode([
                        'notes' => $itemNotes,
                        'selected_ingredients' => collect($selectedIngredients)->map(fn($ingredient) => [
                            'id' => $ingredient['id'],
                            'selected_amount' => $ingredient['selectedAmount'],
                        ])->values()->all(),
                    ], JSON_UNESCAPED_UNICODE),
                ]);
            }

            $order->update([
                'total' => $total,
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Pedido atualizado com sucesso.'
        ]);
    }

    // ========== TELAS DO GARÇOM ==========

    /**
     * Dashboard do garçom - visão das mesas
     */
    public function waiterDashboard()
    {
        $user = auth()->user();
        $store = $user->workplace;

        if (!$store) {
            return redirect()->route('login')->with('error', 'Funcionário não vinculado a nenhuma loja.');
        }

        $tables = Table::where('store_id', $store->id)
            ->with([
                'participants.pins' => fn($query) => $query
                    ->where('status', 'active')
                    ->latest(),
                'activeTableUser.user',
            ])
            ->orderBy('number')
            ->get();

        // Para cada mesa, buscar pedidos e informações
        foreach ($tables as $table) {
            // Buscar IDs dos participantes ativos da mesa
            $activeParticipantIds = $table->participants->pluck('id')->toArray();

            // Se a mesa está ocupada e tem participantes, mostrar apenas pedidos da sessão atual
            // Se a mesa está desocupada (sem participantes), não mostrar pedidos
            if (!empty($activeParticipantIds)) {
                $table->orders = Order::where('table_id', $table->id)
                    ->whereIn('status', ['Aguardando pagamento', 'Aguardando produção', 'Em produção'])
                    ->where(function ($query) use ($activeParticipantIds) {
                        $query->whereIn('participant_id', $activeParticipantIds)
                            ->orWhereNull('participant_id');
                    })
                    ->with(['table', 'items.product', 'participant'])
                    ->orderBy('created_at', 'desc')
                    ->get();
            } else {
                // Mesa desocupada - sem pedidos a mostrar
                $table->orders = collect();
            }

            $table->unpaid_total = $table->orders->where('payment_status', 'pending')->sum('total');
            $owner = $table->participants->firstWhere('is_owner', true);
            $table->access_pin = $owner?->pins->first()?->pin;
        }

        $myTables = $tables->filter(function ($table) use ($user) {
            return $table->activeTableUser && $table->activeTableUser->user_id === $user->id;
        })->values();

        $otherTables = $tables->filter(function ($table) use ($user) {
            return !$table->activeTableUser || $table->activeTableUser->user_id !== $user->id;
        })->values();

        $availableProducts = Product::where('store_id', $store->id)
            ->where('active', true)
            ->with('additionalIngredients')
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'ingredients']);

        return view('waiter.dashboard', compact('myTables', 'otherTables', 'store', 'availableProducts'));
    }

    public function startAttending(Request $request)
    {
        $request->user()->update(['is_attending' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Atendimento iniciado.',
        ]);
    }

    public function stopAttending(Request $request)
    {
        $request->user()->update(['is_attending' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Atendimento encerrado.',
        ]);
    }

    /**
     * Histórico de pedidos para o garçom
     */
    public function waiterHistory(Request $request)
    {
        $user = auth()->user();
        $store = $user->workplace;

        if (!$store) {
            return redirect()->route('login')->with('error', 'Funcionário não vinculado a nenhuma loja.');
        }

        $tables = Table::where('store_id', $store->id)->orderBy('number')->get();

        $ordersQuery = Order::where('store_id', $store->id)
            ->with(['table', 'items.product', 'participant']);

        // Filtros
        if ($request->filled('status')) {
            $ordersQuery->where('status', $request->status);
        }

        if ($request->filled('table_id')) {
            $ordersQuery->where('table_id', $request->table_id);
        }

        if ($request->filled('start_date')) {
            $ordersQuery->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $ordersQuery->whereDate('created_at', '<=', $request->end_date);
        }

        $orders = $ordersQuery->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('waiter.history', compact('orders', 'tables', 'store'));
    }

    /**
     * Ver detalhes da mesa (garçom)
     */
    public function waiterTableDetails(Table $table)
    {
        $user = auth()->user();
        $store = $user->workplace;

        // Verificar se a mesa pertence à loja do funcionário
        if ($table->store_id !== $store->id) {
            abort(403);
        }

        $participants = $table->participants;

        // Buscar IDs dos participantes ativos da mesa
        $activeParticipantIds = $participants->pluck('id')->toArray();

        // Se a mesa tem participantes ativos, mostrar apenas pedidos da sessão atual
        // Se não tem participantes, não mostrar pedidos
        if (!empty($activeParticipantIds)) {
            $orders = Order::where('table_id', $table->id)
                ->whereIn('status', ['Aguardando pagamento', 'Aguardando produção', 'Em produção'])
                ->where(function ($query) use ($activeParticipantIds) {
                    $query->whereIn('participant_id', $activeParticipantIds)
                        ->orWhereNull('participant_id');
                })
                ->with(['table', 'items.product', 'participant'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $orders = collect();
        }

        return view('waiter.table-details', compact('table', 'orders', 'participants', 'store'));
    }

    /**
     * Desocupar mesa (garçom)
     */
    public function waiterClearTable(Table $table)
    {
        $user = auth()->user();
        $store = $user->workplace;

        $this->ensureWaiterCanManageTable($table, $user);
        $table->clearTable();

        return redirect()->route('waiter.dashboard')
            ->with('success', 'Mesa ' . $table->number . ' desocupada com sucesso!');
    }

    public function markAsPaidCash(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        if ($order->table_id) {
            $this->ensureWaiterCanManageTable(Table::findOrFail($order->table_id), $request->user());
        }

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

    private function ensureWaiterCanManageTable(Table $table, User $user): void
    {
        $store = $user->workplace;

        if (!$store || $table->store_id !== $store->id) {
            abort(403);
        }

        $assignment = TableUser::where('table_id', $table->id)
            ->where('service_status', TableServiceStatus::Active->value)
            ->first();

        if (!$assignment || $assignment->user_id !== $user->id) {
            abort(403);
        }
    }
}

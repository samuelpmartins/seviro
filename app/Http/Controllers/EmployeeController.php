<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Table;
use App\Models\TableUser;
use App\Models\Product;
use App\Models\Payment;
use App\Services\TablePaymentService;
use App\Models\OrderAttendance;
use App\Models\TableParticipant;
use App\Models\TableParticipantPin;
use App\Enums\TableAssignmentType;
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
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['kitchen', 'waiter'])
                    ->where('guard_name', 'web');
            })
            ->with('roles')
            ->orderBy('name')
            ->get();

        return view('store.employees.index', compact('employees'));
    }

    public function waiterServiceFees(Request $request, User $employee)
    {
        $store = auth()->user()->store;
        abort_unless($store && $employee->store_id === $store->id && $employee->hasRole('waiter'), 403);

        $validated = $request->validate([
            'start_date' => ['nullable', 'required_with:end_date', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'required_with:start_date', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ]);

        if (!empty($validated['start_date'])) {
            $periodStart = \Carbon\Carbon::createFromFormat('Y-m-d', $validated['start_date'])->startOfDay();
            $periodEnd = \Carbon\Carbon::createFromFormat('Y-m-d', $validated['end_date'])->endOfDay();
        } else {
            $periodStart = now()->startOfMonth()->startOfDay();
            $periodEnd = now()->endOfMonth()->endOfDay();
        }

        $payments = Payment::where('store_id', $store->id)
            ->where('marked_by_user_id', $employee->id)
            ->where('status', Payment::STATUS_SUCCEEDED)
            ->where('service_fee_included', true)
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->get(['order_ids', 'service_fee']);

        $attendedOrderCount = $payments
            ->flatMap(fn(Payment $payment) => $payment->order_ids ?? [])
            ->unique()
            ->count();
        $serviceFeeTotal = $payments->sum(fn(Payment $payment) => (float) $payment->service_fee);
        $startDate = $periodStart->toDateString();
        $endDate = $periodEnd->toDateString();
        $periodLabel = $periodStart->isSameDay($periodEnd)
            ? $periodStart->format('d/m/Y')
            : $periodStart->format('d/m/Y') . ' a ' . $periodEnd->format('d/m/Y');

        return view('store.employees.service-fees', compact(
            'employee',
            'attendedOrderCount',
            'serviceFeeTotal',
            'startDate',
            'endDate',
            'periodLabel'
        ));
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
        Role::firstOrCreate(['name' => 'kitchen', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'waiter', 'guard_name' => 'web']);

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
        if ($employee->store_id !== $store->id || !$employee->hasAnyRole(['kitchen', 'waiter'])) {
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
        if ($employee->store_id !== $store->id || !$employee->hasAnyRole(['kitchen', 'waiter'])) {
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

            // Itens de valor a combinar mantêm o valor lançado se produto e quantidade não mudaram.
            $negotiated = $order->items()->whereNotNull('negotiated_total')->get()->groupBy('product_id');

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

                $negotiatedItem = $product->price_to_negotiate
                    ? $negotiated->get($product->id)?->firstWhere('quantity', (int) $item['quantity'])
                    : null;

                if ($negotiatedItem) {
                    $negotiated->put($product->id, $negotiated->get($product->id)->reject(fn($old) => $old->id === $negotiatedItem->id));
                    $unitPrice = (float) $negotiatedItem->price;
                    $total += (float) $negotiatedItem->negotiated_total;
                } else {
                    $total += $unitPrice * $item['quantity'];
                }

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
                    'negotiated_total' => $negotiatedItem?->negotiated_total,
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
                    ->whereIn('status', ['Aguardando pagamento', 'Aguardando produção', 'Em produção', 'Finalizado'])
                    ->when($table->occupied_at, fn($query) => $query->where('created_at', '>=', $table->occupied_at))
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

        $availableTables = $tables->filter(function ($table) {
            return !$table->occupied
                && $table->participants->isEmpty()
                && !$table->activeTableUser;
        })->values();

        $availableProducts = Product::where('store_id', $store->id)
            ->where('active', true)
            ->with('additionalIngredients')
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'ingredients']);

        return view('waiter.dashboard', compact('myTables', 'otherTables', 'availableTables', 'store', 'availableProducts'));
    }

    public function waiterOccupyTable(Request $request, Table $table)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);
        $user = $request->user();
        $store = $user->workplace;

        if (!$store || $table->store_id !== $store->id) {
            abort(403);
        }

        $pin = DB::transaction(function () use ($table, $user, $validated) {
            $lockedTable = Table::whereKey($table->id)->lockForUpdate()->firstOrFail();

            if ($lockedTable->occupied || $lockedTable->participants()->exists()) {
                abort(422, 'Esta mesa não está disponível para ocupação.');
            }

            $assignment = TableUser::where('table_id', $lockedTable->id)
                ->where('service_status', TableServiceStatus::Active->value)
                ->lockForUpdate()
                ->first();

            if ($assignment && $assignment->user_id !== $user->id) {
                abort(403, 'Esta mesa está atribuída a outro garçom.');
            }

            $participant = TableParticipant::create([
                'table_id' => $lockedTable->id,
                'name' => $validated['name'],
                'is_owner' => true,
            ]);

            $pin = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            TableParticipantPin::create([
                'table_participant_id' => $participant->id,
                'pin' => $pin,
                'status' => 'active',
                'next_validate' => now()->addSeconds(10),
            ]);

            if (!$assignment) {
                TableUser::create([
                    'store_id' => $lockedTable->store_id,
                    'table_id' => $lockedTable->id,
                    'user_id' => $user->id,
                    'service_status' => TableServiceStatus::Active,
                    'assignment_type' => TableAssignmentType::Manual,
                ]);
            }

            $lockedTable->update([
                'occupied' => true,
                'occupied_at' => now(),
            ]);

            return $pin;
        });

        return response()->json([
            'success' => true,
            'message' => 'Mesa ocupada e participante criado com sucesso.',
            'pin' => $pin,
        ]);
    }

    public function waiterTableParticipants(Request $request, Table $table)
    {
        $this->ensureWaiterCanManageTable($table, $request->user());

        abort_unless($table->occupied, 422, 'Esta mesa não está ocupada.');

        return response()->json([
            'participants' => $table->participants()
                ->orderBy('name')
                ->get(['id', 'name', 'is_owner']),
        ]);
    }

    public function waiterAddParticipant(Request $request, Table $table)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);
        $user = $request->user();
        $store = $user->workplace;

        if (!$store || $table->store_id !== $store->id) {
            abort(403);
        }

        $participant = DB::transaction(function () use ($table, $user, $validated) {
            $lockedTable = Table::whereKey($table->id)->lockForUpdate()->firstOrFail();
            $this->ensureWaiterCanManageTable($lockedTable, $user);

            abort_unless($lockedTable->occupied, 422, 'Esta mesa não está ocupada.');

            if ($lockedTable->participants()->where('name', $validated['name'])->exists()) {
                abort(422, 'Já existe um participante com esse nome nesta mesa.');
            }

            return TableParticipant::create([
                'table_id' => $lockedTable->id,
                'name' => $validated['name'],
                'is_owner' => false,
            ]);
        });

        return response()->json([
            'participant' => [
                'id' => $participant->id,
                'name' => $participant->name,
                'is_owner' => $participant->is_owner,
            ],
        ], 201);
    }

    public function waiterCreateOrder(Request $request, Table $table)
    {
        $validated = $request->validate([
            'participant_id' => ['required', 'integer', 'exists:table_participants,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.notes' => ['nullable', 'string'],
            'items.*.selected_ingredients' => ['nullable', 'array'],
            'items.*.selected_ingredients.*.id' => ['required_with:items.*.selected_ingredients', 'integer', 'exists:product_ingredients,id'],
            'items.*.selected_ingredients.*.selected_amount' => ['required_with:items.*.selected_ingredients', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);
        $user = $request->user();
        $store = $user->workplace;

        if (!$store || $table->store_id !== $store->id) {
            abort(403);
        }

        $order = DB::transaction(function () use ($table, $user, $store, $validated) {
            $lockedTable = Table::whereKey($table->id)->lockForUpdate()->firstOrFail();
            $this->ensureWaiterCanManageTable($lockedTable, $user);

            abort_unless($lockedTable->occupied, 422, 'Esta mesa não está ocupada.');

            $participant = TableParticipant::whereKey($validated['participant_id'])
                ->where('table_id', $lockedTable->id)
                ->lockForUpdate()
                ->first();

            abort_unless($participant, 422, 'O participante selecionado não pertence a esta mesa.');

            $products = Product::where('store_id', $store->id)
                ->where('active', true)
                ->whereIn('id', collect($validated['items'])->pluck('product_id')->unique())
                ->with('additionalIngredients')
                ->get()
                ->keyBy('id');

            $itemsToCreate = [];
            $total = 0;
            $onlyQuickItems = true;

            foreach ($validated['items'] as $item) {
                $product = $products->get($item['product_id']);
                abort_unless($product, 422, 'Um ou mais produtos não estão disponíveis nesta loja.');

                $unitPrice = (float) $product->price;
                $selectedIngredients = [];

                foreach ($item['selected_ingredients'] ?? [] as $selectedIngredient) {
                    $ingredient = $product->additionalIngredients->firstWhere('id', $selectedIngredient['id']);
                    abort_unless($ingredient, 422, 'Um ingrediente selecionado não pertence ao produto.');

                    $baseAmount = (int) $ingredient->amount_item;
                    $selectedAmount = (int) $selectedIngredient['selected_amount'];
                    $difference = $selectedAmount - $baseAmount;
                    if ($difference > 0) {
                        $unitPrice += $difference * (float) $ingredient->additional_price;
                    }

                    $selectedIngredients[] = [
                        'id' => $ingredient->id,
                        'selected_amount' => $selectedAmount,
                    ];
                }

                $total += $unitPrice * (int) $item['quantity'];
                $itemsToCreate[] = [
                    'product_id' => $product->id,
                    'quantity' => (int) $item['quantity'],
                    'price' => $unitPrice,
                    'notes' => json_encode([
                        'notes' => $item['notes'] ?? null,
                        'selected_ingredients' => $selectedIngredients,
                    ], JSON_UNESCAPED_UNICODE),
                ];
                $onlyQuickItems = $onlyQuickItems && (bool) $product->is_quick_item;
            }

            $hasWaiters = $store->hasWaiters();
            $initialStatus = $onlyQuickItems
                ? ($hasWaiters ? 'Aguardando produção' : 'Finalizado')
                : ($hasWaiters ? 'Aguardando produção' : 'Aguardando pagamento');

            return Order::createWithRetry([
                'store_id' => $store->id,
                'table_id' => $lockedTable->id,
                'participant_id' => $participant->id,
                'status' => $initialStatus,
                'payment_status' => Order::PAYMENT_STATUS_PENDING,
                'total' => $total,
                'notes' => $validated['notes'] ?? null,
            ], function (Order $order) use ($itemsToCreate, $user, $participant) {
                foreach ($itemsToCreate as $item) {
                    $order->items()->create($item);
                }

                OrderAttendance::create([
                    'store_id' => $order->store_id,
                    'order_id' => $order->id,
                    'table_id' => $order->table_id,
                    'participant_id' => $participant->id,
                    'waiter_id' => $user->id,
                ]);

                $order->load('store');
                event(new \App\Events\OrderCreated($order, true));
            });
        });

        return response()->json([
            'success' => true,
            'message' => 'Pedido lançado e enviado para a cozinha.',
            'order' => $order->load('items.product'),
        ], 201);
    }

    public function startAttending(Request $request)
    {
        $request->user()->update(['is_attending' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Atendimento iniciado.',
        ]);
    }

    public function attendanceStatus(Request $request)
    {
        $user = User::query()->findOrFail($request->user()->id);

        return response()->json([
            'success' => true,
            'is_attending' => (bool) $user->is_attending,
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    public function stopAttending(Request $request)
    {
        $user = User::query()->findOrFail($request->user()->id);
        $wasAttending = (bool) $user->is_attending;
        $user->update(['is_attending' => false]);

        return response()->json([
            'success' => true,
            'was_attending' => $wasAttending,
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
            ->whereHas('attendance', function ($query) use ($user) {
                $query->where('waiter_id', $user->id);
            })
            ->with(['table', 'items.product', 'participant', 'attendance.waiter']);

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
                ->whereIn('status', ['Aguardando pagamento', 'Aguardando produção', 'Em produção', 'Finalizado'])
                ->when($table->occupied_at, fn($query) => $query->where('created_at', '>=', $table->occupied_at))
                ->where(function ($query) use ($activeParticipantIds) {
                    $query->whereIn('participant_id', $activeParticipantIds)
                        ->orWhereNull('participant_id');
                })
                ->with(['table', 'items.product.additionalIngredients', 'participant'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $orders = collect();
        }

        return view('waiter.table-details', compact('table', 'orders', 'participants', 'store'));
    }

    public function setOrderItemValue(Request $request, \App\Models\OrderItem $item)
    {
        $validated = $request->validate(['amount' => ['required', 'numeric', 'min:0.01', 'max:9999999']]);
        $order = Order::findOrFail($item->order_id);
        $store = $request->user()->workplace;

        abort_unless($store && $order->store_id === $store->id, 403);

        if ($order->table_id) {
            $this->ensureWaiterCanManageTable(Table::findOrFail($order->table_id), $request->user());
        }

        app(\App\Services\NegotiatedValueService::class)
            ->setValue($item, (float) $validated['amount'], $request->user());

        return response()->json(['success' => true, 'message' => 'Valor lançado com sucesso.']);
    }

    public function previewTableServiceFee(Request $request, Table $table)
    {
        $payments = app(TablePaymentService::class);
        $validated = $request->validate($payments->rules());
        $this->ensureWaiterCanManageTable($table, $request->user());

        return response()->json($payments->preview($table, $validated, true));
    }

    public function printTableBill(Request $request, Table $table)
    {
        $payments = app(TablePaymentService::class);
        $validated = $request->validate($payments->rules());
        $this->ensureWaiterCanManageTable($table, $request->user());

        $kitchenStaff = User::where('store_id', $table->store_id)
            ->whereHas('roles', fn($query) => $query->where('name', 'kitchen'))
            ->get();

        if ($kitchenStaff->isEmpty()) {
            abort(422, 'Nenhuma cozinha cadastrada para esta loja.');
        }

        $printOrder = $payments->receiptPrintOrder($table, $validated, true);
        \Illuminate\Support\Facades\Notification::send(
            $kitchenStaff,
            new \App\Notifications\PrintRequested($printOrder, $request->user()->name)
        );

        return response()->json(['success' => true, 'message' => 'Conta enviada para impressão na cozinha.']);
    }

    public function markTableAsPaid(Request $request, Table $table)
    {
        $payments = app(TablePaymentService::class);
        $validated = $request->validate($payments->rules());
        $this->ensureWaiterCanManageTable($table, $request->user());

        $payment = $payments->pay(
            $table,
            $request->user(),
            $validated,
            true,
            fn(Table $locked) => $this->ensureWaiterCanManageTable($locked, $request->user())
        );

        return response()->json([
            'success' => true,
            'message' => 'Pagamento registrado com sucesso.',
            'payment_id' => $payment->id,
            'amount' => $payment->amount,
            'service_fee' => $payment->service_fee,
        ]);
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

    /**
     * Transfere a ocupação atual para uma mesa livre.
     */
    public function waiterTransferTable(Request $request, Table $table)
    {
        $validated = $request->validate([
            'target_table_id' => ['required', 'integer'],
        ]);
        $user = $request->user();

        $this->ensureWaiterCanManageTable($table, $user);

        DB::transaction(function () use ($table, $validated, $user) {
            $source = Table::whereKey($table->id)->lockForUpdate()->firstOrFail();
            $target = Table::whereKey($validated['target_table_id'])
                ->where('store_id', $source->store_id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$source->occupied) {
                abort(422, 'A mesa atual já está desocupada.');
            }

            if ($source->id === $target->id) {
                abort(422, 'A mesa de destino deve ser diferente da mesa atual.');
            }

            $sourceAssignment = TableUser::where('table_id', $source->id)
                ->where('service_status', TableServiceStatus::Active->value)
                ->lockForUpdate()
                ->first();

            if (!$sourceAssignment || $sourceAssignment->user_id !== $user->id) {
                abort(403);
            }

            $targetHasParticipants = $target->participants()->exists();
            $targetHasAssignment = TableUser::where('table_id', $target->id)
                ->where('service_status', TableServiceStatus::Active->value)
                ->lockForUpdate()
                ->exists();

            if ($target->occupied || $targetHasParticipants || $targetHasAssignment) {
                abort(422, 'A mesa selecionada não está disponível.');
            }

            $occupiedAt = $source->occupied_at;
            $participantIds = $source->participants()->pluck('id');
            $orderQuery = Order::where('table_id', $source->id)
                ->where(function ($query) use ($participantIds) {
                    $query->whereIn('participant_id', $participantIds)
                        ->orWhereNull('participant_id');
                });

            if ($occupiedAt) {
                $orderQuery->where('created_at', '>=', $occupiedAt);
            }

            $orderIds = $orderQuery->pluck('id');

            $source->participants()->update(['table_id' => $target->id]);
            $orderQuery->update(['table_id' => $target->id]);

            $paymentQuery = Payment::where('table_id', $source->id);
            if ($occupiedAt) {
                $paymentQuery->where('created_at', '>=', $occupiedAt);
            }
            $paymentQuery->update(['table_id' => $target->id]);

            if ($orderIds->isNotEmpty()) {
                OrderAttendance::whereIn('order_id', $orderIds)
                    ->update(['table_id' => $target->id]);
            }

            $sourceAssignment->update(['service_status' => TableServiceStatus::Transferred]);
            $sourceAssignment->delete();

            TableUser::create([
                'store_id' => $source->store_id,
                'table_id' => $target->id,
                'user_id' => $user->id,
                'service_status' => TableServiceStatus::Active,
                'assignment_type' => TableAssignmentType::Transfer,
            ]);

            $target->update([
                'occupied' => true,
                'current_user_id' => $source->current_user_id,
                'current_user_name' => $source->current_user_name,
                'occupied_at' => $source->occupied_at,
                'last_activity' => now(),
                'password' => $source->password,
            ]);

            $source->update([
                'occupied' => false,
                'current_user_id' => null,
                'current_user_name' => null,
                'occupied_at' => null,
                'last_activity' => now(),
                'password' => null,
            ]);
        });

        return redirect()->route('waiter.dashboard')
            ->with('success', 'Ocupação transferida para a mesa selecionada com sucesso!');
    }

    public function markAsPaidCash(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        if ($order->table_id) {
            $this->ensureWaiterCanManageTable(Table::findOrFail($order->table_id), $request->user());
        }

        // Atualizar o pedido
        app(\App\Services\NegotiatedValueService::class)
            ->assertOrdersHaveValues(new \Illuminate\Database\Eloquent\Collection([$order]));

        $order->update([
            'payment_status' => Order::PAYMENT_STATUS_PAID,
            'payment_method' => 'cash',
            'paid_at' => now(),
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

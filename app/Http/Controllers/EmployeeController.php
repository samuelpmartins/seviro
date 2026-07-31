<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
            ->whereIn('status', ['Aguardando pagamento', 'Em produção'])
            ->with(['table', 'items.product', 'participant'])
            ->orderBy('created_at', 'asc')
            ->get();

        return view('kitchen.dashboard', compact('orders', 'store'));
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
            'status' => 'required|in:Em produção,Finalizado'
        ]);

        $oldStatus = $order->status;
        $order->update(['status' => $request->status]);

        // Disparar evento de mudança de status
        event(new \App\Events\OrderStatusChanged($order, $oldStatus, $request->status));

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Status atualizado!']);
        }

        return redirect()->back()->with('success', 'Status do pedido atualizado!');
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
            ->with(['participants'])
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
                    ->whereIn('status', ['Aguardando pagamento', 'Em produção'])
                    ->where(function ($query) use ($activeParticipantIds) {
                        $query->whereIn('participant_id', $activeParticipantIds)
                            ->orWhereNull('participant_id');
                    })
                    ->with(['items.product', 'participant'])
                    ->orderBy('created_at', 'desc')
                    ->get();
            } else {
                // Mesa desocupada - sem pedidos a mostrar
                $table->orders = collect();
            }

            $table->unpaid_total = $table->orders->where('payment_status', 'pending')->sum('total');
        }

        return view('waiter.dashboard', compact('tables', 'store'));
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
                ->whereIn('status', ['Aguardando pagamento', 'Em produção'])
                ->where(function ($query) use ($activeParticipantIds) {
                    $query->whereIn('participant_id', $activeParticipantIds)
                        ->orWhereNull('participant_id');
                })
                ->with(['items.product', 'participant'])
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

        // Verificar se a mesa pertence à loja do funcionário
        if ($table->store_id !== $store->id) {
            abort(403);
        }

        // Remover todos os participantes da mesa
        $table->participants()->get()->each->delete();

        // Resetar a mesa (incluindo a senha)
        $table->update([
            'occupied' => false,
            'current_user_id' => null,
            'current_user_name' => null,
            'occupied_at' => null,
            'password' => null
        ]);

        return redirect()->route('waiter.dashboard')
            ->with('success', 'Mesa ' . $table->number . ' desocupada com sucesso!');
    }

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

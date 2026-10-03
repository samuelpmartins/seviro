<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Store;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class StoreController extends Controller
{
    public function index()
    {
        $stores = Store::with('user')->latest()->paginate(10);
        return view('stores.index', compact('stores'));
    }

    public function create()
    {
        return view('stores.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'document' => 'required|string|max:20|unique:stores,document',
            'cover_image' => 'nullable|image|max:10240',
            'logo' => 'nullable|image|max:10240',
        ]);

        // Criar usuário
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $storeRole = Role::firstOrCreate(['name' => 'store', 'guard_name' => 'web']);
        $user->assignRole($storeRole);

        // Upload de imagens
        $coverPath = null;
        $logoPath = null;

        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')->store('covers', 'public');
        }

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        // Criar loja
        $store = Store::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'document' => $request->document,
            'cover_image' => $coverPath,
            'logo' => $logoPath,
            'user_id' => $user->id,
        ]);

        return redirect()->route('stores.index')
            ->with('success', 'Loja criada com sucesso!');
    }

    public function edit(Store $store = null)
    {
        // Se não foi passada uma loja, pega a do usuário logado
        if (!$store) {
            $store = auth()->user()->store;
        }

        return view('store.edit', compact('store'));
    }

    public function dashboard(Request $request)
    {
        $store = auth()->user()->store;

        $totalProducts = $store->products()->count();
        $totalCategories = $store->categories()->count();
        $totalTables = $store->tables()->count();
        $occupiedTables = $store->tables()->where('occupied', true)->count();

        $pendingOrders = $store->orders()
            ->where('payment_status', 'pending')
            ->with(['table', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Dados financeiros
        $today = now()->startOfDay();
        $validatedPeriod = $request->validate([
            'financial_start_date' => ['nullable', 'required_with:financial_end_date', 'date_format:Y-m-d'],
            'financial_end_date' => ['nullable', 'required_with:financial_start_date', 'date_format:Y-m-d', 'after_or_equal:financial_start_date'],
        ]);

        if (!empty($validatedPeriod['financial_start_date'])) {
            $periodStart = Carbon::createFromFormat('Y-m-d', $validatedPeriod['financial_start_date'])->startOfDay();
            $periodEnd = Carbon::createFromFormat('Y-m-d', $validatedPeriod['financial_end_date'])->endOfDay();
        } else {
            // Mantém compatibilidade com URLs antigas que filtram pelo mês.
            $selectedMonth = $request->input('financial_month', now()->format('Y-m'));
            if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $selectedMonth)) {
                $selectedMonth = now()->format('Y-m');
            }

            $periodStart = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth()->startOfDay();
            $periodEnd = $periodStart->copy()->endOfMonth()->endOfDay();
        }

        $selectedPeriodLabel = $periodStart->isSameDay($periodEnd)
            ? $periodStart->format('d/m/Y')
            : $periodStart->format('d/m/Y') . ' a ' . $periodEnd->format('d/m/Y');
        $financialStartDate = $periodStart->toDateString();
        $financialEndDate = $periodEnd->toDateString();

        $serviceFees = Payment::where('store_id', $store->id)
            ->where('status', Payment::STATUS_SUCCEEDED)
            ->where('service_fee_included', true);
        $todayServiceFees = (clone $serviceFees)->whereDate('created_at', $today)->sum('service_fee');
        $periodServiceFees = (clone $serviceFees)
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->sum('service_fee');
        $totalServiceFees = (clone $serviceFees)->sum('service_fee');

        // Vendas de hoje
        $todaySales = $store->orders()
            ->where('payment_status', 'paid')
            ->whereDate('created_at', $today)
            ->sum('total') + $todayServiceFees;

        // Vendas do período selecionado
        $periodSales = $store->orders()
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->sum('total') + $periodServiceFees;

        // Total de vendas (todos os tempos)
        $totalSales = $store->orders()
            ->where('payment_status', 'paid')
            ->sum('total') + $totalServiceFees;

        // Pedidos pagos hoje
        $todayOrders = $store->orders()
            ->where('payment_status', 'paid')
            ->whereDate('created_at', $today)
            ->count();

        // Pedidos pagos no período selecionado
        $periodOrders = $store->orders()
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->count();

        // Pedidos pendentes de pagamento
        $unpaidOrders = $store->orders()
            ->where('payment_status', 'pending')
            ->count();

        $unpaidTotal = $store->orders()
            ->where('payment_status', 'pending')
            ->sum('total');

        return view('store.dashboard', compact(
            'store',
            'totalProducts',
            'totalCategories',
            'totalTables',
            'occupiedTables',
            'pendingOrders',
            'todaySales',
            'periodSales',
            'totalSales',
            'todayOrders',
            'periodOrders',
            'financialStartDate',
            'financialEndDate',
            'selectedPeriodLabel',
            'unpaidOrders',
            'unpaidTotal'
        ));
    }

    public function update(Request $request, Store $store)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'document' => 'required|string|max:20|unique:stores,document,' . $store->id,
            'cover_image' => 'nullable|image|max:10240',
            'logo' => 'nullable|image|max:10240',
        ]);

        // Upload de imagens
        if ($request->hasFile('cover_image')) {
            if ($store->cover_image) {
                Storage::disk('public')->delete($store->cover_image);
            }
            $store->cover_image = $request->file('cover_image')->store('covers', 'public');
        }

        if ($request->hasFile('logo')) {
            if ($store->logo) {
                Storage::disk('public')->delete($store->logo);
            }
            $store->logo = $request->file('logo')->store('logos', 'public');
        }

        $store->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'document' => $request->document,
        ]);

        return redirect()->route('stores.index')
            ->with('success', 'Loja atualizada com sucesso!');
    }

    public function destroy(Store $store)
    {
        // Deletar imagens
        if ($store->cover_image) {
            Storage::disk('public')->delete($store->cover_image);
        }
        if ($store->logo) {
            Storage::disk('public')->delete($store->logo);
        }

        // Deletar usuário associado
        $store->user->delete();

        // Deletar loja
        $store->delete();

        return redirect()->route('stores.index')
            ->with('success', 'Loja excluída com sucesso!');
    }

    // Métodos para o painel da loja
    public function manage()
    {
        $store = auth()->user()->store;

        // Carregar dados para a tela de gerenciamento completa
        $categories = $store->categories()
            ->with(['products' => function ($query) {
                $query->orderBy('order')->with('additionalIngredients');
            }])
            ->orderBy('order')
            ->get();

        $tables = $store->tables()->get();

        $orders = $store->orders()
            ->with(['table', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('store.edit-restaurant', compact('store', 'categories', 'tables', 'orders'));
    }

    public function updateOwnStore(Request $request)
    {
        $store = auth()->user()->store;

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'document' => 'required|string|max:20|unique:stores,document,' . $store->id,
            'cover_image' => 'nullable|image|max:10240',
            'logo' => 'nullable|image|max:10240',
        ]);

        // Upload de imagens
        if ($request->hasFile('cover_image')) {
            if ($store->cover_image) {
                Storage::disk('public')->delete($store->cover_image);
            }
            $store->cover_image = $request->file('cover_image')->store('covers', 'public');
        }

        if ($request->hasFile('logo')) {
            if ($store->logo) {
                Storage::disk('public')->delete($store->logo);
            }
            $store->logo = $request->file('logo')->store('logos', 'public');
        }

        $store->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'document' => $request->document,
        ]);

        return redirect()->route('store.dashboard')
            ->with('success', 'Loja atualizada com sucesso!');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\User;
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

    public function dashboard()
    {
        $store = auth()->user()->store;

        $totalProducts = $store->products()->count();
        $totalCategories = $store->categories()->count();
        $totalTables = $store->tables()->count();
        $occupiedTables = $store->tables()->where('occupied', true)->count();

        $pendingOrders = $store->orders()
            ->whereNotIn('status', ['Pago'])
            ->with(['table', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Dados financeiros
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        // Vendas de hoje
        $todaySales = $store->orders()
            ->where('payment_status', 'paid')
            ->whereDate('created_at', $today)
            ->sum('total');

        // Vendas do mês
        $monthSales = $store->orders()
            ->where('payment_status', 'paid')
            ->whereDate('created_at', '>=', $thisMonth)
            ->sum('total');

        // Total de vendas (todos os tempos)
        $totalSales = $store->orders()
            ->where('payment_status', 'paid')
            ->sum('total');

        // Pedidos pagos hoje
        $todayOrders = $store->orders()
            ->where('payment_status', 'paid')
            ->whereDate('created_at', $today)
            ->count();

        // Pedidos pagos no mês
        $monthOrders = $store->orders()
            ->where('payment_status', 'paid')
            ->whereDate('created_at', '>=', $thisMonth)
            ->count();

        // Ticket médio
        $averageTicket = $monthOrders > 0 ? $monthSales / $monthOrders : 0;

        // Pedidos pendentes de pagamento
        $unpaidOrders = $store->orders()
            ->where('payment_status', 'pending')
            ->count();

        $unpaidTotal = $store->orders()
            ->where('payment_status', 'pending')
            ->sum('total');

        // Métodos de pagamento (últimos 30 dias)
        $paymentMethods = $store->orders()
            ->where('payment_status', 'paid')
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->selectRaw('payment_method, COUNT(*) as count, SUM(total) as total')
            ->groupBy('payment_method')
            ->get();

        return view('store.dashboard', compact(
            'store',
            'totalProducts',
            'totalCategories',
            'totalTables',
            'occupiedTables',
            'pendingOrders',
            'todaySales',
            'monthSales',
            'totalSales',
            'todayOrders',
            'monthOrders',
            'averageTicket',
            'unpaidOrders',
            'unpaidTotal',
            'paymentMethods'
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

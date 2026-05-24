<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\Withdrawal;
use Illuminate\Http\Request;

class WithdrawalAdminController extends Controller
{
    /**
     * Lista todas as solicitações de saque
     */
    public function index(Request $request)
    {
        $query = Withdrawal::with(['store', 'bankAccount', 'approvedBy'])
            ->orderBy('requested_at', 'desc');

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por restaurante
        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        // Filtro por data
        if ($request->filled('date_from')) {
            $query->whereDate('requested_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('requested_at', '<=', $request->date_to);
        }

        $withdrawals = $query->paginate(20);

        // Lista de restaurantes para o filtro
        $stores = Store::orderBy('name')->get();

        // Estatísticas
        $stats = [
            'pending_count' => Withdrawal::where('status', Withdrawal::STATUS_PENDING)->count(),
            'pending_amount' => Withdrawal::where('status', Withdrawal::STATUS_PENDING)->sum('net_amount'),
            'approved_count' => Withdrawal::where('status', Withdrawal::STATUS_APPROVED)->count(),
            'approved_amount' => Withdrawal::where('status', Withdrawal::STATUS_APPROVED)->sum('net_amount'),
        ];

        return view('admin.withdrawals.index', compact('withdrawals', 'stores', 'stats'));
    }

    /**
     * Exibe detalhes de uma solicitação
     */
    public function show(Withdrawal $withdrawal)
    {
        $withdrawal->load(['store', 'bankAccount', 'approvedBy']);

        return view('admin.withdrawals.show', compact('withdrawal'));
    }

    /**
     * Aprova uma solicitação de saque
     */
    public function approve(Request $request, Withdrawal $withdrawal)
    {
        if (!$withdrawal->isPending()) {
            return back()->with('error', 'Esta solicitação não pode ser aprovada pois não está pendente.');
        }

        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $success = $withdrawal->approve(auth()->id(), $request->admin_notes);

        if ($success) {
            return back()->with('success', 'Solicitação aprovada com sucesso! Agora você pode realizar a transferência bancária.');
        }

        return back()->with('error', 'Erro ao aprovar solicitação.');
    }

    /**
     * Rejeita uma solicitação de saque
     */
    public function reject(Request $request, Withdrawal $withdrawal)
    {
        if (!$withdrawal->isPending()) {
            return back()->with('error', 'Esta solicitação não pode ser rejeitada pois não está pendente.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $success = $withdrawal->reject(auth()->id(), $request->rejection_reason);

        if ($success) {
            return back()->with('success', 'Solicitação rejeitada.');
        }

        return back()->with('error', 'Erro ao rejeitar solicitação.');
    }

    /**
     * Marca como completada após transferência
     */
    public function complete(Request $request, Withdrawal $withdrawal)
    {
        if (!$withdrawal->isApproved()) {
            return back()->with('error', 'Esta solicitação precisa estar aprovada para ser completada.');
        }

        $request->validate([
            'completion_notes' => 'nullable|string|max:1000',
        ]);

        $success = $withdrawal->complete($request->completion_notes);

        if ($success) {
            return back()->with('success', 'Transferência confirmada! O saque foi marcado como completado.');
        }

        return back()->with('error', 'Erro ao completar solicitação.');
    }

    /**
     * Exibe o histórico completo
     */
    public function history(Request $request)
    {
        $query = Withdrawal::with(['store', 'bankAccount', 'approvedBy'])
            ->orderBy('requested_at', 'desc');

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por restaurante
        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        // Filtro por período
        if ($request->filled('period')) {
            switch ($request->period) {
                case 'today':
                    $query->whereDate('requested_at', today());
                    break;
                case 'week':
                    $query->whereBetween('requested_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('requested_at', now()->month)
                          ->whereYear('requested_at', now()->year);
                    break;
                case 'year':
                    $query->whereYear('requested_at', now()->year);
                    break;
            }
        }

        // Filtro por datas customizadas
        if ($request->filled('date_from')) {
            $query->whereDate('requested_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('requested_at', '<=', $request->date_to);
        }

        $withdrawals = $query->paginate(30);

        // Lista de restaurantes para o filtro
        $stores = Store::orderBy('name')->get();

        // Estatísticas gerais
        $stats = [
            'total_amount' => Withdrawal::sum('amount'),
            'total_commission' => Withdrawal::sum('commission_amount'),
            'total_paid' => Withdrawal::where('status', Withdrawal::STATUS_COMPLETED)->sum('net_amount'),
            'count_by_status' => [
                'pending' => Withdrawal::where('status', Withdrawal::STATUS_PENDING)->count(),
                'approved' => Withdrawal::where('status', Withdrawal::STATUS_APPROVED)->count(),
                'completed' => Withdrawal::where('status', Withdrawal::STATUS_COMPLETED)->count(),
                'rejected' => Withdrawal::where('status', Withdrawal::STATUS_REJECTED)->count(),
            ],
        ];

        return view('admin.withdrawals.history', compact('withdrawals', 'stores', 'stats'));
    }
}

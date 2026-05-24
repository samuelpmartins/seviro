<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Payment;
use App\Models\Store;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    /**
     * Exibe o formulário de dados bancários
     */
    public function showBankAccount()
    {
        $user = auth()->user();
        $store = $user->store;

        if (!$store) {
            return redirect()->route('store.dashboard')->with('error', 'Loja não encontrada.');
        }

        $bankAccount = $store->bankAccount;

        return view('store.bank-account', compact('store', 'bankAccount'));
    }

    /**
     * Salva/atualiza os dados bancários
     */
    public function storeBankAccount(Request $request)
    {
        $user = auth()->user();
        $store = $user->store;

        if (!$store) {
            return redirect()->route('store.dashboard')->with('error', 'Loja não encontrada.');
        }

        $request->validate([
            'pix_key' => 'nullable|string|max:255',
            'pix_key_type' => 'nullable|in:cpf,cnpj,email,phone,random',
            'bank_code' => 'nullable|string|max:10',
            'bank_name' => 'nullable|string|max:255',
            'agency' => 'nullable|string|max:20',
            'account_number' => 'nullable|string|max:20',
            'account_digit' => 'nullable|string|max:5',
            'account_type' => 'nullable|in:checking,savings',
            'account_holder_name' => 'nullable|string|max:255',
            'account_holder_document' => 'nullable|string|max:20',
        ]);

        // Verificar se tem pelo menos PIX ou dados bancários
        $hasPix = !empty($request->pix_key) && !empty($request->pix_key_type);
        $hasBankData = !empty($request->bank_code) && !empty($request->agency) 
                       && !empty($request->account_number) && !empty($request->account_holder_name);

        if (!$hasPix && !$hasBankData) {
            return back()->with('error', 'Preencha pelo menos os dados PIX ou os dados bancários completos.');
        }

        $bankAccount = $store->bankAccount()->updateOrCreate(
            ['store_id' => $store->id],
            $request->only([
                'pix_key',
                'pix_key_type',
                'bank_code',
                'bank_name',
                'agency',
                'account_number',
                'account_digit',
                'account_type',
                'account_holder_name',
                'account_holder_document',
            ])
        );

        return back()->with('success', 'Dados bancários salvos com sucesso!');
    }

    /**
     * Calcula o saldo disponível (API endpoint)
     */
    public function getBalance()
    {
        $user = auth()->user();
        $store = $user->store;

        if (!$store) {
            return response()->json(['error' => 'Loja não encontrada.'], 404);
        }

        $balance = $this->calculateBalance($store->id);

        return response()->json([
            'success' => true,
            'balance' => $balance,
        ]);
    }

    /**
     * Exibe o formulário de solicitação de saque
     */
    public function create()
    {
        $user = auth()->user();
        $store = $user->store;

        if (!$store) {
            return redirect()->route('store.dashboard')->with('error', 'Loja não encontrada.');
        }

        $bankAccount = $store->bankAccount;

        // Verificar se tem dados bancários cadastrados
        if (!$bankAccount || !$bankAccount->hasAnyData()) {
            return redirect()->route('store.bank-account')
                ->with('error', 'Você precisa cadastrar seus dados bancários antes de solicitar um saque.');
        }

        // Verificar se já tem saque pendente
        $hasPendingWithdrawal = $store->withdrawals()
            ->where('status', Withdrawal::STATUS_PENDING)
            ->exists();

        if ($hasPendingWithdrawal) {
            return redirect()->route('store.withdrawals.history')
                ->with('error', 'Você já tem uma solicitação de saque pendente. Aguarde a análise do administrador.');
        }

        $balance = $this->calculateBalance($store->id);
        $commissionInfo = config('services.withdrawal', [
            'commission_type' => 'percentage',
            'commission_percentage' => 5.0,
            'commission_fixed' => 0,
        ]);

        return view('store.withdrawals.create', compact('store', 'bankAccount', 'balance', 'commissionInfo'));
    }

    /**
     * Cria a solicitação de saque
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $store = $user->store;

        if (!$store) {
            return back()->with('error', 'Loja não encontrada.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $bankAccount = $store->bankAccount;

        if (!$bankAccount || !$bankAccount->hasAnyData()) {
            return back()->with('error', 'Você precisa cadastrar seus dados bancários antes de solicitar um saque.');
        }

        // Verificar se já tem saque pendente
        $hasPendingWithdrawal = $store->withdrawals()
            ->where('status', Withdrawal::STATUS_PENDING)
            ->exists();

        if ($hasPendingWithdrawal) {
            return back()->with('error', 'Você já tem uma solicitação de saque pendente.');
        }

        $balance = $this->calculateBalance($store->id);
        $amount = (float) $request->amount;

        if ($amount > $balance) {
            return back()->with('error', 'Saldo insuficiente. Saldo disponível: R$ ' . number_format($balance, 2, ',', '.'));
        }

        // Calcular comissão
        $commissionData = Withdrawal::calculateCommission($amount);

        DB::beginTransaction();
        try {
            $withdrawal = Withdrawal::create([
                'store_id' => $store->id,
                'amount' => $amount,
                'commission_amount' => $commissionData['commission_amount'],
                'commission_percentage' => $commissionData['commission_percentage'],
                'net_amount' => $commissionData['net_amount'],
                'status' => Withdrawal::STATUS_PENDING,
                'bank_account_id' => $bankAccount->id,
                'pix_key_used' => $bankAccount->pix_key,
                'bank_data_used' => $bankAccount->bankDataForHistory,
            ]);

            DB::commit();

            return redirect()->route('store.withdrawals.history')
                ->with('success', 'Solicitação de saque enviada com sucesso! Aguarde a análise do administrador.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erro ao criar solicitação de saque: ' . $e->getMessage());
            return back()->with('error', 'Erro ao criar solicitação de saque. Tente novamente.');
        }
    }

    /**
     * Exibe o histórico de saques do restaurante
     */
    public function history()
    {
        $user = auth()->user();
        $store = $user->store;

        if (!$store) {
            return redirect()->route('store.dashboard')->with('error', 'Loja não encontrada.');
        }

        $withdrawals = $store->withdrawals()
            ->with(['bankAccount', 'approvedBy'])
            ->orderBy('requested_at', 'desc')
            ->paginate(15);

        $balance = $this->calculateBalance($store->id);

        // Estatísticas
        $stats = [
            'total_requested' => $store->withdrawals()->sum('amount'),
            'total_completed' => $store->withdrawals()
                ->where('status', Withdrawal::STATUS_COMPLETED)
                ->sum('net_amount'),
            'total_pending' => $store->withdrawals()
                ->where('status', Withdrawal::STATUS_PENDING)
                ->sum('net_amount'),
        ];

        return view('store.withdrawals.history', compact('store', 'withdrawals', 'balance', 'stats'));
    }

    /**
     * Calcula o saldo disponível
     */
    private function calculateBalance(int $storeId): float
    {
        // Total recebido de pagamentos bem-sucedidos
        $totalReceived = Payment::where('store_id', $storeId)
            ->where('status', Payment::STATUS_SUCCEEDED)
            ->sum('amount');

        // Total de saques aprovados ou completados
        $totalWithdrawn = Withdrawal::where('store_id', $storeId)
            ->whereIn('status', [Withdrawal::STATUS_APPROVED, Withdrawal::STATUS_COMPLETED])
            ->sum('net_amount');

        // Total de saques pendentes (bloqueia o saldo)
        $totalPending = Withdrawal::where('store_id', $storeId)
            ->where('status', Withdrawal::STATUS_PENDING)
            ->sum('amount');

        return (float) ($totalReceived - $totalWithdrawn - $totalPending);
    }
}

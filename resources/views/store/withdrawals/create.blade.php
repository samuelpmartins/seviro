@extends('layouts.store-base')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-1">Solicitar Saque</h2>
                        <p class="text-muted mb-0">Informe o valor que deseja sacar</p>
                    </div>
                    <a href="{{ route('store.withdrawals.history') }}" class="btn btn-outline-secondary">
                        Ver Histórico
                    </a>
                </div>

                <!-- Card de Saldo -->
                <div class="card mb-4">
                    <div class="card-body text-center py-4">
                        <h6 class="text-muted mb-2">Saldo Disponível</h6>
                        <h2 class="mb-0 text-success">R$ {{ number_format($balance, 2, ',', '.') }}</h2>
                    </div>
                </div>

                <!-- Dados Bancários Cadastrados -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Dados Bancários Cadastrados</h5>
                        <a href="{{ route('store.bank-account') }}" class="btn btn-sm btn-outline-primary">
                            Editar
                        </a>
                    </div>
                    <div class="card-body">
                        @if ($bankAccount->hasPixData())
                            <div class="mb-3">
                                <strong>PIX:</strong><br>
                                <span class="text-muted">{{ $bankAccount->pixKeyTypeName }}:</span>
                                {{ $bankAccount->pix_key }}
                            </div>
                        @endif

                        @if ($bankAccount->hasBankData())
                            @if ($bankAccount->hasPixData())
                                <hr>
                            @endif
                            <div>
                                <strong>Dados Bancários:</strong><br>
                                <span class="text-muted">Banco:</span> {{ $bankAccount->bank_code }} -
                                {{ $bankAccount->bank_name }}<br>
                                <span class="text-muted">Agência:</span> {{ $bankAccount->agency }}<br>
                                <span class="text-muted">Conta:</span>
                                {{ $bankAccount->account_number }}-{{ $bankAccount->account_digit }}
                                ({{ $bankAccount->accountTypeName }})<br>
                                <span class="text-muted">Titular:</span> {{ $bankAccount->account_holder_name }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Formulário de Saque -->
                <form action="{{ route('store.withdrawals.store') }}" method="POST" id="withdrawalForm">
                    @csrf

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Valor do Saque</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Valor Bruto (R$)</label>
                                <input type="number" class="form-control form-control-lg" id="amount" name="amount"
                                    step="0.01" min="0.01" max="{{ $balance }}" value="{{ old('amount') }}"
                                    placeholder="0,00" required>
                                <small class="form-text text-muted">Valor máximo: R$
                                    {{ number_format($balance, 2, ',', '.') }}</small>
                            </div>

                            <!-- Cálculo de Comissão -->
                            <div id="calculationBox" style="display: none;">
                                <hr>
                                <div class="row">
                                    <div class="col-6">
                                        <p class="mb-1"><strong>Valor Bruto:</strong></p>
                                        <p class="text-muted" id="grossAmount">R$ 0,00</p>
                                    </div>
                                    <div class="col-6">
                                        <p class="mb-1"><strong>Comissão
                                                @if ($commissionInfo['commission_type'] === 'percentage')
                                                    ({{ number_format($commissionInfo['commission_percentage'], 1) }}%):
                                                @else
                                                    (Taxa Fixa)
                                                    :
                                                @endif
                                            </strong></p>
                                        <p class="text-danger" id="commissionAmount">R$ 0,00</p>
                                    </div>
                                </div>
                                <hr>
                                <div class="text-center">
                                    <h6 class="text-muted mb-1">Valor Líquido a Receber</h6>
                                    <h3 class="text-success mb-0" id="netAmount">R$ 0,00</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <strong>Importante:</strong> A solicitação passará por análise do administrador antes de ser
                        processada. Você será notificado sobre o status.
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('store.dashboard') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                            Solicitar Saque
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const amountInput = document.getElementById('amount');
            const calculationBox = document.getElementById('calculationBox');
            const submitBtn = document.getElementById('submitBtn');
            const grossAmountEl = document.getElementById('grossAmount');
            const commissionAmountEl = document.getElementById('commissionAmount');
            const netAmountEl = document.getElementById('netAmount');

            const commissionType = '{{ $commissionInfo['commission_type'] }}';
            const commissionPercentage = parseFloat('{{ $commissionInfo['commission_percentage'] }}');
            const commissionFixed = parseFloat('{{ $commissionInfo['commission_fixed'] }}');
            const maxBalance = parseFloat('{{ $balance }}');

            amountInput.addEventListener('input', function() {
                const amount = parseFloat(this.value) || 0;

                if (amount > 0 && amount <= maxBalance) {
                    let commissionAmount = 0;

                    if (commissionType === 'fixed') {
                        commissionAmount = commissionFixed;
                    } else {
                        commissionAmount = (amount * commissionPercentage) / 100;
                    }

                    const netAmount = amount - commissionAmount;

                    // Atualizar valores
                    grossAmountEl.textContent = 'R$ ' + amount.toFixed(2).replace('.', ',');
                    commissionAmountEl.textContent = '- R$ ' + commissionAmount.toFixed(2).replace('.',
                    ',');
                    netAmountEl.textContent = 'R$ ' + netAmount.toFixed(2).replace('.', ',');

                    // Mostrar cálculo
                    calculationBox.style.display = 'block';
                    submitBtn.disabled = false;
                } else {
                    calculationBox.style.display = 'none';
                    submitBtn.disabled = true;
                }
            });

            // Validação no submit
            document.getElementById('withdrawalForm').addEventListener('submit', function(e) {
                const amount = parseFloat(amountInput.value) || 0;

                if (amount <= 0) {
                    e.preventDefault();
                    alert('Digite um valor válido.');
                    return;
                }

                if (amount > maxBalance) {
                    e.preventDefault();
                    alert('O valor solicitado excede o saldo disponível.');
                    return;
                }

                if (!confirm('Confirma a solicitação de saque no valor de R$ ' + amount.toFixed(2).replace(
                        '.', ',') + '?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
@endsection

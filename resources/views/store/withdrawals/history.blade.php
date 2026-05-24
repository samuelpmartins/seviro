@extends('layouts.store-base')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">Histórico de Saques</h2>
                    <p class="text-muted mb-0">Acompanhe suas solicitações de saque</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('store.dashboard') }}" class="btn btn-outline-secondary">
                        Dashboard
                    </a>
                    <a href="{{ route('store.withdrawals.create') }}" class="btn btn-primary">
                        Novo Saque
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Cards de Estatísticas -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Saldo Disponível</h6>
                            <h4 class="mb-0 text-success">R$ {{ number_format($balance, 2, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Total Solicitado</h6>
                            <h4 class="mb-0">R$ {{ number_format($stats['total_requested'], 2, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Total Recebido</h6>
                            <h4 class="mb-0 text-info">R$ {{ number_format($stats['total_completed'], 2, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Pendente</h6>
                            <h4 class="mb-0 text-warning">R$ {{ number_format($stats['total_pending'], 2, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabela de Saques -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Solicitações de Saque</h5>
                </div>
                <div class="card-body">
                    @if($withdrawals->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Valor Bruto</th>
                                        <th>Comissão</th>
                                        <th>Valor Líquido</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($withdrawals as $withdrawal)
                                        <tr>
                                            <td>
                                                {{ $withdrawal->requested_at->format('d/m/Y H:i') }}
                                            </td>
                                            <td>R$ {{ number_format($withdrawal->amount, 2, ',', '.') }}</td>
                                            <td class="text-danger">
                                                - R$ {{ number_format($withdrawal->commission_amount, 2, ',', '.') }}
                                                @if($withdrawal->commission_percentage > 0)
                                                    <small class="text-muted">({{ number_format($withdrawal->commission_percentage, 1) }}%)</small>
                                                @endif
                                            </td>
                                            <td class="fw-bold">R$ {{ number_format($withdrawal->net_amount, 2, ',', '.') }}</td>
                                            <td>
                                                <span class="badge {{ $withdrawal->statusBadgeClass }}">
                                                    {{ $withdrawal->statusName }}
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#detailsModal{{ $withdrawal->id }}">
                                                    Detalhes
                                                </button>
                                            </td>
                                        </tr>

                                        <!-- Modal de Detalhes -->
                                        <div class="modal fade" id="detailsModal{{ $withdrawal->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Detalhes do Saque #{{ $withdrawal->id }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row mb-3">
                                                            <div class="col-md-6">
                                                                <strong>Data da Solicitação:</strong><br>
                                                                {{ $withdrawal->requested_at->format('d/m/Y H:i') }}
                                                            </div>
                                                            <div class="col-md-6">
                                                                <strong>Status:</strong><br>
                                                                <span class="badge {{ $withdrawal->statusBadgeClass }}">
                                                                    {{ $withdrawal->statusName }}
                                                                </span>
                                                            </div>
                                                        </div>

                                                        <hr>

                                                        <div class="row mb-3">
                                                            <div class="col-md-4">
                                                                <strong>Valor Bruto:</strong><br>
                                                                R$ {{ number_format($withdrawal->amount, 2, ',', '.') }}
                                                            </div>
                                                            <div class="col-md-4">
                                                                <strong>Comissão:</strong><br>
                                                                <span class="text-danger">- R$ {{ number_format($withdrawal->commission_amount, 2, ',', '.') }}</span>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <strong>Valor Líquido:</strong><br>
                                                                <span class="text-success fw-bold">R$ {{ number_format($withdrawal->net_amount, 2, ',', '.') }}</span>
                                                            </div>
                                                        </div>

                                                        @if($withdrawal->approved_at)
                                                            <hr>
                                                            <div class="mb-3">
                                                                <strong>Data da {{ $withdrawal->isRejected() ? 'Rejeição' : 'Aprovação' }}:</strong><br>
                                                                {{ $withdrawal->approved_at->format('d/m/Y H:i') }}
                                                            </div>
                                                        @endif

                                                        @if($withdrawal->completed_at)
                                                            <div class="mb-3">
                                                                <strong>Data da Conclusão:</strong><br>
                                                                {{ $withdrawal->completed_at->format('d/m/Y H:i') }}
                                                            </div>
                                                        @endif

                                                        @if($withdrawal->rejection_reason)
                                                            <hr>
                                                            <div class="alert alert-danger mb-0">
                                                                <strong>Motivo da Rejeição:</strong><br>
                                                                {{ $withdrawal->rejection_reason }}
                                                            </div>
                                                        @endif

                                                        @if($withdrawal->admin_notes)
                                                            <hr>
                                                            <div class="alert alert-info mb-0">
                                                                <strong>Observações do Administrador:</strong><br>
                                                                {{ $withdrawal->admin_notes }}
                                                            </div>
                                                        @endif

                                                        @if($withdrawal->pix_key_used || $withdrawal->bank_data_used)
                                                            <hr>
                                                            <h6>Dados Bancários Utilizados</h6>
                                                            @if($withdrawal->pix_key_used)
                                                                <p class="mb-1"><strong>PIX:</strong> {{ $withdrawal->pix_key_used }}</p>
                                                            @endif
                                                            @if($withdrawal->bank_data_used)
                                                                @php
                                                                    $bankData = $withdrawal->bank_data_used;
                                                                @endphp
                                                                @if(!empty($bankData['bank_name']))
                                                                    <p class="mb-1">
                                                                        <strong>Banco:</strong> {{ $bankData['bank_code'] ?? '' }} - {{ $bankData['bank_name'] ?? '' }}<br>
                                                                        <strong>Agência:</strong> {{ $bankData['agency'] ?? '' }}<br>
                                                                        <strong>Conta:</strong> {{ $bankData['account_number'] ?? '' }}-{{ $bankData['account_digit'] ?? '' }}
                                                                    </p>
                                                                @endif
                                                            @endif
                                                        @endif
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $withdrawals->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <p class="text-muted mb-3">Você ainda não solicitou nenhum saque.</p>
                            <a href="{{ route('store.withdrawals.create') }}" class="btn btn-primary">
                                Solicitar Primeiro Saque
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection






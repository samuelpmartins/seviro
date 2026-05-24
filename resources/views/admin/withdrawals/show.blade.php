@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">Solicitação de Saque #{{ $withdrawal->id }}</h2>
                    <p class="text-muted mb-0">{{ $withdrawal->store->name }}</p>
                </div>
                <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-outline-secondary">
                    Voltar à Lista
                </a>
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

            <div class="row">
                <!-- Coluna Principal -->
                <div class="col-lg-8">
                    <!-- Informações da Solicitação -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Informações da Solicitação</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p class="mb-1 text-muted">Data da Solicitação</p>
                                    <p class="fw-bold">{{ $withdrawal->requested_at->format('d/m/Y H:i') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1 text-muted">Status</p>
                                    <p>
                                        <span class="badge {{ $withdrawal->statusBadgeClass }} fs-6">
                                            {{ $withdrawal->statusName }}
                                        </span>
                                    </p>
                                </div>
                            </div>

                            <hr>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <p class="mb-1 text-muted">Valor Bruto</p>
                                    <p class="fw-bold fs-5">R$ {{ number_format($withdrawal->amount, 2, ',', '.') }}</p>
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-1 text-muted">Comissão
                                        @if($withdrawal->commission_percentage > 0)
                                            ({{ number_format($withdrawal->commission_percentage, 1) }}%)
                                        @endif
                                    </p>
                                    <p class="fw-bold fs-5 text-danger">- R$ {{ number_format($withdrawal->commission_amount, 2, ',', '.') }}</p>
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-1 text-muted">Valor Líquido</p>
                                    <p class="fw-bold fs-4 text-success">R$ {{ number_format($withdrawal->net_amount, 2, ',', '.') }}</p>
                                </div>
                            </div>

                            @if($withdrawal->approved_at)
                                <hr>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <p class="mb-1 text-muted">{{ $withdrawal->isRejected() ? 'Rejeitado' : 'Aprovado' }} em</p>
                                        <p class="fw-bold">{{ $withdrawal->approved_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1 text-muted">{{ $withdrawal->isRejected() ? 'Rejeitado' : 'Aprovado' }} por</p>
                                        <p class="fw-bold">{{ $withdrawal->approvedBy->name ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            @endif

                            @if($withdrawal->completed_at)
                                <hr>
                                <div class="mb-3">
                                    <p class="mb-1 text-muted">Completado em</p>
                                    <p class="fw-bold">{{ $withdrawal->completed_at->format('d/m/Y H:i') }}</p>
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
                        </div>
                    </div>

                    <!-- Dados Bancários -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Dados Bancários</h5>
                        </div>
                        <div class="card-body">
                            @if($withdrawal->pix_key_used)
                                <div class="mb-3">
                                    <h6>PIX</h6>
                                    <p class="mb-0">
                                        <strong>Chave PIX:</strong> {{ $withdrawal->pix_key_used }}
                                    </p>
                                </div>
                            @endif

                            @if($withdrawal->bank_data_used)
                                @php
                                    $bankData = $withdrawal->bank_data_used;
                                @endphp
                                @if(!empty($bankData['bank_name']))
                                    @if($withdrawal->pix_key_used)
                                        <hr>
                                    @endif
                                    <h6>Dados Bancários</h6>
                                    <p class="mb-1"><strong>Banco:</strong> {{ $bankData['bank_code'] ?? '' }} - {{ $bankData['bank_name'] ?? '' }}</p>
                                    <p class="mb-1"><strong>Agência:</strong> {{ $bankData['agency'] ?? '' }}</p>
                                    <p class="mb-1"><strong>Conta:</strong> {{ $bankData['account_number'] ?? '' }}-{{ $bankData['account_digit'] ?? '' }}</p>
                                    @if(!empty($bankData['account_type']))
                                        <p class="mb-1"><strong>Tipo:</strong> 
                                            {{ $bankData['account_type'] == 'checking' ? 'Conta Corrente' : 'Conta Poupança' }}
                                        </p>
                                    @endif
                                    <p class="mb-1"><strong>Titular:</strong> {{ $bankData['account_holder_name'] ?? '' }}</p>
                                    @if(!empty($bankData['account_holder_document']))
                                        <p class="mb-0"><strong>CPF/CNPJ:</strong> {{ $bankData['account_holder_document'] }}</p>
                                    @endif
                                @endif
                            @endif

                            @if(!$withdrawal->pix_key_used && !$withdrawal->bank_data_used)
                                <p class="text-muted mb-0">Dados bancários não disponíveis</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Coluna de Ações -->
                <div class="col-lg-4">
                    <!-- Ações -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Ações</h5>
                        </div>
                        <div class="card-body">
                            @if($withdrawal->isPending())
                                <!-- Aprovar -->
                                <button class="btn btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#approveModal">
                                    Aprovar Solicitação
                                </button>

                                <!-- Rejeitar -->
                                <button class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                    Rejeitar Solicitação
                                </button>
                            @elseif($withdrawal->isApproved())
                                <div class="alert alert-info mb-3">
                                    <strong>Solicitação Aprovada!</strong><br>
                                    Realize a transferência bancária e depois marque como completada.
                                </div>

                                <!-- Completar -->
                                <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#completeModal">
                                    Marcar como Completado
                                </button>
                            @elseif($withdrawal->isCompleted())
                                <div class="alert alert-success mb-0">
                                    <strong>Saque Completado!</strong><br>
                                    A transferência foi realizada com sucesso.
                                </div>
                            @elseif($withdrawal->isRejected())
                                <div class="alert alert-danger mb-0">
                                    <strong>Saque Rejeitado</strong><br>
                                    Esta solicitação foi rejeitada.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Informações do Restaurante -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">Restaurante</h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-1"><strong>Nome:</strong> {{ $withdrawal->store->name }}</p>
                            <p class="mb-1"><strong>Telefone:</strong> {{ $withdrawal->store->phone }}</p>
                            <p class="mb-0"><strong>Documento:</strong> {{ $withdrawal->store->document }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Aprovar -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.withdrawals.approve', $withdrawal) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Aprovar Solicitação</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Confirma a aprovação desta solicitação de saque?</p>
                    <p class="fw-bold">Valor a ser transferido: R$ {{ number_format($withdrawal->net_amount, 2, ',', '.') }}</p>
                    
                    <div class="mb-3">
                        <label for="admin_notes" class="form-label">Observações (opcional)</label>
                        <textarea class="form-control" id="admin_notes" name="admin_notes" rows="3" 
                                  placeholder="Digite observações se necessário"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Aprovar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Rejeitar -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.withdrawals.reject', $withdrawal) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Rejeitar Solicitação</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-danger">Você está prestes a rejeitar esta solicitação de saque.</p>
                    
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">Motivo da Rejeição *</label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4" 
                                  placeholder="Explique o motivo da rejeição" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Rejeitar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Completar -->
<div class="modal fade" id="completeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.withdrawals.complete', $withdrawal) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Marcar como Completado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Confirma que a transferência bancária foi realizada com sucesso?</p>
                    <p class="fw-bold">Valor transferido: R$ {{ number_format($withdrawal->net_amount, 2, ',', '.') }}</p>
                    
                    <div class="mb-3">
                        <label for="completion_notes" class="form-label">Observações (opcional)</label>
                        <textarea class="form-control" id="completion_notes" name="completion_notes" rows="3" 
                                  placeholder="Ex: ID da transação, comprovante, etc."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection






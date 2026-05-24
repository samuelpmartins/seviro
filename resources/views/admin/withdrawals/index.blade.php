@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">Solicitações de Saque</h2>
                    <p class="text-muted mb-0">Gerencie as solicitações dos restaurantes</p>
                </div>
                <a href="{{ route('admin.withdrawals.history') }}" class="btn btn-outline-primary">
                    Ver Histórico Completo
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

            <!-- Cards de Estatísticas -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-warning">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Pendentes</h6>
                            <h4 class="mb-0 text-warning">{{ $stats['pending_count'] }}</h4>
                            <small class="text-muted">R$ {{ number_format($stats['pending_amount'], 2, ',', '.') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-info">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Aprovados</h6>
                            <h4 class="mb-0 text-info">{{ $stats['approved_count'] }}</h4>
                            <small class="text-muted">R$ {{ number_format($stats['approved_amount'], 2, ',', '.') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Total a Transferir</h6>
                            <h3 class="mb-0">R$ {{ number_format($stats['approved_amount'], 2, ',', '.') }}</h3>
                            <small class="text-muted">Saques aprovados aguardando transferência</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.withdrawals.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">Todos</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendentes</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Aprovados</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completados</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejeitados</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Restaurante</label>
                            <select name="store_id" class="form-select">
                                <option value="">Todos</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>
                                        {{ $store->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Data Início</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Data Fim</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabela de Solicitações -->
            <div class="card">
                <div class="card-body">
                    @if($withdrawals->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Restaurante</th>
                                        <th>Data</th>
                                        <th>Valor Líquido</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($withdrawals as $withdrawal)
                                        <tr>
                                            <td>{{ $withdrawal->id }}</td>
                                            <td>
                                                <strong>{{ $withdrawal->store->name }}</strong>
                                            </td>
                                            <td>{{ $withdrawal->requested_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <div class="text-muted small">
                                                    Bruto: R$ {{ number_format($withdrawal->amount, 2, ',', '.') }}
                                                </div>
                                                <div class="fw-bold">
                                                    Líquido: R$ {{ number_format($withdrawal->net_amount, 2, ',', '.') }}
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge {{ $withdrawal->statusBadgeClass }}">
                                                    {{ $withdrawal->statusName }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.withdrawals.show', $withdrawal) }}" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    Ver Detalhes
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $withdrawals->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <p class="text-muted">Nenhuma solicitação encontrada com os filtros aplicados.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection






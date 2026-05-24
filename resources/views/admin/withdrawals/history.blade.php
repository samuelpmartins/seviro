@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">Histórico Completo de Saques</h2>
                    <p class="text-muted mb-0">Visualize todas as solicitações de saque do sistema</p>
                </div>
                <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-outline-primary">
                    Solicitações Ativas
                </a>
            </div>

            <!-- Estatísticas Gerais -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Total Solicitado</h6>
                            <h4 class="mb-0">R$ {{ number_format($stats['total_amount'], 2, ',', '.') }}</h4>
                            <small class="text-muted">Valor bruto</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Total Comissões</h6>
                            <h4 class="mb-0 text-info">R$ {{ number_format($stats['total_commission'], 2, ',', '.') }}</h4>
                            <small class="text-muted">Receita do sistema</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Total Pago</h6>
                            <h4 class="mb-0 text-success">R$ {{ number_format($stats['total_paid'], 2, ',', '.') }}</h4>
                            <small class="text-muted">Saques completados</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Status</h6>
                            <div class="d-flex justify-content-between">
                                <small>Pendentes: <strong>{{ $stats['count_by_status']['pending'] }}</strong></small>
                                <small>Aprovados: <strong>{{ $stats['count_by_status']['approved'] }}</strong></small>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <small>Completados: <strong>{{ $stats['count_by_status']['completed'] }}</strong></small>
                                <small>Rejeitados: <strong>{{ $stats['count_by_status']['rejected'] }}</strong></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros Avançados -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.withdrawals.history') }}" class="row g-3">
                        <div class="col-md-2">
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
                            <label class="form-label">Período</label>
                            <select name="period" class="form-select" id="periodSelect">
                                <option value="">Customizado</option>
                                <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>Hoje</option>
                                <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}>Esta Semana</option>
                                <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}>Este Mês</option>
                                <option value="year" {{ request('period') == 'year' ? 'selected' : '' }}>Este Ano</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Data Início</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" id="dateFrom">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Data Fim</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" id="dateTo">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabela de Histórico -->
            <div class="card">
                <div class="card-body">
                    @if($withdrawals->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Restaurante</th>
                                        <th>Data Solicitação</th>
                                        <th>Valor Bruto</th>
                                        <th>Comissão</th>
                                        <th>Valor Líquido</th>
                                        <th>Status</th>
                                        <th>Data Conclusão</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($withdrawals as $withdrawal)
                                        <tr>
                                            <td>{{ $withdrawal->id }}</td>
                                            <td>
                                                <strong>{{ $withdrawal->store->name }}</strong><br>
                                                <small class="text-muted">{{ $withdrawal->store->document }}</small>
                                            </td>
                                            <td>{{ $withdrawal->requested_at->format('d/m/Y H:i') }}</td>
                                            <td>R$ {{ number_format($withdrawal->amount, 2, ',', '.') }}</td>
                                            <td class="text-danger">
                                                R$ {{ number_format($withdrawal->commission_amount, 2, ',', '.') }}
                                                @if($withdrawal->commission_percentage > 0)
                                                    <br><small>({{ number_format($withdrawal->commission_percentage, 1) }}%)</small>
                                                @endif
                                            </td>
                                            <td class="fw-bold">R$ {{ number_format($withdrawal->net_amount, 2, ',', '.') }}</td>
                                            <td>
                                                <span class="badge {{ $withdrawal->statusBadgeClass }}">
                                                    {{ $withdrawal->statusName }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($withdrawal->completed_at)
                                                    {{ $withdrawal->completed_at->format('d/m/Y H:i') }}
                                                @elseif($withdrawal->approved_at && $withdrawal->isRejected())
                                                    <span class="text-danger">Rejeitado</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.withdrawals.show', $withdrawal) }}" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    Detalhes
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="3" class="text-end">Totais (página atual):</th>
                                        <th>R$ {{ number_format($withdrawals->sum('amount'), 2, ',', '.') }}</th>
                                        <th class="text-danger">R$ {{ number_format($withdrawals->sum('commission_amount'), 2, ',', '.') }}</th>
                                        <th class="fw-bold">R$ {{ number_format($withdrawals->sum('net_amount'), 2, ',', '.') }}</th>
                                        <th colspan="3"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $withdrawals->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <p class="text-muted">Nenhum registro encontrado com os filtros aplicados.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const periodSelect = document.getElementById('periodSelect');
    const dateFrom = document.getElementById('dateFrom');
    const dateTo = document.getElementById('dateTo');

    periodSelect.addEventListener('change', function() {
        if (this.value !== '') {
            dateFrom.disabled = true;
            dateTo.disabled = true;
        } else {
            dateFrom.disabled = false;
            dateTo.disabled = false;
        }
    });

    // Inicializar
    if (periodSelect.value !== '') {
        dateFrom.disabled = true;
        dateTo.disabled = true;
    }
});
</script>
@endsection






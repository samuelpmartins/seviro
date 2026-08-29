@extends('layouts.app')

@section('content')
<style>
    /* Fundo escuro */
    body {
        background: #0f0f23;
        color: #e8e8e9;
        min-height: 100vh;
        margin: 0;
    }
    
    /* Header do garçom */
    .waiter-header {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        padding: 20px 0;
        margin-bottom: 30px;
    }
    
    .waiter-header h1 {
        color: white;
        font-weight: 700;
        margin: 0;
    }
    
    .waiter-header .store-name {
        color: rgba(255, 255, 255, 0.8);
        font-size: 1.1rem;
    }
    
    /* Navegação */
    .waiter-nav {
        display: flex;
        gap: 15px;
    }
    
    .waiter-nav a {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        padding: 10px 20px;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .waiter-nav a:hover, .waiter-nav a.active {
        background: white;
        color: #3498db;
    }
    
    /* Container */
    .waiter-container {
        padding: 0 20px 40px;
    }
    
    /* Cards */
    .history-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        margin-bottom: 20px;
    }
    
    .history-card .card-header {
        background: #f8f9fa;
        padding: 20px 25px;
        border: none;
    }
    
    .history-card .card-header h5 {
        color: #2c3e50;
        font-weight: 700;
        margin: 0;
    }
    
    .history-card .card-body {
        padding: 25px;
    }
    
    /* Formulário de filtro */
    .form-label {
        color: #2c3e50;
        font-weight: 600;
        margin-bottom: 8px;
    }
    
    .form-control, .form-select {
        border: 2px solid #ecf0f1;
        border-radius: 10px;
        padding: 12px 15px;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #3498db;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }
    
    /* Tabela */
    .table {
        margin: 0;
    }
    
    .table thead th {
        background: #f8f9fa;
        border: none;
        color: #2c3e50;
        font-weight: 700;
        padding: 15px;
        font-size: 0.85rem;
        text-transform: uppercase;
    }
    
    .table tbody td {
        border: none;
        padding: 15px;
        vertical-align: middle;
        border-bottom: 1px solid #ecf0f1;
        color: #2c3e50;
    }
    
    .table tbody tr:hover {
        background: #f8f9fa;
    }
    
    /* Badges */
    .badge {
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.75rem;
    }
    
    /* Botão de logout */
    .btn-logout {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        padding: 8px 20px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .btn-logout:hover {
        background: rgba(255, 255, 255, 0.3);
        color: white;
    }
    
    /* Modal */
    .modal-content {
        border: none;
        border-radius: 16px;
    }
    
    .modal-header {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        color: white;
        border: none;
        padding: 20px 25px;
        border-radius: 16px 16px 0 0;
    }
    
    .btn-close {
        filter: invert(1);
    }
    
    .modal-body {
        padding: 25px;
    }
    
    /* Lista de itens */
    .list-group-item {
        border: none;
        border-bottom: 1px solid #ecf0f1;
        padding: 15px;
    }
    
    .list-group-item:last-child {
        border-bottom: none;
    }
</style>

<!-- Header -->
<div class="waiter-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fas fa-history me-3"></i>Histórico de Pedidos</h1>
                <p class="store-name mb-0">{{ $store->name }}</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <nav class="waiter-nav">
                    <a href="{{ route('waiter.dashboard') }}">
                        <i class="fas fa-th-large me-2"></i>Mesas
                    </a>
                    <a href="{{ route('waiter.history') }}" class="active">
                        <i class="fas fa-history me-2"></i>Histórico
                    </a>
                </nav>
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt me-2"></i>Sair
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="container waiter-container">
    <!-- Filtros -->
    <div class="history-card">
        <div class="card-header">
            <h5><i class="fas fa-filter me-2"></i>Filtrar Pedidos</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('waiter.history') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Data Inicial</label>
                    <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data Final</label>
                    <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">Todos</option>
                        <option value="Aguardando pagamento" {{ request('status') == 'Aguardando pagamento' ? 'selected' : '' }}>Aguardando pagamento</option>
                        <option value="Em produção" {{ request('status') == 'Em produção' ? 'selected' : '' }}>Em produção</option>
                        <option value="Finalizado" {{ request('status') == 'Finalizado' ? 'selected' : '' }}>Finalizado</option>
                        <option value="Cancelado" {{ request('status') == 'Cancelado' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Mesa</label>
                    <select class="form-select" name="table_id">
                        <option value="">Todas</option>
                        @foreach($tables as $table)
                            <option value="{{ $table->id }}" {{ request('table_id') == $table->id ? 'selected' : '' }}>
                                Mesa {{ $table->number }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Filtrar
                    </button>
                    <a href="{{ route('waiter.history') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-undo me-2"></i>Limpar
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Lista de pedidos -->
    <div class="history-card">
        <div class="card-body">
            @if($orders->count() > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Mesa / Participante</th>
                                <th>Itens</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Data</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td>
                                        @if($order->table)
                                            <strong>Mesa {{ $order->table->number }}</strong>
                                        @else
                                            <strong>Balcão</strong>
                                        @endif
                                        @if($order->participant)
                                            - {{ $order->participant->name }}
                                        @endif
                                    </td>
                                    <td>{{ $order->items->count() }} itens</td>
                                    <td>R$ {{ number_format($order->total, 2, ',', '.') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $order->status === 'Aguardando pagamento' ? 'warning' : 
                                                               ($order->status === 'Em produção' ? 'info' : 
                                                               ($order->status === 'Finalizado' ? 'success' : 
                                                               ($order->status === 'Cancelado' ? 'danger' : 'primary'))) }}">
                                            {{ $order->status }}
                                        </span>
                                    </td>
                                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#orderModal{{ $order->id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                
                                <!-- Modal de detalhes -->
                                <div class="modal fade" id="orderModal{{ $order->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">
                                                    {{ $order->table ? 'Mesa ' . $order->table->number : 'Balcão' }}
                                                    @if($order->participant)
                                                        - {{ $order->participant->name }}
                                                    @endif
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <strong>Mesa:</strong> 
                                                    @if($order->table)
                                                        {{ $order->table->number }}
                                                    @else
                                                        <span class="badge bg-secondary">Balcão</span>
                                                    @endif
                                                </div>
                                                @if($order->participant)
                                                    <div class="mb-3">
                                                        <strong>Cliente:</strong> {{ $order->participant->name }}
                                                    </div>
                                                @endif
                                                <div class="mb-3">
                                                    <strong>Status:</strong>
                                                    <span class="badge bg-{{ $order->status === 'Aguardando pagamento' ? 'warning' : 
                                                                           ($order->status === 'Em produção' ? 'info' : 
                                                                           ($order->status === 'Finalizado' ? 'success' : 
                                                                           ($order->status === 'Cancelado' ? 'danger' : 'primary'))) }}">
                                                        {{ $order->status }}
                                                    </span>
                                                </div>
                                                <div class="mb-3">
                                                    <strong>Data:</strong> {{ $order->created_at->format('d/m/Y H:i') }}
                                                </div>
                                                @if($order->notes)
                                                    <div class="mb-3">
                                                        <strong>Observações:</strong>
                                                        <p class="mb-0">{{ $order->notes }}</p>
                                                    </div>
                                                @endif
                                                <div class="mb-3">
                                                    <strong>Itens:</strong>
                                                    <ul class="list-group mt-2">
                                                        @foreach($order->items as $item)
                                                            <li class="list-group-item d-flex justify-content-between">
                                                                <div>
                                                                    <strong>{{ $item->quantity }}x</strong> {{ $item->product->name }}
                                                                    @if($item->notes)
                                                                        <br><small class="text-muted">{{ $item->notes }}</small>
                                                                    @endif
                                                                </div>
                                                                <span>R$ {{ number_format($item->price * $item->quantity, 2, ',', '.') }}</span>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                                <div class="text-end">
                                                    <strong>Total: R$ {{ number_format($order->total, 2, ',', '.') }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4">
                    {{ $orders->withQueryString()->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Nenhum pedido encontrado.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection










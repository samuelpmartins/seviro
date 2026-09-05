@extends('layouts.store-base')

@section('content')
    <style>
        /* Fundo preto por padrão */
        body {
            background: #000000;
            color: #e8e8e9;
            min-height: 100vh;
        }

        /* Tema light - fundo cinza claro */
        [data-bs-theme="light"] body {
            background: #e8e8e9;
            color: #000000;
        }

        /* Navbar - Nova Identidade Visual */
        .navbar {
            background: #000000 !important;
            backdrop-filter: blur(10px);
            border: none !important;
            border-bottom: 2px solid #9da1a1;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            z-index: 1050 !important;
            position: relative;
        }

        .navbar-brand,
        .nav-link {
            color: #e8e8e9 !important;
        }

        .navbar-brand:hover,
        .nav-link:hover {
            color: #9da1a1 !important;
        }

        .dropdown-menu {
            background: #000000;
            backdrop-filter: blur(10px);
            border: 1px solid #9da1a1;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .dropdown-item {
            color: #e8e8e9;
        }

        .dropdown-item:hover {
            background: #9da1a1;
            color: #000000;
        }

        /* Container principal */
        .dashboard-container {
            background: transparent;
            padding: 20px 0;
            margin-top: 0;
        }

        /* Título principal */
        .dashboard-title {
            color: #e8e8e9;
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-align: left;
            letter-spacing: -0.02em;
        }

        [data-bs-theme="light"] .dashboard-title {
            color: #000000;
        }

        .dashboard-subtitle {
            color: #9da1a1;
            font-size: 1.1rem;
            font-weight: 400;
            margin-bottom: 40px;
            text-align: left;
            line-height: 1.5;
        }

        /* Cards de estatísticas */
        .stats-card {
            background: white;
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .stats-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
        }

        .stats-card .card-body {
            padding: 25px;
        }

        .stats-card .card-subtitle {
            color: #6b7280;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .stats-card .card-title {
            color: #1f2937;
            font-size: 2.25rem;
            font-weight: 700;
            margin: 8px 0 16px 0;
            line-height: 1.2;
        }

        .stats-card .fs-1 {
            opacity: 0.6;
            transition: all 0.3s ease;
            font-size: 2.5rem !important;
        }

        .stats-card:hover .fs-1 {
            opacity: 0.8;
            transform: scale(1.05);
        }

        /* Botões dos cards */
        .stats-card .btn {
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.875rem;
            padding: 8px 16px;
            transition: all 0.3s ease;
            border: none;
        }

        .stats-card .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* Cards de conteúdo */
        .content-card {
            background: white;
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .content-card .card-header {
            background: transparent;
            border: none;
            padding: 25px 25px 0;
        }

        .content-card .card-title {
            color: #1f2937;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.01em;
        }

        .content-card .card-body {
            padding: 25px;
        }

        /* Tabela */
        .table {
            margin: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table thead th {
            background: #f9fafb;
            border: none;
            color: #374151;
            font-weight: 600;
            font-size: 0.875rem;
            padding: 16px;
            border-bottom: 1px solid #e5e7eb;
        }

        .table tbody td {
            border: none;
            padding: 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f3f4f6;
        }

        .table tbody tr {
            transition: all 0.2s ease;
        }

        .table tbody tr:hover {
            background: #f9fafb;
        }

        /* Badges */
        .badge {
            font-size: 0.75rem;
            font-weight: 500;
            padding: 6px 12px;
            border-radius: 6px;
        }

        /* Botões de ação */
        .action-buttons {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .action-buttons .btn {
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.8rem;
            padding: 6px 12px;
            transition: all 0.2s ease;
            border: 1px solid transparent;
        }

        .action-buttons .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Botões de ação rápida */
        .quick-action-btn {
            border-radius: 8px;
            font-weight: 600;
            padding: 12px 20px;
            transition: all 0.3s ease;
            border: 1px solid #e5e7eb;
            background: white;
            color: #374151;
            font-size: 0.875rem;
        }

        .quick-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-color: #d1d5db;
        }

        .btn-outline-primary:hover {
            background: #3498db;
            border-color: #3498db;
        }

        .btn-outline-success:hover {
            background: #27ae60;
            border-color: #27ae60;
        }

        .btn-outline-info:hover {
            background: #17a2b8;
            border-color: #17a2b8;
        }

        .btn-outline-secondary:hover {
            background: #6c757d;
            border-color: #6c757d;
        }

        /* Progress bar */
        .progress {
            height: 8px;
            border-radius: 10px;
            background: #ecf0f1;
        }

        .progress-bar {
            border-radius: 10px;
        }

        /* Modais */
        .modal-content {
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            border: none;
            padding: 25px 25px 0;
        }

        .modal-body {
            padding: 25px;
            max-height: 70vh;
            overflow-y: auto;
        }

        .order-summary-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            color: #2c3e50;
        }

        .order-item-image {
            width: 72px;
            height: 72px;
            flex: 0 0 72px;
            object-fit: cover;
            border-radius: 8px;
        }

        .order-item-image-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eef1f3;
            color: #9aa5ad;
        }

        .modal-title {
            color: #2c3e50;
            font-weight: bold;
        }

        /* Garantir que o modal apareça corretamente */
        .modal {
            z-index: 1055;
        }

        .modal-backdrop {
            z-index: 1050;
        }

        /* Prevenir problemas de renderização */
        .modal.show .modal-dialog {
            transform: none !important;
        }

        /* Lista de status */
        .list-group-item {
            border: none;
            border-radius: 10px;
            margin-bottom: 8px;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }

        .list-group-item:hover {
            background: #f8f9fa;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transform: none !important;
            /* Prevenir qualquer transformação */
        }

        .list-group-item.active {
            background: #3498db;
            color: white;
        }

        /* Financial Cards */
        .financial-card {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .financial-card:hover {
            background: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }

        .financial-icon {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 1.5rem;
        }

        .financial-card h3 {
            font-weight: 700;
            font-size: 1.75rem;
        }

        .financial-card h6 {
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .dashboard-title {
                font-size: 2.25rem;
            }

            .dashboard-subtitle {
                font-size: 1rem;
            }

            .stats-card .card-title {
                font-size: 1.875rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .action-buttons .btn {
                width: 100%;
                margin-bottom: 4px;
            }

            .quick-action-btn {
                padding: 10px 16px;
            }

            .financial-card h3 {
                font-size: 1.5rem;
            }

            .financial-icon {
                width: 45px;
                height: 45px;
                font-size: 1.25rem;
            }
        }
    </style>

    <div class="dashboard-container">
        <div class="container">
            <h1 class="dashboard-title">Dashboard</h1>
            <p class="dashboard-subtitle">Gerencie sua loja e acompanhe o desempenho em tempo real</p>

            <div class="row">
                <!-- Card de Produtos -->
                <div class="col-md-3 mb-4">
                    <div class="card stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-subtitle mb-2 text-muted">Total de Produtos</h6>
                                    <h2 class="card-title mb-0">{{ $totalProducts }}</h2>
                                </div>
                                <div class="fs-1 text-primary">
                                    <i class="fas fa-box"></i>
                                </div>
                            </div>
                            <a href="{{ route('store.products.index') }}" class="btn btn-sm btn-primary mt-3">
                                Ver Produtos
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Card de Categorias -->
                <div class="col-md-3 mb-4">
                    <div class="card stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-subtitle mb-2 text-muted">Total de Categorias</h6>
                                    <h2 class="card-title mb-0">{{ $totalCategories }}</h2>
                                </div>
                                <div class="fs-1 text-success">
                                    <i class="fas fa-tags"></i>
                                </div>
                            </div>
                            <a href="{{ route('store.categories.index') }}" class="btn btn-sm btn-success mt-3">
                                Ver Categorias
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Card de Mesas -->
                <div class="col-md-3 mb-4">
                    <div class="card stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-subtitle mb-2 text-muted">Total de Mesas</h6>
                                    <h2 class="card-title mb-0">{{ $totalTables }}</h2>
                                </div>
                                <div class="fs-1 text-info">
                                    <i class="fas fa-chair"></i>
                                </div>
                            </div>
                            <a href="{{ route('store.tables.index') }}" class="btn btn-sm btn-info mt-3">
                                Ver Mesas
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Card de Mesas Ocupadas -->
                <div class="col-md-3 mb-4">
                    <div class="card stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-subtitle mb-2 text-muted">Mesas Ocupadas</h6>
                                    <h2 class="card-title mb-0">{{ $occupiedTables }}</h2>
                                </div>
                                <div class="fs-1 text-warning">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                            <div class="progress mt-3" style="height: 5px;">
                                <div class="progress-bar bg-warning" role="progressbar"
                                    style="width: {{ $totalTables > 0 ? ($occupiedTables / $totalTables) * 100 : 0 }}%">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Painel Financeiro -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card content-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-line me-2"></i>Painel Financeiro
                            </h5>
                            <form method="GET" action="{{ route('store.dashboard') }}"
                                class="d-flex align-items-center gap-2 mt-3 mt-md-0">
                                <label for="financial_month" class="text-muted small mb-0">Período:</label>
                                <input type="month" id="financial_month" name="financial_month"
                                    value="{{ $selectedMonth }}" class="form-control form-control-sm"
                                    style="max-width: 170px;" onchange="this.form.submit()">
                            </form>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-4">
                                Indicadores de {{ $selectedMonthLabel }}. Vendas consideradas somente após o pagamento.
                            </p>
                            <div class="row g-4">
                                <!-- Vendas de Hoje -->
                                <div class="col-md-4">
                                    <div class="financial-card h-100">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="financial-icon bg-success bg-opacity-10 text-success me-3">
                                                <i class="fas fa-calendar-day"></i>
                                            </div>
                                            <div>
                                                <h6 class="text-muted mb-1">Vendas de Hoje</h6>
                                                <h3 class="mb-0 text-success">R$
                                                    {{ number_format($todaySales, 2, ',', '.') }}</h3>
                                                <small class="text-muted">{{ $todayOrders }} pedido(s)</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Vendas do Mês -->
                                <div class="col-md-4">
                                    <div class="financial-card h-100">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="financial-icon bg-primary bg-opacity-10 text-primary me-3">
                                                <i class="fas fa-calendar-alt"></i>
                                            </div>
                                            <div>
                                                <h6 class="text-muted mb-1">Vendas de {{ $selectedMonthLabel }}</h6>
                                                <h3 class="mb-0 text-primary">R$
                                                    {{ number_format($monthSales, 2, ',', '.') }}</h3>
                                                <small class="text-muted">{{ $monthOrders }} pedido(s)</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Total de Vendas -->
                                <div class="col-md-4">
                                    <div class="financial-card h-100">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="financial-icon bg-info bg-opacity-10 text-info me-3">
                                                <i class="fas fa-coins"></i>
                                            </div>
                                            <div>
                                                <h6 class="text-muted mb-1">Total Acumulado</h6>
                                                <h3 class="mb-0 text-info">R$
                                                    {{ number_format($totalSales, 2, ',', '.') }}</h3>
                                                <small class="text-muted">Todos os tempos</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pedidos Pendentes de Pagamento -->
                                <div class="col-md-4">
                                    <div class="financial-card h-100">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="financial-icon bg-danger bg-opacity-10 text-danger me-3">
                                                <i class="fas fa-clock"></i>
                                            </div>
                                            <div>
                                                <h6 class="text-muted mb-1">Pendente de Pagamento</h6>
                                                <h3 class="mb-0 text-danger">R$
                                                    {{ number_format($unpaidTotal, 2, ',', '.') }}</h3>
                                                <small class="text-muted">{{ $unpaidOrders }} pedido(s)</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pedidos Pendentes -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card content-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Pedidos Pendentes</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Pedido</th>
                                            <th>Mesa</th>
                                            <th>Itens</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Pagamento</th>
                                            <th>Data</th>
                                            <th style="min-width: 150px;">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($pendingOrders as $order)
                                            <tr>
                                                <td><strong>{{ $order->order_number }}</strong></td>
                                                <td>{{ $order->table ? 'Mesa ' . $order->table->number : 'Balcão' }}</td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-link"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#orderModal{{ $order->id }}">
                                                        Ver Itens ({{ $order->items->count() }})
                                                    </button>
                                                </td>
                                                <td>R$ {{ number_format($order->total, 2, ',', '.') }}</td>
                                                <td>
                                                    <span
                                                        class="badge bg-{{ $order->status === 'Aguardando pagamento'
                                                            ? 'warning'
                                                            : ($order->status === 'Em produção'
                                                                ? 'info'
                                                                : ($order->status === 'Finalizado'
                                                                    ? 'success'
                                                                    : ($order->status === 'Cancelado'
                                                                        ? 'danger'
                                                                        : 'primary'))) }}">
                                                        {{ $order->status }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if ($order->payment_status === 'paid')
                                                        <span class="badge bg-success mb-1">
                                                            <i class="fas fa-check-circle me-1"></i>Pago
                                                        </span>
                                                        <br>
                                                        <small class="text-muted">
                                                            @if ($order->payment_method === 'card')
                                                                <i class="fas fa-credit-card me-1"></i>Crédito/Débito
                                                            @elseif($order->payment_method === 'pix')
                                                                <i class="fas fa-qrcode me-1"></i>PIX
                                                            @elseif($order->payment_method === 'cash')
                                                                <i class="fas fa-money-bill-wave me-1"></i>Dinheiro
                                                            @endif
                                                        </small>
                                                    @else
                                                        <span class="badge bg-warning">
                                                            <i class="fas fa-clock me-1"></i>Pendente
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <button type="button" class="btn btn-sm btn-primary"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#statusModal{{ $order->id }}">
                                                            Mudar Status
                                                        </button>

                                                        @if ($order->payment_status !== 'paid')
                                                            <button type="button" class="btn btn-sm btn-success ms-1"
                                                                onclick="markAsPaid({{ $order->id }})">
                                                                <i class="fas fa-dollar-sign me-1"></i>Marcar Pago
                                                            </button>
                                                        @endif

                                                        <form method="POST"
                                                            action="{{ route('store.orders.cancel', $order->id) }}"
                                                            class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger ms-1"
                                                                onclick="return confirm('Tem certeza que deseja cancelar este pedido?')">
                                                                Cancelar
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>

                                            <!-- Modal de Detalhes do Pedido -->
                                            <div class="modal fade" id="orderModal{{ $order->id }}" tabindex="-1">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Pedido {{ $order->order_number }} -
                                                                {{ $order->table ? 'Mesa ' . $order->table->number : 'Balcão' }}
                                                            </h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <strong>Status:</strong>
                                                                <span
                                                                    class="badge bg-{{ $order->status === 'Aguardando pagamento'
                                                                        ? 'warning'
                                                                        : ($order->status === 'Em produção'
                                                                            ? 'info'
                                                                            : ($order->status === 'Finalizado'
                                                                                ? 'success'
                                                                                : ($order->status === 'Cancelado'
                                                                                    ? 'danger'
                                                                                    : 'primary'))) }}">
                                                                    {{ $order->status }}
                                                                </span>
                                                            </div>
                                                            <div class="mb-3">
                                                                <strong>Data:</strong>
                                                                {{ $order->created_at->format('d/m/Y H:i') }}
                                                            </div>
                                                            @if ($order->notes)
                                                                <div class="mb-3">
                                                                    <strong>Observações do Pedido:</strong>
                                                                    <p class="mb-0">{{ $order->notes }}</p>
                                                                </div>
                                                            @endif
                                                            <div class="mb-3">
                                                                <strong>Itens:</strong>
                                                                <ul class="list-group">
                                                                    @foreach ($order->items as $item)
                                                                        @include(
                                                                            'components.order-item-details',
                                                                            ['item' => $item]
                                                                        )
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                            <div class="text-end">
                                                                <strong>Total: R$
                                                                    {{ number_format($order->total, 2, ',', '.') }}</strong>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Modal de Seleção de Status -->
                                            <div class="modal fade" id="statusModal{{ $order->id }}" tabindex="-1">
                                                <div class="modal-dialog modal-sm">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Alterar Status</h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="list-group">
                                                                <form method="POST"
                                                                    action="{{ route('store.orders.update-status', $order->id) }}"
                                                                    class="mb-2">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <input type="hidden" name="status"
                                                                        value="Aguardando pagamento">
                                                                    <button type="submit"
                                                                        class="list-group-item list-group-item-action {{ $order->status === 'Aguardando pagamento' ? 'active' : '' }}">
                                                                        Aguardando pagamento
                                                                    </button>
                                                                </form>

                                                                <form method="POST"
                                                                    action="{{ route('store.orders.update-status', $order->id) }}"
                                                                    class="mb-2">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <input type="hidden" name="status"
                                                                        value="Em produção">
                                                                    <button type="submit"
                                                                        class="list-group-item list-group-item-action {{ $order->status === 'Em produção' ? 'active' : '' }}">
                                                                        Em produção
                                                                    </button>
                                                                </form>

                                                                <form method="POST"
                                                                    action="{{ route('store.orders.update-status', $order->id) }}"
                                                                    class="mb-2">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <input type="hidden" name="status"
                                                                        value="Finalizado">
                                                                    <button type="submit"
                                                                        class="list-group-item list-group-item-action {{ $order->status === 'Finalizado' ? 'active' : '' }}">
                                                                        Finalizado
                                                                    </button>
                                                                </form>

                                                                <form method="POST"
                                                                    action="{{ route('store.orders.update-status', $order->id) }}"
                                                                    class="mb-2">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <input type="hidden" name="status"
                                                                        value="Cancelado">
                                                                    <button type="submit"
                                                                        class="list-group-item list-group-item-action {{ $order->status === 'Cancelado' ? 'active' : '' }}">
                                                                        Cancelado
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">Nenhum pedido pendente</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informações da Loja -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card content-card">
                        <div class="card-body">
                            <h5 class="card-title">Informações da Loja</h5>
                            <div class="mb-3">
                                <strong>Nome:</strong> {{ $store->name }}
                            </div>
                            <div class="mb-3">
                                <strong>Telefone:</strong> {{ $store->phone }}
                            </div>
                            <div class="mb-3">
                                <strong>Endereço:</strong> {{ $store->address }}
                            </div>
                            <div class="mb-3">
                                <strong>Documento:</strong> {{ $store->document }}
                            </div>
                            <a href="{{ route('store.edit') }}" class="btn btn-primary">
                                Editar Informações
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card content-card">
                        <div class="card-body">
                            <h5 class="card-title">Ações Rápidas</h5>
                            <div class="d-grid gap-3">
                                <button type="button" class="quick-action-btn" data-bs-toggle="modal"
                                    data-bs-target="#newProductModal">
                                    <i class="fas fa-plus-circle me-2"></i> Novo Produto
                                </button>
                                <button type="button" class="quick-action-btn" data-bs-toggle="modal"
                                    data-bs-target="#newCategoryModal">
                                    <i class="fas fa-folder-plus me-2"></i> Nova Categoria
                                </button>
                                <button type="button" class="quick-action-btn" data-bs-toggle="modal"
                                    data-bs-target="#newTableModal">
                                    <i class="fas fa-plus-square me-2"></i> Nova Mesa
                                </button>
                                <a href="{{ route('store.menu.preview') }}" class="quick-action-btn" target="_blank">
                                    <i class="fas fa-eye me-2"></i> Visualizar Cardápio
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nova Categoria -->
    <div class="modal fade" id="newCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nova Categoria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="newCategoryForm" action="{{ route('store.categories.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="category_name" class="form-label">Nome da Categoria</label>
                            <input type="text" class="form-control" id="category_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="category_description" class="form-label">Descrição (opcional)</label>
                            <textarea class="form-control" id="category_description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Criar Categoria</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Novo Produto -->
    <div class="modal fade" id="newProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Novo Produto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="newProductForm" action="{{ route('store.products.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="product_category_id" class="form-label">Categoria</label>
                            <select class="form-select" id="product_category_id" name="category_id" required>
                                <option value="">Selecione uma categoria</option>
                                @foreach ($store->categories()->orderBy('order')->get() as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="product_name" class="form-label">Nome do Produto</label>
                            <input type="text" class="form-control" id="product_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="product_price" class="form-label">Preço</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="text" class="form-control" id="product_price" name="price" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="product_description" class="form-label">Descrição</label>
                            <textarea class="form-control" id="product_description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="product_image" class="form-label">Imagem</label>
                            <input type="file" class="form-control" id="product_image" name="image"
                                accept="image/*">
                        </div>
                        <div class="form-check mb-2">
                            <input type="checkbox" class="form-check-input" id="product_active" name="active"
                                value="1" checked>
                            <label class="form-check-label" for="product_active">
                                Produto ativo
                            </label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="product_is_quick_item"
                                name="is_quick_item" value="1">
                            <label class="form-check-label" for="product_is_quick_item">
                                Item rápido
                            </label>
                            <small class="d-block text-muted">Itens rápidos (ex: refrigerantes) não vão para a cozinha, são
                                entregues diretamente pelo garçom.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Criar Produto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Nova Mesa -->
    <div class="modal fade" id="newTableModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nova Mesa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="newTableForm" action="{{ route('store.tables.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="table_number" class="form-label">Número da Mesa</label>
                            <input type="text" class="form-control" id="table_number" name="number" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Criar Mesa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            /* Garantir que os modais apareçam sobre todos os outros elementos */
            .modal {
                z-index: 1060;
            }

            /* Estilo para os botões de ação */
            .action-buttons {
                display: flex;
                gap: 0.25rem;
            }

            /* Destaque para os status diferentes */
            .badge.bg-warning {
                color: #000;
            }

            /* Animar transição dos itens do modal - SEM deslocamento horizontal */
            .list-group-item {
                transition: background-color 0.2s ease, box-shadow 0.2s ease;
                border: none;
                margin-bottom: 5px;
            }

            .list-group-item:hover {
                background-color: #f8f9fa !important;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                transform: none !important;
                /* Prevenir qualquer transformação */
            }

            /* Remover margens dos formulários dentro da lista */
            .list-group form {
                margin-bottom: 5px;
            }

            /* Garantir que os botões ocupem toda a largura do item */
            .list-group button.list-group-item {
                width: 100%;
                text-align: left;
                border-radius: 5px;
            }

            /* Melhorar visualização em dispositivos móveis */
            @media (max-width: 768px) {
                .table-responsive {
                    overflow-x: auto;
                }

                .action-buttons button {
                    padding: 0.25rem 0.5rem;
                    font-size: 0.75rem;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/cleave.js@1.6.0/dist/cleave.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Máscara para preço no modal de produto
                new Cleave('#product_price', {
                    numeral: true,
                    numeralThousandsGroupStyle: 'thousand',
                    numeralDecimalMark: ',',
                    delimiter: '.'
                });

                // Limpar formulários quando modais são fechados
                document.getElementById('newCategoryModal').addEventListener('hidden.bs.modal', function() {
                    document.getElementById('newCategoryForm').reset();
                });

                document.getElementById('newProductModal').addEventListener('hidden.bs.modal', function() {
                    document.getElementById('newProductForm').reset();
                });

                document.getElementById('newTableModal').addEventListener('hidden.bs.modal', function() {
                    document.getElementById('newTableForm').reset();
                });

                // Interceptar submissão dos formulários para mostrar feedback
                document.getElementById('newCategoryForm').addEventListener('submit', function(e) {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Criando...';
                });

                document.getElementById('newProductForm').addEventListener('submit', function(e) {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Criando...';
                });

                document.getElementById('newTableForm').addEventListener('submit', function(e) {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Criando...';
                });

                // Prevenir múltiplos modais abertos ao mesmo tempo
                document.querySelectorAll('.modal').forEach(function(modal) {
                    modal.addEventListener('show.bs.modal', function(event) {
                        // Fechar todos os outros modais abertos
                        document.querySelectorAll('.modal.show').forEach(function(openModal) {
                            if (openModal !== modal) {
                                var instance = bootstrap.Modal.getInstance(openModal);
                                if (instance) {
                                    instance.hide();
                                }
                            }
                        });
                    });
                });
            });

            // Função para marcar pedido como pago em dinheiro
            function markAsPaid(orderId) {
                if (!confirm('Confirma que este pedido foi pago em DINHEIRO?')) {
                    return;
                }

                const button = event.target.closest('button');
                const originalHtml = button.innerHTML;
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Marcando...';

                fetch(`/store/orders/${orderId}/mark-paid-cash`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.message) {
                            // Recarregar a página para atualizar a tabela
                            location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Erro ao marcar pedido como pago. Tente novamente.');
                        button.disabled = false;
                        button.innerHTML = originalHtml;
                    });
            }

            // Carregar saldo disponível para saque
            function loadAvailableBalance() {
                const balanceElement = document.getElementById('availableBalance');

                fetch('{{ route('store.balance') }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const balance = parseFloat(data.balance);
                            balanceElement.innerHTML = 'R$ ' + balance.toLocaleString('pt-BR', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        } else {
                            balanceElement.innerHTML = 'R$ 0,00';
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao carregar saldo:', error);
                        balanceElement.innerHTML = 'R$ 0,00';
                    });
            }

            // Executar quando o DOM carregar
            document.addEventListener('DOMContentLoaded', function() {
                // Carregar saldo ao iniciar
                loadAvailableBalance();
            });
        </script>
    @endpush
@endsection

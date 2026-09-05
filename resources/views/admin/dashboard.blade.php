@extends('layouts.app')

@section('content')
    <nav class="navbar navbar-expand-md shadow-sm"
        style="background-color: #000000 !important; border-bottom: 2px solid #9da1a1;">
        <div class="container">
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}">
                <img src="{{ asset('storage/img/logo.png') }}" alt="{{ config('app.name', 'Laravel') }}"
                    style="height: 36px; width: auto; object-fit: contain;">
            </a>
            <div class="navbar-nav me-auto">
                <a class="nav-link" href="{{ route('admin.dashboard') }}">Painel</a>
            </div>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a id="adminDashboardUserMenu" class="nav-link dropdown-toggle" href="#" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        {{ auth('admin')->user()->name }}
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="adminDashboardUserMenu">
                        <a class="dropdown-item d-flex align-items-center" href="{{ route('admin.demo-requests.index') }}">
                            <i class="fas fa-list me-2"></i>
                            Solicitações de Demonstração
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('admin.logout') }}"
                            onclick="event.preventDefault(); document.getElementById('admin-dashboard-logout-form').submit();">
                            {{ __('Logout') }}
                        </a>
                        <form id="admin-dashboard-logout-form" action="{{ route('admin.logout') }}" method="POST"
                            class="d-none">
                            @csrf
                        </form>
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container-fluid py-5"
        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">
        <!-- Header -->
        <div class="container mb-5">
            <div>
                <h1 class="display-4 fw-bold text-white mb-2">
                    <i class="fas fa-chart-line me-3"></i>Painel Financeiro
                </h1>
                <p class="text-white-50 fs-5">Visão geral da receita e desempenho de seus restaurantes</p>
                <form method="GET" action="{{ route('admin.dashboard') }}" class="d-flex align-items-center gap-2 mt-4">
                    <label for="store_search" class="text-white mb-0">Restaurante:</label>
                    <input type="search" id="store_search" name="store_search" value="{{ $storeSearch }}"
                        class="form-control form-control-lg" style="width: 280px; min-width: 0; flex: 0 1 280px;"
                        placeholder="Nome ou CPF/CNPJ" aria-label="Buscar restaurante por nome ou CPF/CNPJ">
                    <button type="submit" class="btn btn-light btn-lg" title="Buscar restaurante">
                        <i class="fas fa-search"></i>
                    </button>
                    @if ($storeSearch !== '')
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-light btn-lg" title="Limpar busca">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Cards de Métricas -->
        <div class="container mb-5">
            <div class="row g-4">
                <!-- Receita Total -->
                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="p-3 rounded-3" style="background-color: #d4edda;">
                                    <i class="fas fa-shopping-bag fs-5" style="color: #28a745;"></i>
                                </div>
                            </div>
                            <h6 class="text-uppercase fw-bold"
                                style="color: #6c757d; font-size: 0.75rem; letter-spacing: 0.5px;">Receita Total</h6>
                            <h2 class="fw-bold mb-2" style="color: #28a745;">R$
                                {{ number_format($totalRevenue, 2, ',', '.') }}</h2>
                            <p class="text-muted small mb-0">Todos os tempos</p>
                        </div>
                    </div>
                </div>

                <!-- Receita do Mês -->
                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="p-3 rounded-3" style="background-color: #cce5ff;">
                                    <i class="fas fa-calendar fs-5" style="color: #007bff;"></i>
                                </div>
                            </div>
                            <h6 class="text-uppercase fw-bold"
                                style="color: #6c757d; font-size: 0.75rem; letter-spacing: 0.5px;">Receita do Mês</h6>
                            <h2 class="fw-bold mb-2" style="color: #007bff;">R$
                                {{ number_format($monthlyRevenue, 2, ',', '.') }}</h2>
                            <p class="text-muted small mb-0">{{ $monthlyFinishedOrders }} pedido(s) neste mês</p>
                        </div>
                    </div>
                </div>

                <!-- Total Acumulado -->
                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="p-3 rounded-3" style="background-color: #cff4fc;">
                                    <i class="fas fa-database fs-5" style="color: #0dcaf0;"></i>
                                </div>
                            </div>
                            <h6 class="text-uppercase fw-bold"
                                style="color: #6c757d; font-size: 0.75rem; letter-spacing: 0.5px;">Total Acumulado</h6>
                            <h2 class="fw-bold mb-2" style="color: #0dcaf0;">R$
                                {{ number_format($totalRevenue, 2, ',', '.') }}</h2>
                            <p class="text-muted small mb-0">Todos os tempos</p>
                        </div>
                    </div>
                </div>

                <!-- Restaurantes -->
                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="p-3 rounded-3" style="background-color: #fff3cd;">
                                    <i class="fas fa-store fs-5" style="color: #ffc107;"></i>
                                </div>
                            </div>
                            <h6 class="text-uppercase fw-bold"
                                style="color: #6c757d; font-size: 0.75rem; letter-spacing: 0.5px;">Restaurantes</h6>
                            <h2 class="fw-bold mb-2" style="color: #ffc107;">
                                {{ number_format($totalStores, 0, ',', '.') }}
                            </h2>
                            <p class="text-muted small mb-0">Ativos na plataforma</p>
                        </div>
                    </div>
                </div>

                <!-- Ticket Médio -->
                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="p-3 rounded-3" style="background-color: #f8d7da;">
                                    <i class="fas fa-receipt fs-5" style="color: #dc3545;"></i>
                                </div>
                            </div>
                            <h6 class="text-uppercase fw-bold"
                                style="color: #6c757d; font-size: 0.75rem; letter-spacing: 0.5px;">Ticket Médio</h6>
                            <h2 class="fw-bold mb-2" style="color: #dc3545;">R$
                                {{ number_format($totalFinishedOrders > 0 ? $totalRevenue / $totalFinishedOrders : 0, 2, ',', '.') }}
                            </h2>
                            <p class="text-muted small mb-0">Por pedido</p>
                        </div>
                    </div>
                </div>

                <!-- Pedidos Finalizados -->
                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="p-3 rounded-3" style="background-color: #d1ecf1;">
                                    <i class="fas fa-check-circle fs-5" style="color: #17a2b8;"></i>
                                </div>
                            </div>
                            <h6 class="text-uppercase fw-bold"
                                style="color: #6c757d; font-size: 0.75rem; letter-spacing: 0.5px;">Pedidos Finalizados</h6>
                            <h2 class="fw-bold mb-2" style="color: #17a2b8;">
                                {{ number_format($totalFinishedOrders, 0, ',', '.') }}</h2>
                            <p class="text-muted small mb-0">Todos os tempos</p>
                        </div>
                    </div>
                </div>

                <!-- Pedidos Totais -->
                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="p-3 rounded-3" style="background-color: #e2e3e5;">
                                    <i class="fas fa-list fs-5" style="color: #6c757d;"></i>
                                </div>
                            </div>
                            <h6 class="text-uppercase fw-bold"
                                style="color: #6c757d; font-size: 0.75rem; letter-spacing: 0.5px;">Pedidos Totais</h6>
                            <h2 class="fw-bold mb-2" style="color: #6c757d;">
                                {{ number_format($totalOrders, 0, ',', '.') }}</h2>
                            <p class="text-muted small mb-0">Todos os tempos</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela de Restaurantes -->
        <div class="container">
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-header bg-white py-4" style="border-radius: 12px 12px 0 0;">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-store me-2" style="color: #667eea;"></i>Restaurantes
                    </h5>
                    <p class="text-muted mb-0 small">Detalhes de receita, pedidos e desempenho por restaurante.</p>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="fw-bold">Restaurante</th>
                                    <th class="fw-bold">Responsável</th>
                                    <th class="fw-bold">Pedidos Finalizados</th>
                                    <th class="fw-bold">Receita (Paga)</th>
                                    <th class="fw-bold">Pedidos Totais</th>
                                    <th class="fw-bold">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stores as $store)
                                    <tr>
                                        <td class="fw-500">{{ $store->name }}</td>
                                        <td>{{ $store->user?->name ?? '—' }}</td>
                                        <td>
                                            <span class="badge bg-success">
                                                {{ number_format($store->paid_orders_count ?? 0, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td class="fw-bold" style="color: #28a745;">
                                            R$ {{ number_format($store->paid_revenue ?? 0, 2, ',', '.') }}
                                        </td>
                                        <td>{{ number_format($store->total_orders_count ?? 0, 0, ',', '.') }}</td>
                                        <td>
                                            <a href="{{ route('admin.stores.edit', $store) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="fas fa-inbox fs-4 mb-2 d-block"></i>
                                            Nenhum restaurante encontrado.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white" style="border-radius: 0 0 12px 12px;">
                    {{ $stores->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

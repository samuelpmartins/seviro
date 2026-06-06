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
        .production-container {
            background: transparent;
            padding: 20px 0;
            margin-top: 0;
        }

        /* Título principal */
        .production-title {
            color: #e8e8e9;
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-align: left;
            letter-spacing: -0.02em;
        }

        [data-bs-theme="light"] .production-title {
            color: #000000;
        }

        /* Botão de voltar */
        .btn-back {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            color: #e8e8e9;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            text-decoration: none;
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.4);
            color: #e8e8e9;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            text-decoration: none;
        }

        /* Card principal */
        .production-card {
            background: white;
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .production-card .card-body {
            padding: 40px;
        }

        /* Tabela moderna */
        .table {
            border: none;
        }

        .table thead th {
            background: #f8f9fa;
            border: none;
            color: #2c3e50;
            font-weight: 700;
            padding: 20px 15px;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .table tbody td {
            border: none;
            padding: 20px 15px;
            vertical-align: middle;
            border-bottom: 1px solid #ecf0f1;
        }

        .table tbody tr:hover {
            background: #f8f9fa;
            transition: all 0.2s ease;
        }

        /* Botões modernos */
        .btn-success,
        .btn-link {
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-success:hover,
        .btn-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .btn-link {
            color: #3498db;
            text-decoration: none;
        }

        .btn-link:hover {
            color: #2980b9;
            text-decoration: none;
        }

        /* Modais com identidade visual */
        .modal-content {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .modal-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            border: none;
            padding: 25px 30px;
        }

        .modal-title {
            font-weight: 700;
            font-size: 1.25rem;
        }

        .btn-close {
            filter: invert(1);
            opacity: 0.8;
        }

        .btn-close:hover {
            opacity: 1;
        }

        .modal-body {
            padding: 30px;
        }

        .modal-footer {
            border: none;
            padding: 20px 30px 30px;
        }

        /* Lista de itens do pedido */
        .list-group-item {
            border: none;
            border-radius: 12px;
            margin-bottom: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: box-shadow 0.3s ease;
        }

        .list-group-item:hover {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transform: none !important;
            /* Prevenir qualquer transformação */
        }

        /* Imagens dos produtos */
        .img-thumbnail {
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        /* Estado vazio */
        .text-center.py-4 {
            padding: 60px 20px !important;
        }

        .text-center.py-4 i {
            color: #bdc3c7;
            margin-bottom: 20px;
        }

        .text-center.py-4 .h5 {
            color: #7f8c8d;
            font-weight: 600;
        }
    </style>

    <div class="container production-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="production-title">Pedidos em Produção</h1>
            <div>
                <a href="{{ route('store.dashboard') }}" class="btn btn-back">
                    <i class="fas fa-arrow-left me-2"></i> Voltar
                </a>
            </div>
        </div>

        <div class="card production-card">
            <div class="card-body">
                @if ($orders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Pedido</th>
                                    <th>Mesa</th>
                                    <th>Itens</th>
                                    <th>Total</th>
                                    <th>Criado em</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    <tr>
                                        <td><strong>{{ $order->order_number }}</strong></td>
                                        <td>Mesa {{ $order->table->number }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-link" data-bs-toggle="modal"
                                                data-bs-target="#orderModal{{ $order->id }}">
                                                Ver Itens ({{ $order->items->count() }})
                                            </button>
                                        </td>
                                        <td>R$ {{ number_format($order->total, 2, ',', '.') }}</td>
                                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <form action="{{ route('store.orders.update-status', $order->id) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="Finalizado">
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="fas fa-check me-1"></i> Finalizar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- Modal de Detalhes do Pedido -->
                                    <div class="modal fade" id="orderModal{{ $order->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Pedido {{ $order->order_number }} - Mesa
                                                        {{ $order->table->number }}</h5>
                                                    <button type="button" class="btn-close"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
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
                                                                <li class="list-group-item">
                                                                    <div class="d-flex">
                                                                        @if ($item->product->image)
                                                                            <div class="me-3">
                                                                                <img src="{{ asset('storage/' . $item->product->image) }}"
                                                                                    alt="{{ $item->product->name }}"
                                                                                    class="img-thumbnail"
                                                                                    style="width: 70px; height: 70px; object-fit: cover;">
                                                                            </div>
                                                                        @endif
                                                                        <div class="flex-grow-1">
                                                                            <div
                                                                                class="d-flex justify-content-between align-items-center">
                                                                                <div>
                                                                                    <strong>{{ $item->product->name }}</strong>
                                                                                    @if ($item->product->is_quick_item)
                                                                                        <span class="badge bg-info ms-1"
                                                                                            title="Item rápido - entrega direta pelo garçom">
                                                                                            <i class="fas fa-bolt"></i>
                                                                                            Rápido
                                                                                        </span>
                                                                                    @endif
                                                                                    <br>
                                                                                    <small class="text-muted">
                                                                                        {{ $item->quantity }}x R$
                                                                                        {{ number_format($item->price, 2, ',', '.') }}
                                                                                    </small>
                                                                                    @if ($item->notes)
                                                                                        <br>
                                                                                        <small
                                                                                            class="text-muted fst-italic">
                                                                                            <strong>Obs.:</strong>
                                                                                            {{ $item->notes }}
                                                                                        </small>
                                                                                    @endif
                                                                                </div>
                                                                                <span>
                                                                                    R$
                                                                                    {{ number_format($item->price * $item->quantity, 2, ',', '.') }}
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                    <div class="text-end">
                                                        <strong>Total: R$
                                                            {{ number_format($order->total, 2, ',', '.') }}</strong>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Fechar</button>
                                                    <form action="{{ route('store.orders.update-status', $order->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="status" value="Finalizado">
                                                        <button type="submit" class="btn btn-success">
                                                            <i class="fas fa-check me-1"></i> Finalizar Pedido
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-utensils fa-3x mb-3 text-muted"></i>
                        <p class="h5 text-muted">Não há pedidos em produção no momento.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

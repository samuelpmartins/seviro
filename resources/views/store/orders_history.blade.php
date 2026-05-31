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
        .history-container {
            background: transparent;
            padding: 20px 0;
            margin-top: 0;
        }

        /* Título principal */
        .history-title {
            color: #e8e8e9;
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-align: left;
            letter-spacing: -0.02em;
        }

        [data-bs-theme="light"] .history-title {
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

        /* Cards modernos */
        .history-card {
            background: white;
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            transition: all 0.3s ease;
        }

        .history-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
        }

        .history-card .card-header {
            background: #f8f9fa;
            border: none;
            padding: 25px 30px;
            border-bottom: 1px solid #ecf0f1;
        }

        .history-card .card-body {
            padding: 30px;
        }

        .history-card h5 {
            color: #2c3e50;
            font-weight: 700;
            margin: 0;
        }

        /* Formulários modernos */
        .form-label {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }

        .form-control,
        .form-select {
            border: 2px solid #ecf0f1;
            border-radius: 10px;
            padding: 15px 20px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #3498db;
            background: white;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
            outline: none;
        }

        /* Botões modernos */
        .btn-primary,
        .btn-outline-secondary {
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .btn-outline-secondary {
            background: transparent;
            border: 2px solid #bdc3c7;
            color: #7f8c8d;
        }

        .btn-outline-secondary:hover {
            background: #bdc3c7;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
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

        /* Badges modernos */
        .badge {
            border-radius: 8px;
            padding: 8px 12px;
            font-weight: 600;
            font-size: 0.75rem;
        }

        /* Botões da tabela */
        .btn-sm {
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-sm:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
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

        /* Garantir que o modal apareça corretamente */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1055;
            width: 100%;
            height: 100%;
            overflow: hidden;
            outline: 0;
            transition: none !important;
        }

        .modal.show {
            display: block !important;
        }

        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1050;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.5);
            transition: none !important;
        }

        .modal-backdrop.show {
            opacity: 0.5;
        }

        /* Garantir que o modal-dialog fique centralizado e estável */
        .modal-dialog {
            position: relative;
            width: auto;
            margin: 1.75rem auto;
            pointer-events: auto;
            /* permitir interação direta com o diálogo */
            max-width: 500px;
            transition: none !important;
            /* evitar reflows/animations ao mover o mouse */
            transform: none !important;
        }

        .modal-content {
            pointer-events: auto;
            background-color: #fff;
            border: 1px solid rgba(0, 0, 0, .2);
            border-radius: 0.3rem;
            outline: 0;
        }

        /* Garantir que o modal-dialog-sm não se mova */
        .modal-dialog.modal-sm {
            max-width: 300px;
            margin: 1.75rem auto;
        }

        /* Prevenir problemas de overflow */
        body.modal-open {
            overflow: hidden !important;
            padding-right: 0 !important;
        }

        /* Estilo dos itens de lista no modal */
        #statusModalGlobal .list-group-item {
            cursor: pointer;
            border: 1px solid rgba(0, 0, 0, .125);
            padding: 0.75rem 1.25rem;
            background-color: #fff;
            border-radius: 5px !important;
            margin-bottom: 5px;
            transition: background-color 0.2s ease, box-shadow 0.2s ease;
        }

        #statusModalGlobal .list-group-item:hover {
            background-color: #f8f9fa !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        #statusModalGlobal .list-group-item.active {
            background-color: #3498db !important;
            color: white !important;
            border-color: #3498db !important;
        }

        /* Lista de itens do pedido */
        .list-group-item {
            border: none;
            border-radius: 12px;
            margin-bottom: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .list-group-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        /* Botões de status */
        .list-group-item-action {
            border-radius: 8px;
            margin-bottom: 8px;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .list-group-item-action:hover {
            transform: translateX(5px);
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

    <div class="container history-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="history-title">Histórico de Pedidos</h1>
            <div>
                <a href="{{ route('store.dashboard') }}" class="btn btn-back">
                    <i class="fas fa-arrow-left me-2"></i> Voltar
                </a>
            </div>
        </div>

        <div class="card history-card">
            <div class="card-header">
                <h5 class="mb-0">Filtrar Pedidos</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('store.orders.history') }}" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Data Inicial</label>
                        <input type="date" class="form-control" id="start_date" name="start_date"
                            value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">Data Final</label>
                        <input type="date" class="form-control" id="end_date" name="end_date"
                            value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Todos</option>
                            <option value="Aguardando pagamento"
                                {{ request('status') == 'Aguardando pagamento' ? 'selected' : '' }}>Aguardando pagamento
                            </option>
                            <option value="Em produção" {{ request('status') == 'Em produção' ? 'selected' : '' }}>Em
                                produção</option>
                            <option value="Finalizado" {{ request('status') == 'Finalizado' ? 'selected' : '' }}>Finalizado
                            </option>
                            <option value="Cancelado" {{ request('status') == 'Cancelado' ? 'selected' : '' }}>Cancelado
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="table_id" class="form-label">Mesa</label>
                        <select class="form-select" id="table_id" name="table_id">
                            <option value="">Todas</option>
                            @foreach ($tables as $table)
                                <option value="{{ $table->id }}"
                                    {{ request('table_id') == $table->id ? 'selected' : '' }}>Mesa {{ $table->number }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-1"></i> Filtrar
                        </button>
                        <a href="{{ route('store.orders.history') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-undo me-1"></i> Limpar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card history-card">
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
                                    <th>Status</th>
                                    <th>Pagamento</th>
                                    <th>Data</th>
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
                                            <button type="button" class="btn btn-sm btn-primary change-status-btn"
                                                data-order-id="{{ $order->id }}"
                                                data-current-status="{{ $order->status }}">
                                                Mudar Status
                                            </button>

                                            @if ($order->payment_status !== 'paid')
                                                <button type="button" class="btn btn-sm btn-success ms-1"
                                                    onclick="markAsPaid({{ $order->id }})">
                                                    <i class="fas fa-dollar-sign me-1"></i>Marcar Pago
                                                </button>
                                            @endif
                                        </td>
                                    </tr>

                                    <!-- Modal de Detalhes do Pedido -->
                                    <div class="modal" id="orderModal{{ $order->id }}" tabindex="-1"
                                        data-bs-backdrop="static">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Pedido {{ $order->order_number }}</h5>
                                                    <button type="button" class="btn-close"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <strong>Mesa:</strong>
                                                        {{ $order->table ? $order->table->number : 'Balcão' }}
                                                    </div>
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
                    <div class="text-center py-4">
                        <i class="fas fa-history fa-3x mb-3 text-muted"></i>
                        <p class="h5 text-muted">Nenhum pedido encontrado.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('styles')
        <style>
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

            /* Garantir estabilidade do modal */
            .modal-dialog {
                transform: none !important;
            }

            .modal.fade .modal-dialog {
                transition: none !important;
            }

            /* While a modal is open, prevent hover effects and pointer events
                               on underlying page elements to avoid layout shifts/repaints. */
            body.modal-open .history-card,
            body.modal-open .table tbody tr,
            body.modal-open .history-container,
            body.modal-open .container {
                pointer-events: none !important;
            }

            /* Ensure modal itself remains interactive */
            body.modal-open .modal,
            body.modal-open .modal * {
                pointer-events: auto !important;
            }

            /* Disable hover transforms and shadows on background content while modal open */
            body.modal-open .history-card,
            body.modal-open .history-card:hover {
                transform: none !important;
                box-shadow: none !important;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Criar modal único para mudança de status
                const statusModalHTML = `
        <div class="modal" id="statusModalGlobal" tabindex="-1" role="dialog" style="display: none;">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Alterar Status</h5>
                        <button type="button" class="btn-close" onclick="closeStatusModal()"></button>
                    </div>
                    <div class="modal-body">
                        <div class="list-group" id="statusOptions">
                            <!-- Os status serão inseridos aqui -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop" id="statusModalBackdrop" style="display: none;"></div>
    `;

                // Adicionar modal ao body
                const modalContainer = document.createElement('div');
                modalContainer.innerHTML = statusModalHTML;
                document.body.appendChild(modalContainer);

                const statusModal = document.getElementById('statusModalGlobal');
                const statusBackdrop = document.getElementById('statusModalBackdrop');
                const statusOptions = document.getElementById('statusOptions');

                // Função para abrir o modal
                window.openStatusModal = function(orderId, currentStatus) {
                    const statuses = [
                        'Aguardando pagamento',
                        'Em produção',
                        'Finalizado',
                        'Cancelado'
                    ];

                    // Limpar opções anteriores
                    statusOptions.innerHTML = '';

                    // Criar opções de status
                    statuses.forEach(function(status) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `/store/orders/${orderId}/status`;
                        form.className = 'mb-2';

                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = '{{ csrf_token() }}';
                        form.appendChild(csrfInput);

                        const methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        methodInput.value = 'PUT';
                        form.appendChild(methodInput);

                        const statusInput = document.createElement('input');
                        statusInput.type = 'hidden';
                        statusInput.name = 'status';
                        statusInput.value = status;
                        form.appendChild(statusInput);

                        const button = document.createElement('button');
                        button.type = 'submit';
                        button.className = 'list-group-item list-group-item-action' + (currentStatus ===
                            status ? ' active' : '');
                        button.textContent = status;
                        form.appendChild(button);

                        statusOptions.appendChild(form);
                    });

                    // Mostrar modal e backdrop
                    statusBackdrop.style.display = 'block';
                    statusModal.style.display = 'block';
                    document.body.classList.add('modal-open');

                    // Pequeno delay para garantir renderização
                    setTimeout(function() {
                        statusModal.classList.add('show');
                        statusBackdrop.classList.add('show');
                    }, 10);
                };

                // Função para fechar o modal
                window.closeStatusModal = function() {
                    statusModal.classList.remove('show');
                    statusBackdrop.classList.remove('show');

                    setTimeout(function() {
                        statusModal.style.display = 'none';
                        statusBackdrop.style.display = 'none';
                        document.body.classList.remove('modal-open');
                        statusOptions.innerHTML = '';
                    }, 150);
                };

                // Adicionar event listener aos botões de mudança de status
                document.querySelectorAll('.change-status-btn').forEach(function(button) {
                    button.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        const orderId = this.getAttribute('data-order-id');
                        const currentStatus = this.getAttribute('data-current-status');
                        openStatusModal(orderId, currentStatus);
                    });
                });

                // Fechar ao clicar no backdrop
                statusBackdrop.addEventListener('click', closeStatusModal);
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
        </script>
    @endpush
@endsection

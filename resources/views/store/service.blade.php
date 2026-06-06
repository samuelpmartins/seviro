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
        .service-container {
            background: transparent;
            padding: 20px 0;
            margin-top: 0;
        }

        /* Título principal */
        .service-title {
            color: #e8e8e9;
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-align: left;
            letter-spacing: -0.02em;
        }

        [data-bs-theme="light"] .service-title {
            color: #000000;
        }

        /* Botão de atualizar */
        .btn-refresh {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            color: #e8e8e9;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .btn-refresh:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.4);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
    </style>

    <div class="container service-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="service-title">Tela de Atendimento</h1>
            <div>
                <button type="button" class="btn btn-refresh" id="refreshButton">
                    <i class="fas fa-sync-alt me-2"></i> Atualizar
                </button>
            </div>
        </div>

        <!-- Grid de Mesas -->
        <div class="row g-4 mb-4">
            @forelse($tables as $table)
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card h-100 table-card {{ $table->occupied ? 'occupied' : 'free' }}"
                        data-table-id="{{ $table->id }}" data-table-number="{{ $table->number }}"
                        data-unpaid-total="{{ $table->unpaid_total }}"
                        style="{{ $table->occupied ? 'background-color: #f8d7da; border: 2px solid #dc3545; cursor: pointer;' : 'background-color: #d1e7dd; border: 2px solid #198754; cursor: pointer;' }}">
                        <div class="card-body">
                            <h5 class="card-title">Mesa {{ $table->number }}</h5>
                            <div class="card-details">
                                <div class="table-info">
                                    <div class="mb-1">
                                        <strong>Status:</strong>
                                        <span class="status-text">
                                            {{ $table->occupied ? 'Ocupada' : 'Livre' }}
                                        </span>
                                    </div>
                                    <div class="mb-1">
                                        <strong>Total não pago:</strong>
                                        R$ {{ number_format($table->unpaid_total, 2, ',', '.') }}
                                    </div>
                                    <div>
                                        <strong>Pedidos:</strong>
                                        {{ $table->unpaid_count }}/{{ $table->total_orders }}
                                    </div>
                                    @if ($table->occupied && $table->occupied_at)
                                        <div class="mt-2">
                                            <strong>Ocupada há:</strong>
                                            {{ $table->occupied_at->diffForHumans(null, true) }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info">
                        Nenhuma mesa encontrada. <a href="{{ route('store.tables.create') }}">Criar uma mesa</a>.
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Modal de Opções da Mesa -->
    <div class="modal fade" id="tableOptionsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Opções - Mesa <span id="optionsTableNumber"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="d-grid gap-3">
                        <div id="clearTableOption">
                            <form id="clearTableForm" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="fas fa-door-open me-2"></i> Desocupar Mesa
                                </button>
                            </form>
                        </div>

                        <a href="#" id="viewOrdersLink" class="btn btn-info w-100">
                            <i class="fas fa-list-ul me-2"></i> Ver Pedidos
                        </a>

                        <div id="paymentOption">
                            <button type="button" class="btn btn-success w-100" id="showPaymentBtn">
                                <i class="fas fa-hand-holding-usd me-2"></i> Pagamento
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Pagamento -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-cash-register me-2"></i>Receber Pagamento - Mesa <span
                            id="tableNumber"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <h4>Total a Receber: <span id="unpaidTotal" class="fw-bold text-success"></span></h4>
                    </div>

                    <!-- Selecao de pedidos -->
                    <div id="orderSelectionSection">
                        <h6 class="mb-3"><i class="fas fa-list me-2"></i>Selecione os pedidos a pagar:</h6>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAllOrdersService">
                                <label class="form-check-label fw-bold" for="selectAllOrdersService">
                                    Selecionar todos os pedidos
                                </label>
                            </div>
                        </div>
                        <div id="ordersList" class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                            <!-- Os pedidos serão carregados aqui via JavaScript -->
                        </div>
                        <div class="mt-3 p-3 bg-light rounded">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Total Selecionado:</span>
                                <strong id="selectedTotalService" class="h5 text-success mb-0">R$ 0,00</strong>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Forma de Pagamento -->
                    <div id="paymentMethodSection" class="mt-4">
                        <h6 class="mb-3"><i class="fas fa-money-bill me-2"></i>Forma de Pagamento:</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-4">
                                <div class="form-check payment-type-card" style="cursor: pointer;">
                                    <input class="form-check-input" type="radio" name="paymentTypeService"
                                        id="paymentTypeCash" value="cash" checked>
                                    <label class="form-check-label" for="paymentTypeCash">
                                        <i class="fas fa-money-bill-wave fa-2x text-success d-block mb-1"></i>
                                        Dinheiro
                                    </label>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-check payment-type-card" style="cursor: pointer;">
                                    <input class="form-check-input" type="radio" name="paymentTypeService"
                                        id="paymentTypeCard" value="card">
                                    <label class="form-check-label" for="paymentTypeCard">
                                        <i class="fas fa-credit-card fa-2x text-primary d-block mb-1"></i>
                                        Crédito/Débito
                                    </label>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-check payment-type-card" style="cursor: pointer;">
                                    <input class="form-check-input" type="radio" name="paymentTypeService"
                                        id="paymentTypePix" value="pix">
                                    <label class="form-check-label" for="paymentTypePix">
                                        <i class="fas fa-qrcode fa-2x text-info d-block mb-1"></i>
                                        PIX
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Campos para pagamento em dinheiro -->
                        <div id="cashPaymentFields">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="cashReceived" class="form-label">Valor Recebido</label>
                                    <div class="input-group">
                                        <span class="input-group-text">R$</span>
                                        <input type="number" class="form-control form-control-lg" id="cashReceived"
                                            step="0.01" min="0" placeholder="0,00">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Troco</label>
                                    <div class="input-group">
                                        <span class="input-group-text">R$</span>
                                        <input type="text" class="form-control form-control-lg bg-light"
                                            id="changeAmount" readonly value="0,00">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Campo de observacoes -->
                        <div class="mt-3">
                            <label for="paymentNotes" class="form-label">Observacoes (opcional)</label>
                            <textarea class="form-control" id="paymentNotes" rows="2"
                                placeholder="Alguma observacao sobre o pagamento..."></textarea>
                        </div>
                    </div>

                    <!-- Formulario de pagamento em dinheiro -->
                    <form id="cashPaymentForm" method="POST" class="mt-4">
                        @csrf
                        <input type="hidden" name="order_ids[]" id="cashOrderIds">
                        <input type="hidden" name="cash_received" id="cashReceivedHidden">
                        <input type="hidden" name="notes" id="paymentNotesHidden">
                        <div id="selectedOrdersInputsCash"></div>
                        <button type="submit" class="btn btn-success btn-lg w-100" id="confirmPaymentBtn" disabled>
                            <i class="fas fa-check-circle me-2"></i>Confirmar Pagamento
                        </button>
                    </form>

                    <!-- Formularios antigos (mantidos para compatibilidade) -->
                    <form id="payFullForm" method="POST" style="display: none;">
                        @csrf
                    </form>
                    <form id="payPartialForm" method="POST" style="display: none;">
                        @csrf
                        @method('POST')
                        <div id="selectedOrdersInputs"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .table-card {
                background: white;
                border: none;
                border-radius: 16px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                transition: all 0.3s ease;
                overflow: hidden;
                border: 1px solid rgba(0, 0, 0, 0.05);
                cursor: pointer;
            }

            .table-card:hover {
                transform: translateY(-8px);
                box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
            }

            /* Cores mais fortes e usando !important para garantir aplicação */
            .table-card.free {
                background: white !important;
                border-left: 6px solid #198754 !important;
            }

            .table-card.occupied {
                background: white !important;
                border-left: 6px solid #dc3545 !important;
            }

            .table-card .card-body {
                padding: 25px;
            }

            /* Cores para texto de status */
            .table-card.free .status-text {
                color: #198754 !important;
                /* Verde */
                font-weight: bold;
            }

            .table-card.occupied .status-text {
                color: #dc3545 !important;
                /* Vermelho */
                font-weight: bold;
            }

            /* Temas escuros */
            [data-bs-theme="dark"] .table-card.free {
                background-color: #0f5132 !important;
                border: 2px solid #20c997 !important;
            }

            [data-bs-theme="dark"] .table-card.occupied {
                background-color: #842029 !important;
                border: 2px solid #ff6b6b !important;
            }

            [data-bs-theme="dark"] .table-card.free .status-text {
                color: #75b798 !important;
                /* Verde claro para tema escuro */
            }

            [data-bs-theme="dark"] .table-card.occupied .status-text {
                color: #ea868f !important;
                /* Vermelho claro para tema escuro */
            }

            /* Animação para mesas ocupadas há muito tempo */
            @keyframes pulseWarning {
                0% {
                    box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7);
                }

                70% {
                    box-shadow: 0 0 0 10px rgba(255, 193, 7, 0);
                }

                100% {
                    box-shadow: 0 0 0 0 rgba(255, 193, 7, 0);
                }
            }

            .table-warning {
                animation: pulseWarning 2s infinite;
            }

            /* Responsividade */
            @media (max-width: 576px) {
                .col-6 {
                    width: 100%;
                }
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

            /* Botões dos modais */
            .btn-danger,
            .btn-success,
            .btn-info,
            .btn-primary {
                border-radius: 12px;
                padding: 12px 24px;
                font-weight: 600;
                transition: all 0.3s ease;
                border: none;
            }

            .btn-danger:hover,
            .btn-success:hover,
            .btn-info:hover,
            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            }

            /* Checkbox personalizado */
            .custom-checkbox {
                display: block;
                position: relative;
                padding-left: 35px;
                margin-bottom: 12px;
                cursor: pointer;
                user-select: none;
            }

            .custom-checkbox input {
                position: absolute;
                opacity: 0;
                cursor: pointer;
            }

            .checkmark {
                position: absolute;
                top: 0;
                left: 0;
                height: 25px;
                width: 25px;
                background-color: #ecf0f1;
                border-radius: 8px;
                transition: all 0.3s ease;
            }

            .custom-checkbox:hover input~.checkmark {
                background-color: #d5dbdb;
            }

            .custom-checkbox input:checked~.checkmark {
                background-color: #3498db;
            }

            .checkmark:after {
                content: "";
                position: absolute;
                display: none;
            }

            .custom-checkbox input:checked~.checkmark:after {
                display: block;
            }

            .custom-checkbox .checkmark:after {
                left: 9px;
                top: 5px;
                width: 7px;
                height: 12px;
                border: solid white;
                border-width: 0 3px 3px 0;
                transform: rotate(45deg);
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Debug: Verificar se o script está sendo carregado
                console.log('Script carregado corretamente');

                // Debug: Verificar se as mesas são encontradas
                const tableCards = document.querySelectorAll('.table-card');
                console.log('Mesas encontradas:', tableCards.length);

                // Auto-refresh a cada 60 segundos
                const refreshInterval = setInterval(function() {
                    location.reload();
                }, 60000);

                // Botão de atualização manual
                document.getElementById('refreshButton').addEventListener('click', function() {
                    location.reload();
                });

                // Inicializar os modais
                let tableOptionsModal, paymentModal;

                if (typeof bootstrap !== 'undefined') {
                    const tableOptionsEl = document.getElementById('tableOptionsModal');
                    const paymentEl = document.getElementById('paymentModal');

                    tableOptionsModal = new bootstrap.Modal(tableOptionsEl);
                    paymentModal = new bootstrap.Modal(paymentEl);

                    // Corrigir problema de acessibilidade - remover aria-hidden quando o modal é mostrado
                    tableOptionsEl.addEventListener('shown.bs.modal', function() {
                        this.removeAttribute('aria-hidden');
                    });

                    paymentEl.addEventListener('shown.bs.modal', function() {
                        this.removeAttribute('aria-hidden');
                    });

                    console.log('Bootstrap disponível, modais inicializados');
                } else {
                    console.warn('Bootstrap não está disponível, usando fallback para modais');
                    // Fallback para quando bootstrap não está disponível
                    tableOptionsModal = {
                        show: function() {
                            const modalEl = document.getElementById('tableOptionsModal');
                            modalEl.classList.add('show');
                            modalEl.style.display = 'block';
                            modalEl.removeAttribute('aria-hidden');
                            document.body.classList.add('modal-open');

                            const backdrop = document.createElement('div');
                            backdrop.classList.add('modal-backdrop', 'fade', 'show');
                            document.body.appendChild(backdrop);
                        },
                        hide: function() {
                            const modalEl = document.getElementById('tableOptionsModal');
                            modalEl.classList.remove('show');
                            modalEl.style.display = 'none';
                            document.body.classList.remove('modal-open');

                            const backdrop = document.querySelector('.modal-backdrop');
                            if (backdrop) backdrop.remove();
                        }
                    };

                    paymentModal = {
                        show: function() {
                            const modalEl = document.getElementById('paymentModal');
                            modalEl.classList.add('show');
                            modalEl.style.display = 'block';
                            modalEl.removeAttribute('aria-hidden');
                            document.body.classList.add('modal-open');

                            if (!document.querySelector('.modal-backdrop')) {
                                const backdrop = document.createElement('div');
                                backdrop.classList.add('modal-backdrop', 'fade', 'show');
                                document.body.appendChild(backdrop);
                            }
                        },
                        hide: function() {
                            const modalEl = document.getElementById('paymentModal');
                            modalEl.classList.remove('show');
                            modalEl.style.display = 'none';
                            document.body.classList.remove('modal-open');

                            const backdrop = document.querySelector('.modal-backdrop');
                            if (backdrop) backdrop.remove();
                        }
                    };

                    // Fechar modais quando clicar em .btn-close
                    document.querySelectorAll('.btn-close, [data-bs-dismiss="modal"]').forEach(button => {
                        button.addEventListener('click', function() {
                            const modalId = this.closest('.modal').id;
                            if (modalId === 'tableOptionsModal') {
                                tableOptionsModal.hide();
                            } else if (modalId === 'paymentModal') {
                                paymentModal.hide();
                            }
                        });
                    });
                }

                let currentTableId = null;

                // Garantir que os cards tenham o estilo correto explicitamente
                tableCards.forEach(card => {
                    // Forçar o estilo inline como backup
                    if (card.classList.contains('free')) {
                        card.style.backgroundColor = '#d1e7dd';
                        card.style.border = '2px solid #198754';
                    } else if (card.classList.contains('occupied')) {
                        card.style.backgroundColor = '#f8d7da';
                        card.style.border = '2px solid #dc3545';
                    }

                    // Aplicar cursor pointer explicitamente
                    card.style.cursor = 'pointer';

                    // Configurar clique nas mesas para abrir modal de opções
                    card.addEventListener('click', function(e) {
                        e.preventDefault();
                        console.log('Card clicado: Mesa #' + this.getAttribute('data-table-number'));

                        const tableId = this.getAttribute('data-table-id');
                        const tableNumber = this.getAttribute('data-table-number');
                        const unpaidTotal = parseFloat(this.getAttribute('data-unpaid-total'));
                        const isOccupied = this.classList.contains('occupied');

                        // Configurar o modal de opções
                        document.getElementById('optionsTableNumber').textContent = tableNumber;

                        // Configurar o formulário de desocupar mesa
                        const clearTableForm = document.getElementById('clearTableForm');
                        clearTableForm.action = '/store/tables/' + tableId + '/clear';

                        // Mostrar/esconder opção de desocupar mesa
                        document.getElementById('clearTableOption').style.display = isOccupied ?
                            'block' : 'none';

                        // Configurar link de ver pedidos
                        const viewOrdersLink = document.getElementById('viewOrdersLink');
                        viewOrdersLink.href = '/store/orders/history?table_id=' + tableId +
                            '&start_date=' + new Date().toISOString().split('T')[0];

                        // Mostrar/esconder opção de pagamento
                        document.getElementById('paymentOption').style.display = unpaidTotal > 0 ?
                            'block' : 'none';

                        // Armazenar ID da mesa atual
                        currentTableId = tableId;

                        // Mostrar modal
                        tableOptionsModal.show();
                    });
                });

                // Variáveis para pagamento
                let selectedOrdersService = [];
                let selectedTotalService = 0;
                let currentTableInfo = null;

                // Botão de mostrar modal de pagamento
                document.getElementById('showPaymentBtn').addEventListener('click', function() {
                    // Esconder modal de opções
                    tableOptionsModal.hide();

                    // Buscar informações da mesa novamente
                    const cards = document.querySelectorAll('.table-card');
                    currentTableInfo = null;

                    cards.forEach(card => {
                        if (card.getAttribute('data-table-id') === currentTableId) {
                            currentTableInfo = {
                                id: card.getAttribute('data-table-id'),
                                number: card.getAttribute('data-table-number'),
                                unpaidTotal: parseFloat(card.getAttribute('data-unpaid-total'))
                            };
                        }
                    });

                    if (!currentTableInfo) return;

                    // Configurar modal de pagamento
                    const tableNumberEl = document.getElementById('tableNumber');
                    const unpaidTotalEl = document.getElementById('unpaidTotal');
                    const cashPaymentForm = document.getElementById('cashPaymentForm');

                    tableNumberEl.textContent = currentTableInfo.number;
                    unpaidTotalEl.textContent = 'R$ ' + currentTableInfo.unpaidTotal.toLocaleString('pt-BR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });

                    // Atualizar o action do formulário de pagamento em dinheiro
                    cashPaymentForm.action = '/store/service/table/' + currentTableInfo.id + '/cash-payment';

                    // Resetar campos
                    document.getElementById('cashReceived').value = '';
                    document.getElementById('changeAmount').value = '0,00';
                    document.getElementById('paymentNotes').value = '';
                    selectedOrdersService = [];
                    selectedTotalService = 0;
                    updateSelectedTotalService();

                    // Carregar pedidos
                    loadUnpaidOrders(currentTableId);

                    // Mostrar modal
                    setTimeout(() => {
                        paymentModal.show();
                    }, 300);
                });

                // Selecionar todos os pedidos
                document.getElementById('selectAllOrdersService')?.addEventListener('change', function() {
                    document.querySelectorAll('.order-checkbox-service').forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    updateSelectedOrdersService();
                });

                // Calcular troco ao digitar valor recebido
                document.getElementById('cashReceived')?.addEventListener('input', function() {
                    const received = parseFloat(this.value) || 0;
                    const change = Math.max(0, received - selectedTotalService);
                    document.getElementById('changeAmount').value = change.toLocaleString('pt-BR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                });

                // Função para atualizar total selecionado no service
                function updateSelectedTotalService() {
                    document.getElementById('selectedTotalService').textContent = 'R$ ' + selectedTotalService
                        .toLocaleString('pt-BR', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });

                    // Habilitar/desabilitar botão de confirmar
                    const confirmBtn = document.getElementById('confirmPaymentBtn');
                    confirmBtn.disabled = selectedOrdersService.length === 0;

                    // Atualizar troco
                    const received = parseFloat(document.getElementById('cashReceived').value) || 0;
                    const change = Math.max(0, received - selectedTotalService);
                    document.getElementById('changeAmount').value = change.toLocaleString('pt-BR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }

                // Atualizar pedidos selecionados
                window.updateSelectedOrdersService = function() {
                    selectedOrdersService = [];
                    selectedTotalService = 0;

                    document.querySelectorAll('.order-checkbox-service:checked').forEach(checkbox => {
                        selectedOrdersService.push(parseInt(checkbox.value));
                        selectedTotalService += parseFloat(checkbox.dataset.total);
                    });

                    updateSelectedTotalService();

                    // Atualizar inputs hidden do formulário
                    const selectedOrdersInputsCash = document.getElementById('selectedOrdersInputsCash');
                    selectedOrdersInputsCash.innerHTML = '';
                    selectedOrdersService.forEach(orderId => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'order_ids[]';
                        input.value = orderId;
                        selectedOrdersInputsCash.appendChild(input);
                    });
                };

                // Submeter formulário de pagamento em dinheiro
                document.getElementById('cashPaymentForm')?.addEventListener('submit', function(e) {
                    e.preventDefault();

                    if (selectedOrdersService.length === 0) {
                        alert('Selecione pelo menos um pedido para pagar.');
                        return;
                    }

                    const cashReceived = parseFloat(document.getElementById('cashReceived').value) || 0;

                    if (cashReceived < selectedTotalService) {
                        alert('O valor recebido deve ser maior ou igual ao total: R$ ' + selectedTotalService
                            .toLocaleString('pt-BR', {
                                minimumFractionDigits: 2
                            }));
                        return;
                    }

                    // Preencher campos hidden
                    document.getElementById('cashReceivedHidden').value = cashReceived;
                    document.getElementById('paymentNotesHidden').value = document.getElementById(
                        'paymentNotes').value;

                    // Submeter formulário
                    this.submit();
                });

                // Função para carregar pedidos não pagos
                function loadUnpaidOrders(tableId) {
                    const ordersList = document.getElementById('ordersList');

                    // Mostrar loading
                    ordersList.innerHTML =
                        '<div class="text-center py-3"><div class="spinner-border text-primary" role="status"></div></div>';

                    fetch('/api/tables/' + tableId + '/unpaid-orders')
                        .then(response => response.json())
                        .then(data => {
                            if (data.orders.length === 0) {
                                ordersList.innerHTML =
                                    '<div class="alert alert-info mb-0">Nenhum pedido pendente encontrado.</div>';
                                return;
                            }

                            // Criar lista de pedidos com checkboxes
                            let html = '';

                            data.orders.forEach(order => {
                                const dateFormatted = new Date(order.created_at).toLocaleString('pt-BR');
                                const total = parseFloat(order.total) || 0;
                                const formattedTotal = total.toLocaleString('pt-BR', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });

                                html += `
                        <div class="form-check border-bottom py-2">
                            <input class="form-check-input order-checkbox-service" type="checkbox" 
                                   value="${order.id}" 
                                   data-total="${total}"
                                   id="order-service-${order.id}"
                                   onchange="updateSelectedOrdersService()">
                            <label class="form-check-label w-100" for="order-service-${order.id}">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong>Pedido #${order.order_number || order.id}</strong>
                                        <small class="d-block text-muted">${dateFormatted}</small>
                                        <small class="text-muted">${order.items ? order.items.length + ' itens' : ''}</small>
                                    </div>
                                    <div class="text-end">
                                        <strong class="text-success">R$ ${formattedTotal}</strong>
                                        <small class="d-block badge bg-${order.payment_status === 'pending' ? 'warning' : 'success'}">${order.payment_status === 'pending' ? 'Pendente' : order.payment_status}</small>
                                    </div>
                                </div>
                            </label>
                        </div>
                    `;
                            });

                            ordersList.innerHTML = html;
                        })
                        .catch(error => {
                            ordersList.innerHTML =
                                '<div class="alert alert-danger mb-0">Erro ao carregar pedidos.</div>';
                            console.error(error);
                        });
                }

                // Função para atualizar pedidos selecionados (mantida para compatibilidade)
                window.updateSelectedOrders = function() {
                    const checkboxes = document.querySelectorAll('input[name="order_checkbox"]:checked');
                    const selectedOrdersInputs = document.getElementById('selectedOrdersInputs');

                    // Limpar inputs anteriores
                    selectedOrdersInputs.innerHTML = '';

                    // Adicionar um input para cada pedido selecionado
                    checkboxes.forEach(checkbox => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'order_ids[]';
                        input.value = checkbox.value;
                        selectedOrdersInputs.appendChild(input);
                    });
                };
            });
        </script>
    @endpush
@endsection

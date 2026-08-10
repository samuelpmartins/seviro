@extends('layouts.app')

@section('content')
    <style>
        /* Fundo escuro */
        body {
            background: #1a1a2e;
            color: #e8e8e9;
            min-height: 100vh;
            margin: 0;
        }

        /* Header da cozinha */
        .kitchen-header {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            padding: 20px 0;
            margin-bottom: 30px;
        }

        .kitchen-header h1 {
            color: white;
            font-weight: 700;
            margin: 0;
        }

        .kitchen-header .store-name {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1rem;
        }

        /* Container */
        .kitchen-container {
            padding: 0 20px 40px;
        }

        /* Cards de pedidos */
        .orders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }

        .order-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }

        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
        }

        .order-card.waiting {
            border-left: 5px solid #f39c12;
        }

        .order-card.in-production {
            border-left: 5px solid #3498db;
        }

        /* Header do card */
        .order-card-header {
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ecf0f1;
        }

        .order-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
        }

        .order-table {
            background: #3498db;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .order-time {
            color: #7f8c8d;
            font-size: 0.85rem;
            margin-top: 5px;
        }

        .order-time.urgent {
            color: #e74c3c;
            font-weight: 600;
        }

        /* Status badge */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-badge.waiting {
            background: #f39c12;
            color: white;
        }

        .status-badge.in-production {
            background: #3498db;
            color: white;
        }

        /* Body do card */
        .order-card-body {
            padding: 20px;
        }

        /* Itens do pedido */
        .order-items {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .order-item {
            display: flex;
            align-items: flex-start;
            padding: 12px 0;
            border-bottom: 1px dashed #ecf0f1;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .item-quantity {
            background: #2c3e50;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-right: 12px;
            flex-shrink: 0;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-weight: 600;
            color: #2c3e50;
            font-size: 1rem;
        }

        .item-notes {
            color: #e74c3c;
            font-size: 0.85rem;
            margin-top: 4px;
            font-style: italic;
        }

        .item-quick {
            background: #17a2b8;
            color: white;
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 10px;
            margin-left: 8px;
        }

        /* Observação geral */
        .order-notes {
            background: #fff3cd;
            border-radius: 8px;
            padding: 12px;
            margin-top: 15px;
            color: #856404;
            font-size: 0.9rem;
        }

        .order-notes strong {
            display: block;
            margin-bottom: 4px;
        }

        /* Cliente/Participante */
        .order-customer {
            background: #e8f4f8;
            border-radius: 8px;
            padding: 10px 12px;
            margin-top: 10px;
            color: #2980b9;
            font-size: 0.85rem;
        }

        /* Footer do card (botões) */
        .order-card-footer {
            padding: 15px 20px;
            background: #f8f9fa;
            display: flex;
            gap: 10px;
        }

        .order-card-footer .btn {
            flex: 1;
            padding: 12px;
            font-weight: 600;
            border-radius: 10px;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-production {
            background: #3498db;
            color: white;
        }

        .btn-production:hover {
            background: #2980b9;
            color: white;
            transform: translateY(-2px);
        }

        .btn-done {
            background: #27ae60;
            color: white;
        }

        .btn-done:hover {
            background: #1e8449;
            color: white;
            transform: translateY(-2px);
        }

        /* Estado vazio */
        .empty-state {
            text-align: center;
            padding: 100px 20px;
        }

        .empty-state i {
            font-size: 5rem;
            color: #4a4a6a;
            margin-bottom: 20px;
        }

        .empty-state h2 {
            color: #e8e8e9;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #9da1a1;
        }

        /* Contador de pedidos */
        .orders-counter {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }

        .counter-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 20px 30px;
            text-align: center;
        }

        .counter-card h3 {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
        }

        .counter-card p {
            margin: 5px 0 0;
            opacity: 0.8;
        }

        .counter-card.waiting h3 {
            color: #f39c12;
        }

        .counter-card.production h3 {
            color: #3498db;
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

        /* Auto-refresh indicator */
        .refresh-indicator {
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .refresh-indicator i {
            animation: spin 2s linear infinite;
        }

        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }

        /* Printer modal: centered square-like responsive modal matching other app modals */
        .modal.printer-modal .modal-dialog {
            max-width: 720px;
            width: 100%;
            margin: 1.75rem auto;
        }

        .modal.printer-modal .modal-content {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.45);
        }

        .modal.printer-modal .modal-body {
            padding: 1.25rem 1.5rem;
        }

        .modal.printer-modal .modal-header,
        .modal.printer-modal .modal-footer {
            border: none;
            padding: 0.75rem 1.25rem;
        }

        @media (max-width: 576px) {
            .modal.printer-modal .modal-dialog {
                margin: 0.75rem;
            }
        }
    </style>

    <!-- Header -->
    <div class="kitchen-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-utensils me-3"></i>Painel da Cozinha</h1>
                    <p class="store-name mb-0">{{ $store->name }}</p>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="refresh-indicator">
                        <i class="fas fa-sync-alt"></i>
                        <span>Atualiza a cada 30s</span>
                    </div>
                    <button type="button" class="btn btn-logout" data-bs-toggle="modal" data-bs-target="#printerConfigModal">
                        <i class="fas fa-print me-2"></i>Configurar Impressora
                    </button>
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

    <!-- order detail modal removed; per-order print indicators live inside each card -->

    <div class="container kitchen-container">
        <!-- Contadores -->
        <div class="orders-counter">
            <div class="counter-card waiting">
                <h3>{{ $orders->where('status', 'Aguardando pagamento')->count() }}</h3>
                <p>Aguardando</p>
            </div>
            <div class="counter-card production">
                <h3>{{ $orders->where('status', 'Em produção')->count() }}</h3>
                <p>Em Produção</p>
            </div>
        </div>

        @include('kitchen._orders_grid')
    </div>

    <!-- Modal Configuração de Impressora -->
    <div class="modal fade printer-modal" id="printerConfigModal" tabindex="-1" aria-labelledby="printerConfigModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="printerConfigModalLabel">Configuração de Impressora</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    @php
                        $printerAgentBaseUrl = config('app.printer_agent_base_url', '');
                    @endphp

                    <div class="mb-3">
                        <label class="form-label">Endereço do Agent</label>
                        <div class="form-control bg-light text-dark" style="min-height: 38px;">
                            {{ $printerAgentBaseUrl ?: 'Não configurado' }}
                        </div>
                        <div class="form-text">Este valor vem do ambiente da aplicação e é usado automaticamente.</div>
                    </div>

                    @if (empty($printerAgentBaseUrl))
                        <div class="alert alert-warning" role="alert">
                            Não foi possível encontrar a URL do Agent. Configure <strong>PRINTER_AGENT_BASE_URL</strong> no
                            arquivo <strong>.env</strong>.
                        </div>
                    @endif

                    <div class="d-grid gap-2 mb-3">
                        <button type="button" id="printer-connect-btn" class="btn btn-primary">Conectar</button>
                    </div>

                    <div id="printer-connection-status" class="mb-3"></div>

                    <div id="printer-android-section" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">Impressoras Bluetooth encontradas</label>
                            <div id="printer-android-list" class="list-group"></div>
                        </div>
                    </div>

                    <div id="printer-selected-info" class="alert alert-info d-none"></div>

                    <!-- (removed per-order failed list from printer modal) -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const printerStorageKeys = {
            agentUrl: 'kitchenPrinterAgentUrl',
            agentModel: 'kitchenPrinterAgentModel',
            printerAddress: 'kitchenPrinterAddress',
            connected: 'kitchenPrinterConnected'
        };

        function normalizeAgentUrl(value) {
            let url = (value || '').trim();
            if (!url) {
                return '';
            }

            if (!/^https?:\/\//i.test(url)) {
                url = 'http://' + url;
            }

            return url.replace(/\/+$/, '');
        }

        function getPrinterConfig() {
            return {
                agentUrl: localStorage.getItem(printerStorageKeys.agentUrl) || '',
                agentModel: localStorage.getItem(printerStorageKeys.agentModel) || '',
                printerAddress: localStorage.getItem(printerStorageKeys.printerAddress) || '',
                connected: localStorage.getItem(printerStorageKeys.connected) === 'true'
            };
        }

        function savePrinterConfig(config) {
            if (config.agentUrl !== undefined) {
                localStorage.setItem(printerStorageKeys.agentUrl, config.agentUrl);
            }
            if (config.agentModel !== undefined) {
                localStorage.setItem(printerStorageKeys.agentModel, config.agentModel);
            }
            if (config.printerAddress !== undefined) {
                localStorage.setItem(printerStorageKeys.printerAddress, config.printerAddress);
            }
            if (config.connected !== undefined) {
                localStorage.setItem(printerStorageKeys.connected, config.connected ? 'true' : 'false');
            }
        }

        function setPrinterStatus(message, type = 'info') {
            const status = document.getElementById('printer-connection-status');
            const allowedTypes = ['info', 'success', 'warning', 'danger'];
            const alertType = allowedTypes.includes(type) ? type : 'info';

            status.className = 'alert alert-' + alertType;
            status.textContent = message;
            status.style.display = 'block';
        }

        function hidePrinterStatus() {
            const status = document.getElementById('printer-connection-status');
            status.style.display = 'none';
        }

        function renderAndroidPrinters(printers) {
            const list = document.getElementById('printer-android-list');
            list.innerHTML = '';

            if (!Array.isArray(printers) || printers.length === 0) {
                list.innerHTML = '<div class="text-muted mt-2">Nenhuma impressora Bluetooth encontrada.</div>';
                return;
            }

            printers.forEach((printer, index) => {
                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'list-group-item list-group-item-action';
                item.innerHTML = `
                <div class="d-flex w-100 justify-content-between">
                    <strong>${printer.Name}</strong>
                    <small>${printer.Address}</small>
                </div>
                <p class="mb-0 text-muted">Conectado: ${printer.IsConnected ? 'Sim' : 'Não'} · Pareado: ${printer.IsPaired ? 'Sim' : 'Não'}</p>
            `;
                item.onclick = () => selectPrinter(printer);
                list.appendChild(item);
            });
        }

        function selectPrinter(printer) {
            savePrinterConfig({
                printerAddress: printer.Address,
                connected: true
            });
            const selectedInfo = document.getElementById('printer-selected-info');
            selectedInfo.classList.remove('d-none');
            selectedInfo.textContent = `Impressora selecionada: ${printer.Name} (${printer.Address})`;
        }

        async function connectPrinterAgent() {
            hidePrinterStatus();
            const connectButton = document.getElementById('printer-connect-btn');
            connectButton.disabled = true;

            try {
                const baseUrl = normalizeAgentUrl('{{ $printerAgentBaseUrl }}');

                if (!baseUrl) {
                    setPrinterStatus('A URL do Agent não está configurada.', 'warning');
                    return;
                }

                const response = await fetch(`${baseUrl}/status`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                    }
                });

                if (!response.ok) {
                    setPrinterStatus('Falha ao conectar com o Agent. Verifique o endereço e tente novamente.',
                        'danger');
                    return;
                }

                const result = await response.json();

                if (!result.success || !result.model) {
                    setPrinterStatus('Resposta inesperada do Agent. Verifique a configuração.', 'danger');
                    return;
                }

                savePrinterConfig({
                    agentUrl: baseUrl,
                    connected: true,
                    agentModel: result.model,
                    printerAddress: ''
                });

                if (result.model === 'Windows') {
                    setPrinterStatus(
                        'Agent Windows conectado com sucesso. Pronto para impressão local.',
                        'success');
                    document.getElementById('printer-android-section').style.display = 'none';
                    document.getElementById('printer-selected-info').classList.add('d-none');
                } else if (result.model === 'Android') {
                    setPrinterStatus('Agent Android conectado. Buscando impressoras Bluetooth...', 'info');
                    document.getElementById('printer-android-section').style.display = 'block';
                    await fetchAndroidPrinters(baseUrl);
                } else {
                    setPrinterStatus(`Modelo de Agent não suportado: ${result.model}`, 'danger');
                }
            } catch (error) {
                console.error('Erro ao conectar com o Agent:', error);
                setPrinterStatus('Erro ao conectar com o Agent. Confira se o dispositivo está acessível.', 'danger');
            } finally {
                connectButton.disabled = false;
            }
        }

        async function fetchAndroidPrinters(baseUrl) {
            try {
                const response = await fetch(`${baseUrl}/printers`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    setPrinterStatus('Falha ao buscar impressoras Bluetooth.', 'danger');
                    return;
                }

                const data = await response.json();
                const printers = Array.isArray(data) ? data : data.Printers || [];
                renderAndroidPrinters(printers);
                setPrinterStatus(printers.length > 0 ? 'Selecione a impressora Bluetooth desejada.' :
                    'Nenhuma impressora encontrada.', printers.length > 0 ? 'success' : 'warning');
            } catch (error) {
                console.error('Erro ao buscar impressoras Bluetooth:', error);
                setPrinterStatus('Erro ao buscar impressoras Bluetooth. Verifique a conexão do Agent.', 'danger');
            }
        }

        function loadPrinterConfig() {
            const config = getPrinterConfig();

            if (config.connected && config.agentModel === 'Android' && config.printerAddress) {
                document.getElementById('printer-selected-info').classList.remove('d-none');
                document.getElementById('printer-selected-info').textContent =
                    `Impressora selecionada: ${config.printerAddress}`;
                document.getElementById('printer-android-section').style.display = 'block';
            } else {
                document.getElementById('printer-selected-info').classList.add('d-none');
                document.getElementById('printer-android-section').style.display = 'none';
            }

            if (config.connected && config.agentModel === 'Windows') {
                setPrinterStatus('Agent Windows configurado. Pronto para impressão.', 'success');
            } else if (config.connected && config.agentModel === 'Android') {
                setPrinterStatus('Agent Android configurado. Selecione uma impressora Bluetooth.', 'info');
            } else {
                hidePrinterStatus();
            }

            // pending prints list rendering removed from modal; indicators are per-order in grid
        }

        document.addEventListener('DOMContentLoaded', function() {
            const connectButton = document.getElementById('printer-connect-btn');
            if (connectButton) {
                connectButton.addEventListener('click', connectPrinterAgent);
            }

            const printerModal = document.getElementById('printerConfigModal');
            if (printerModal) {
                printerModal.addEventListener('shown.bs.modal', loadPrinterConfig);
            }

            // Expose getFailedPrints globally for other scripts

            function getFailedPrints() {
                try {
                    return JSON.parse(localStorage.getItem('kitchenFailedPrints') || '[]');
                } catch (e) {
                    return [];
                }
            }

            // expose for other code
            window.getFailedPrints = getFailedPrints;


            if (window.notificationSystem && typeof window.notificationSystem.fetchNotifications === 'function') {
                console.info(
                    '[KitchenDashboard] NotificationSystem encontrado; iniciando polling de notificações.');
                window.notificationSystem.fetchNotifications();
            } else {
                console.warn('[KitchenDashboard] NotificationSystem não foi inicializado nesta página.');
            }
        });
    </script>

    <script>
        // Poll parcial de pedidos para atualizar a grid sem recarregar a página inteira
        const KITCHEN_PARTIAL_URL = '{{ route('kitchen.orders.partial') }}';
        const KITCHEN_POLL_INTERVAL = 30000; // 30s

        function getPrintedOrders() {
            try {
                const raw = localStorage.getItem('kitchenPrintedOrders') || '{}';
                return JSON.parse(raw);
            } catch (e) {
                return {};
            }
        }

        function setPrintedOrder(orderId) {
            const map = getPrintedOrders();
            map[orderId] = new Date().toISOString();
            localStorage.setItem('kitchenPrintedOrders', JSON.stringify(map));
            applyPrintedBadges();
        }

        function applyPrintedBadges() {
            const map = getPrintedOrders();
            Object.keys(map).forEach(id => {
                const card = document.querySelector(`.order-card[data-order-id="${id}"]`);
                if (card) {
                    const badge = card.querySelector('.printed-badge');
                    if (badge) badge.classList.remove('d-none');
                }
            });
        }

        function updateKitchenCounters() {
            const waitingCount = document.querySelectorAll('.order-card[data-order-status="Aguardando pagamento"]').length;
            const productionCount = document.querySelectorAll('.order-card[data-order-status="Em produção"]').length;
            const waitingEl = document.querySelector('.orders-counter .counter-card.waiting h3');
            const productionEl = document.querySelector('.orders-counter .counter-card.production h3');

            if (waitingEl) waitingEl.textContent = waitingCount;
            if (productionEl) productionEl.textContent = productionCount;
        }

        function applyFailedPrintIndicators() {
            const failed = (window.getFailedPrints ? window.getFailedPrints() : (JSON.parse(localStorage.getItem(
                'kitchenFailedPrints') || '[]')));
            document.querySelectorAll('.order-card').forEach(card => {
                const oid = card.getAttribute('data-order-id');
                const indicator = card.querySelector('.order-print-failed');
                if (!indicator) return;
                const retryBtn = indicator.querySelector('.order-retry-btn');

                const isFailed = failed && failed.some(id => String(id) === String(oid));
                if (isFailed) {
                    indicator.classList.remove('d-none');
                } else {
                    indicator.classList.add('d-none');
                }

                // disable retry button if user already clicked retry previously
                if (retryBtn && window.notificationSystem && typeof window.notificationSystem.retryClicked ===
                    'function') {
                    try {
                        if (window.notificationSystem.retryClicked(oid)) {
                            retryBtn.disabled = true;
                            retryBtn.textContent = 'Enviado';
                        }
                    } catch (e) {
                        /* ignore */
                    }
                }

                if (retryBtn) {
                    retryBtn.onclick = async () => {
                        // disable button after single click to avoid duplicates
                        retryBtn.disabled = true;

                        const original = retryBtn.textContent;
                        retryBtn.textContent = 'Enviando...';
                        try {
                            if (window.notificationSystem && typeof window.notificationSystem.printOrder ===
                                'function') {
                                const res = await window.notificationSystem.printOrder(oid);

                                if (res && res.status === 200) {

                                } else {
                                    retryBtn.disabled = true;
                                    retryBtn.textContent = 'Falha';
                                }
                            }
                        } catch (err) {
                            console.error('Erro ao reenviar impressão do pedido', oid, err);
                            retryBtn.textContent = 'Falha';
                        }
                    };
                }
            });
        }

        async function refreshOrdersPartial() {
            try {
                const res = await fetch(KITCHEN_PARTIAL_URL, {
                    credentials: 'same-origin'
                });
                if (!res.ok) return;
                const html = await res.text();
                const container = document.querySelector('.kitchen-container');
                // Replace only the orders-grid / empty-state area inside container
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newGrid = doc.querySelector('.orders-grid') || doc.querySelector('.empty-state');
                const oldGrid = document.querySelector('.orders-grid') || document.querySelector('.empty-state');
                if (newGrid && oldGrid && oldGrid.parentElement) {
                    oldGrid.parentElement.replaceChild(newGrid, oldGrid);
                }

                // Re-apply printed badges and failed indicators after the grid refresh
                applyPrintedBadges();
                updateKitchenCounters();
                try {
                    applyFailedPrintIndicators();
                } catch (e) {
                    console.error(e);
                }
            } catch (error) {
                console.error('Erro ao atualizar pedidos (partial):', error);
            }
        }

        // Start periodic polling without interfering with printer config stored in localStorage
        setInterval(() => {
            refreshOrdersPartial();
        }, KITCHEN_POLL_INTERVAL);

        // Apply badges, failed-print indicators and counters on initial load
        document.addEventListener('DOMContentLoaded', () => {
            applyPrintedBadges();
            updateKitchenCounters();
            try {
                applyFailedPrintIndicators();
            } catch (e) {
                console.error(e);
            }
        });

        // per-order modal click handler removed — per-order indicators live in each card
    </script>
@endsection

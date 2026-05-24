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
    
    /* Grid de mesas */
    .tables-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }
    
    /* Card de mesa */
    .table-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        transition: all 0.3s ease;
    }
    
    .table-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
    }
    
    .table-card.available {
        border-top: 5px solid #27ae60;
    }
    
    .table-card.occupied {
        border-top: 5px solid #e74c3c;
    }
    
    /* Header do card */
    .table-card-header {
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #ecf0f1;
    }
    
    .table-number {
        font-size: 1.8rem;
        font-weight: 700;
        color: #2c3e50;
    }
    
    .table-status {
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
    }
    
    .table-status.available {
        background: #d4edda;
        color: #155724;
    }
    
    .table-status.occupied {
        background: #f8d7da;
        color: #721c24;
    }
    
    /* Body do card */
    .table-card-body {
        padding: 20px;
    }
    
    /* Info da mesa */
    .table-info {
        margin-bottom: 15px;
    }
    
    .table-info-item {
        display: flex;
        align-items: center;
        padding: 8px 0;
        color: #7f8c8d;
        font-size: 0.9rem;
    }
    
    .table-info-item i {
        width: 24px;
        margin-right: 10px;
        color: #3498db;
    }
    
    .table-info-item strong {
        color: #2c3e50;
        margin-left: 5px;
    }
    
    /* Participantes */
    .participants-list {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
    }
    
    .participants-title {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 10px;
        font-size: 0.9rem;
    }
    
    .participant-item {
        display: flex;
        align-items: center;
        padding: 6px 0;
        font-size: 0.85rem;
        color: #34495e;
    }
    
    .participant-item i {
        margin-right: 8px;
        color: #3498db;
    }
    
    /* Total pendente */
    .pending-total {
        background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
        color: white;
        padding: 15px;
        border-radius: 10px;
        text-align: center;
    }
    
    .pending-total .amount {
        font-size: 1.5rem;
        font-weight: 700;
    }
    
    .pending-total .label {
        font-size: 0.85rem;
        opacity: 0.9;
    }
    
    /* Footer do card */
    .table-card-footer {
        padding: 15px 20px;
        background: #f8f9fa;
        display: flex;
        gap: 10px;
    }
    
    .table-card-footer .btn {
        flex: 1;
        padding: 12px;
        font-weight: 600;
        border-radius: 10px;
        border: none;
        transition: all 0.3s ease;
    }
    
    .table-card-footer .btn-primary {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        color: white;
    }
    
    .table-card-footer .btn-primary:hover {
        background: linear-gradient(135deg, #2980b9 0%, #2471a3 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
    }
    
    .table-card-footer .btn-danger {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        color: white;
    }
    
    .table-card-footer .btn-danger:hover {
        background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(231, 76, 60, 0.4);
    }
    
    /* Resumo dos pedidos */
    .orders-summary {
        margin-top: 15px;
    }
    
    .orders-summary-title {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 10px;
        font-size: 0.9rem;
    }
    
    .order-mini {
        background: #ecf0f1;
        border-radius: 8px;
        padding: 10px;
        margin-bottom: 8px;
        font-size: 0.85rem;
    }
    
    .order-mini-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 5px;
    }
    
    .order-mini-number {
        font-weight: 600;
        color: #2c3e50;
    }
    
    .order-mini-status {
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .order-mini-status.waiting {
        background: #f39c12;
        color: white;
    }
    
    .order-mini-status.production {
        background: #3498db;
        color: white;
    }
    
    .order-mini-status.done {
        background: #27ae60;
        color: white;
    }
    
    /* Estado vazio */
    .empty-table {
        text-align: center;
        padding: 20px;
        color: #bdc3c7;
    }
    
    .empty-table i {
        font-size: 2rem;
        margin-bottom: 10px;
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
    
    /* Contador */
    .tables-counter {
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
    
    .counter-card.available h3 {
        color: #27ae60;
    }
    
    .counter-card.occupied h3 {
        color: #e74c3c;
    }
    
    /* Hover do pedido */
    .order-mini:hover {
        transform: translateX(5px);
        background: #dfe4ea !important;
    }
    
    /* Modal de detalhes do pedido */
    .order-details-modal {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 10001;
        animation: fadeIn 0.3s ease;
    }
    
    .order-details-modal.show {
        display: flex !important;
    }
    
    .order-details-content {
        background: white;
        border-radius: 20px;
        max-width: 600px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 10px 50px rgba(0, 0, 0, 0.5);
        animation: slideUp 0.3s ease;
    }
    
    .order-details-header {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        color: white;
        padding: 25px;
        border-radius: 20px 20px 0 0;
        position: sticky;
        top: 0;
        z-index: 1;
    }
    
    .order-details-header h3 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
    }
    
    .order-details-header .order-meta {
        display: flex;
        gap: 15px;
        margin-top: 10px;
        font-size: 0.9rem;
        opacity: 0.9;
    }
    
    .order-details-body {
        padding: 25px;
    }
    
    .order-info-section {
        margin-bottom: 20px;
    }
    
    .order-info-section h4 {
        color: #2c3e50;
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .order-info-section h4 i {
        color: #3498db;
    }
    
    .order-item-card {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 10px;
        display: flex;
        justify-content: space-between;
        align-items: start;
    }
    
    .order-item-info {
        flex: 1;
    }
    
    .order-item-name {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 5px;
    }
    
    .order-item-details {
        font-size: 0.85rem;
        color: #7f8c8d;
    }
    
    .order-item-price {
        font-weight: 700;
        color: #27ae60;
        font-size: 1.1rem;
    }
    
    .order-total-box {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        color: white;
        padding: 20px;
        border-radius: 12px;
        text-align: center;
        margin-top: 20px;
    }
    
    .order-total-box .label {
        font-size: 0.9rem;
        opacity: 0.9;
        margin-bottom: 5px;
    }
    
    .order-total-box .amount {
        font-size: 2rem;
        font-weight: 700;
    }
    
    .order-details-footer {
        padding: 20px 25px;
        background: #f8f9fa;
        border-radius: 0 0 20px 20px;
        display: flex;
        gap: 10px;
    }
    
    .order-details-footer button {
        flex: 1;
        padding: 12px;
        border-radius: 10px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-close-modal {
        background: #ecf0f1;
        color: #2c3e50;
    }
    
    .btn-close-modal:hover {
        background: #bdc3c7;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes slideUp {
        from { transform: translateY(50px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
</style>

<!-- Header -->
<div class="waiter-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fas fa-concierge-bell me-3"></i>Painel do Garçom</h1>
                <p class="store-name mb-0">{{ $store->name }}</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <nav class="waiter-nav">
                    <a href="{{ route('waiter.dashboard') }}" class="active">
                        <i class="fas fa-th-large me-2"></i>Mesas
                    </a>
                    <a href="{{ route('waiter.history') }}">
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
    <!-- Contadores -->
    <div class="tables-counter">
        <div class="counter-card available">
            <h3>{{ $tables->where('occupied', false)->count() }}</h3>
            <p>Disponíveis</p>
        </div>
        <div class="counter-card occupied">
            <h3>{{ $tables->where('occupied', true)->count() }}</h3>
            <p>Ocupadas</p>
        </div>
    </div>

    <div class="tables-grid">
        @foreach($tables as $table)
            <div class="table-card {{ $table->occupied ? 'occupied' : 'available' }}">
                <div class="table-card-header">
                    <span class="table-number">Mesa {{ $table->number }}</span>
                    <span class="table-status {{ $table->occupied ? 'occupied' : 'available' }}">
                        {{ $table->occupied ? 'Ocupada' : 'Disponível' }}
                    </span>
                </div>
                
                <div class="table-card-body">
                    @if($table->occupied)
                        <div class="table-info">
                            @if($table->current_user_name)
                                <div class="table-info-item">
                                    <i class="fas fa-user"></i>
                                    Responsável: <strong>{{ $table->current_user_name }}</strong>
                                </div>
                            @endif
                            @if($table->occupied_at)
                                <div class="table-info-item">
                                    <i class="fas fa-clock"></i>
                                    Ocupada há: <strong>{{ $table->occupied_at->diffForHumans() }}</strong>
                                </div>
                            @endif
                        </div>
                        
                        @if($table->participants && $table->participants->count() > 0)
                            <div class="participants-list">
                                <div class="participants-title">
                                    <i class="fas fa-users me-1"></i> Participantes ({{ $table->participants->count() }})
                                </div>
                                @foreach($table->participants->take(5) as $participant)
                                    <div class="participant-item">
                                        <i class="fas fa-user-circle"></i>
                                        {{ $participant->name }}
                                    </div>
                                @endforeach
                                @if($table->participants->count() > 5)
                                    <div class="participant-item text-muted">
                                        <i class="fas fa-ellipsis-h"></i>
                                        +{{ $table->participants->count() - 5 }} mais
                                    </div>
                                @endif
                            </div>
                        @endif
                        
                        @if($table->orders && $table->orders->count() > 0)
                            <div class="orders-summary">
                                <div class="orders-summary-title">
                                    <i class="fas fa-receipt me-1"></i> Pedidos ({{ $table->orders->count() }})
                                </div>
                                @foreach($table->orders->take(3) as $order)
                                    <div class="order-mini" onclick="showOrderDetails({{ $order->id }})" style="cursor: pointer; transition: transform 0.2s;">
                                        <div class="order-mini-header">
                                            <span class="order-mini-number">#{{ $order->order_number }}</span>
                                            <span class="order-mini-status {{ $order->status === 'Aguardando pagamento' ? 'waiting' : ($order->status === 'Em produção' ? 'production' : 'done') }}">
                                                {{ $order->status }}
                                            </span>
                                        </div>
                                        <div class="text-muted">
                                            {{ $order->items->sum('quantity') }} itens - R$ {{ number_format($order->total, 2, ',', '.') }}
                                            @if($order->participant)
                                                <br><small><i class="fas fa-user me-1"></i>{{ $order->participant->name }}</small>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                                @if($table->orders->count() > 3)
                                    <div class="text-center text-muted mt-2" style="font-size: 0.85rem;">
                                        +{{ $table->orders->count() - 3 }} pedidos
                                    </div>
                                @endif
                            </div>
                        @endif
                        
                        @if($table->unpaid_total > 0)
                            <div class="pending-total mt-3">
                                <div class="label">Total Pendente</div>
                                <div class="amount">R$ {{ number_format($table->unpaid_total, 2, ',', '.') }}</div>
                            </div>
                        @endif
                    @else
                        <div class="empty-table">
                            <i class="fas fa-chair"></i>
                            <p>Mesa disponível</p>
                        </div>
                    @endif
                </div>
                
                @if($table->occupied)
                    <div class="table-card-footer">
                        <a href="{{ route('waiter.table-details', $table) }}" class="btn btn-primary">
                            <i class="fas fa-eye me-2"></i>Ver Detalhes
                        </a>
                        <form action="{{ route('waiter.table.clear', $table) }}" method="POST" style="flex: 1;" onsubmit="return confirm('Tem certeza que deseja desocupar a mesa {{ $table->number }}? Isso removerá todos os participantes.');">
                            @csrf
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-broom me-2"></i>Desocupar
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>

<!-- Modal de Detalhes do Pedido -->
<div id="orderDetailsModal" class="order-details-modal" onclick="closeOrderDetails(event)">
    <div class="order-details-content" onclick="event.stopPropagation()">
        <!-- O conteúdo será preenchido dinamicamente -->
    </div>
</div>

<script>
    // Auto-refresh a cada 30 segundos
    setTimeout(function() {
        location.reload();
    }, 30000);
    
    // Função para mostrar detalhes do pedido
    async function showOrderDetails(orderId) {
        try {
            const response = await fetch(`/api/orders/${orderId}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error('Erro ao buscar detalhes do pedido');
            }
            
            const data = await response.json();
            
            // Aceitar tanto data.order quanto data diretamente sendo o pedido
            const order = data.order || data;
            
            if (order && order.id) {
                renderOrderDetails(order);
                const modal = document.getElementById('orderDetailsModal');
                if (modal) {
                    modal.classList.add('show');
                }
            }
        } catch (error) {
            console.error('Erro ao carregar pedido:', error);
            alert('Erro ao carregar detalhes do pedido');
        }
    }
    
    // Função para renderizar os detalhes do pedido
    function renderOrderDetails(order) {
        const modal = document.querySelector('.order-details-content');
        
        if (!modal) {
            console.error('Elemento .order-details-content não encontrado!');
            return;
        }
        
        // Status badge color
        let statusColor = '#f39c12';
        if (order.status === 'Em produção') statusColor = '#3498db';
        if (order.status === 'Finalizado') statusColor = '#27ae60';
        if (order.status === 'Cancelado') statusColor = '#e74c3c';
        
        // Informações do cliente
        let clientInfo = '';
        if (order.participant) {
            clientInfo = `<i class="fas fa-user"></i> ${order.participant.name}`;
        } else if (order.user) {
            clientInfo = `<i class="fas fa-user"></i> ${order.user.name}`;
        } else {
            clientInfo = `<i class="fas fa-user-slash"></i> Cliente não identificado`;
        }
        
        // Mesa
        const tableInfo = order.table ? `Mesa ${order.table.number}` : 'Balcão';
        
        // Itens do pedido
        let itemsHtml = '';
        order.items.forEach(item => {
            const itemTotal = item.quantity * item.price;
            itemsHtml += `
                <div class="order-item-card">
                    <div class="order-item-info">
                        <div class="order-item-name">
                            ${item.quantity}x ${item.product.name}
                            ${item.product.is_quick_item ? '<span style="background: #3498db; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; margin-left: 5px;">RÁPIDO</span>' : ''}
                        </div>
                        <div class="order-item-details">
                            R$ ${parseFloat(item.price).toFixed(2).replace('.', ',')} cada
                            ${item.notes ? `<br><i class="fas fa-sticky-note"></i> ${item.notes}` : ''}
                        </div>
                    </div>
                    <div class="order-item-price">
                        R$ ${itemTotal.toFixed(2).replace('.', ',')}
                    </div>
                </div>
            `;
        });
        
        // Observações gerais
        let notesHtml = '';
        if (order.notes) {
            notesHtml = `
                <div class="order-info-section">
                    <h4><i class="fas fa-sticky-note"></i> Observações</h4>
                    <div style="background: #fff3cd; padding: 15px; border-radius: 8px; color: #856404;">
                        ${order.notes}
                    </div>
                </div>
            `;
        }
        
        modal.innerHTML = `
            <div class="order-details-header">
                <h3>Pedido #${order.order_number}</h3>
                <div class="order-meta">
                    <span><i class="fas fa-table"></i> ${tableInfo}</span>
                    <span>${clientInfo}</span>
                    <span style="background: ${statusColor}; padding: 4px 12px; border-radius: 12px; font-weight: 600;">
                        ${order.status}
                    </span>
                </div>
            </div>
            
            <div class="order-details-body">
                <div class="order-info-section">
                    <h4><i class="fas fa-receipt"></i> Itens do Pedido</h4>
                    ${itemsHtml}
                </div>
                
                ${notesHtml}
                
                <div class="order-total-box">
                    <div class="label">Total do Pedido</div>
                    <div class="amount">R$ ${parseFloat(order.total).toFixed(2).replace('.', ',')}</div>
                </div>
                
                <div style="text-align: center; margin-top: 15px; color: #95a5a6; font-size: 0.85rem;">
                    <i class="fas fa-clock"></i> Realizado ${formatDate(order.created_at)}
                </div>
            </div>
            
            <div class="order-details-footer">
                <button class="btn-close-modal" onclick="closeOrderDetails()">
                    <i class="fas fa-times me-2"></i>Fechar
                </button>
            </div>
        `;
    }
    
    // Função para fechar o modal
    function closeOrderDetails(event) {
        if (!event || event.target === event.currentTarget) {
            document.getElementById('orderDetailsModal').classList.remove('show');
        }
    }
    
    // Função para formatar data
    function formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        
        if (diffMins < 1) return 'agora mesmo';
        if (diffMins < 60) return `há ${diffMins} minuto${diffMins > 1 ? 's' : ''}`;
        if (diffHours < 24) return `há ${diffHours} hora${diffHours > 1 ? 's' : ''}`;
        
        return date.toLocaleDateString('pt-BR', { 
            day: '2-digit', 
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    // Fechar modal com ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeOrderDetails();
        }
    });
</script>
@endsection










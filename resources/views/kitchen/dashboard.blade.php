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
        100% { transform: rotate(360deg); }
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

    @if($orders->count() > 0)
        <div class="orders-grid">
            @foreach($orders as $order)
                <div class="order-card {{ $order->status === 'Aguardando pagamento' ? 'waiting' : 'in-production' }}">
                    <div class="order-card-header">
                        <div>
                            <div class="order-number">#{{ $order->order_number }}</div>
                            <div class="order-time {{ $order->created_at->diffInMinutes(now()) > 15 ? 'urgent' : '' }}">
                                <i class="fas fa-clock me-1"></i>
                                {{ $order->created_at->diffForHumans() }}
                                @if($order->created_at->diffInMinutes(now()) > 15)
                                    <span class="ms-2">({{ $order->created_at->diffInMinutes(now()) }} min)</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="order-table">
                                <i class="fas fa-{{ $order->table ? 'chair' : 'store' }} me-1"></i>{{ $order->table ? 'Mesa ' . $order->table->number : 'Balcão' }}
                            </span>
                            <div class="mt-2">
                                <span class="status-badge {{ $order->status === 'Aguardando pagamento' ? 'waiting' : 'in-production' }}">
                                    {{ $order->status }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="order-card-body">
                        @if($order->participant)
                            <div class="order-customer">
                                <i class="fas fa-user me-2"></i>
                                <strong>{{ $order->participant->name }}</strong>
                            </div>
                        @endif
                        
                        <ul class="order-items">
                            @foreach($order->items as $item)
                                <li class="order-item">
                                    <span class="item-quantity">{{ $item->quantity }}</span>
                                    <div class="item-details">
                                        <div class="item-name">
                                            {{ $item->product->name }}
                                            @if($item->product->is_quick_item)
                                                <span class="item-quick">
                                                    <i class="fas fa-bolt"></i> Rápido
                                                </span>
                                            @endif
                                        </div>
                                        @if($item->notes)
                                            <div class="item-notes">
                                                <i class="fas fa-comment me-1"></i>{{ $item->notes }}
                                            </div>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        
                        @if($order->notes)
                            <div class="order-notes">
                                <strong><i class="fas fa-sticky-note me-1"></i>Observação do Pedido:</strong>
                                {{ $order->notes }}
                            </div>
                        @endif
                    </div>
                    
                    <div class="order-card-footer">
                        @if($order->status === 'Aguardando pagamento')
                            <form action="{{ route('kitchen.update-status', $order) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="Em produção">
                                <button type="submit" class="btn btn-production w-100">
                                    <i class="fas fa-fire me-2"></i>Iniciar Produção
                                </button>
                            </form>
                        @endif
                        <form action="{{ route('kitchen.update-status', $order) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="Finalizado">
                            <button type="submit" class="btn btn-done w-100">
                                <i class="fas fa-check me-2"></i>Finalizar
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-check-circle"></i>
            <h2>Nenhum pedido pendente!</h2>
            <p>Todos os pedidos foram finalizados. Aguardando novos pedidos...</p>
        </div>
    @endif
</div>

<script>
    // Auto-refresh a cada 30 segundos
    setTimeout(function() {
        location.reload();
    }, 30000);
</script>
@endsection






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
    
    /* Header */
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
    
    /* Container */
    .waiter-container {
        padding: 0 20px 40px;
    }
    
    /* Botão voltar */
    .btn-back {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        padding: 10px 20px;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-back:hover {
        background: rgba(255, 255, 255, 0.3);
        color: white;
    }
    
    /* Cards */
    .detail-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        margin-bottom: 20px;
    }
    
    .detail-card .card-header {
        background: #f8f9fa;
        padding: 20px 25px;
        border: none;
    }
    
    .detail-card .card-header h5 {
        color: #2c3e50;
        font-weight: 700;
        margin: 0;
    }
    
    .detail-card .card-body {
        padding: 25px;
    }
    
    /* Info da mesa */
    .table-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }
    
    .info-item {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 12px;
        text-align: center;
    }
    
    .info-item i {
        font-size: 2rem;
        color: #3498db;
        margin-bottom: 10px;
    }
    
    .info-item .value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2c3e50;
    }
    
    .info-item .label {
        color: #7f8c8d;
        font-size: 0.9rem;
    }
    
    /* Participantes */
    .participant-item {
        display: flex;
        align-items: center;
        padding: 15px;
        border-bottom: 1px solid #ecf0f1;
    }
    
    .participant-item:last-child {
        border-bottom: none;
    }
    
    .participant-item i {
        font-size: 1.5rem;
        color: #3498db;
        margin-right: 15px;
    }
    
    .participant-name {
        font-weight: 600;
        color: #2c3e50;
    }
    
    /* Pedidos */
    .order-card {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 15px;
    }
    
    .order-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px dashed #dee2e6;
    }
    
    .order-number {
        font-size: 1.2rem;
        font-weight: 700;
        color: #2c3e50;
    }
    
    .order-customer {
        color: #7f8c8d;
        font-size: 0.9rem;
    }
    
    /* Itens do pedido */
    .order-items-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .order-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #ecf0f1;
    }
    
    .order-item:last-child {
        border-bottom: none;
    }
    
    .item-info {
        display: flex;
        align-items: center;
    }
    
    .item-qty {
        background: #2c3e50;
        color: white;
        width: 28px;
        height: 28px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.85rem;
        margin-right: 12px;
    }
    
    .item-name {
        color: #2c3e50;
        font-weight: 500;
    }
    
    .item-notes {
        color: #e74c3c;
        font-size: 0.8rem;
        font-style: italic;
    }
    
    .item-price {
        color: #2c3e50;
        font-weight: 600;
    }
    
    /* Total do pedido */
    .order-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 15px;
        margin-top: 15px;
        border-top: 2px solid #dee2e6;
    }
    
    .order-total span:last-child {
        font-size: 1.2rem;
        font-weight: 700;
        color: #27ae60;
    }
    
    /* Badges */
    .badge {
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.8rem;
    }
    
    /* Total geral */
    .grand-total {
        background: linear-gradient(135deg, #27ae60 0%, #1e8449 100%);
        color: white;
        padding: 25px;
        border-radius: 12px;
        text-align: center;
    }
    
    .grand-total .label {
        font-size: 1rem;
        opacity: 0.9;
    }
    
    .grand-total .amount {
        font-size: 2.5rem;
        font-weight: 700;
    }
</style>

<!-- Header -->
<div class="waiter-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fas fa-chair me-3"></i>Mesa {{ $table->number }}</h1>
                <p class="mb-0" style="color: rgba(255,255,255,0.8);">{{ $store->name }}</p>
            </div>
            <a href="{{ route('waiter.dashboard') }}" class="btn-back">
                <i class="fas fa-arrow-left me-2"></i>Voltar
            </a>
        </div>
    </div>
</div>

<div class="container waiter-container">
    <div class="row">
        <!-- Coluna de informações -->
        <div class="col-lg-4">
            <!-- Info da Mesa -->
            <div class="detail-card">
                <div class="card-header">
                    <h5><i class="fas fa-info-circle me-2"></i>Informações</h5>
                </div>
                <div class="card-body">
                    <div class="table-info-grid">
                        <div class="info-item">
                            <i class="fas fa-receipt"></i>
                            <div class="value">{{ $orders->count() }}</div>
                            <div class="label">Pedidos</div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-clock"></i>
                            <div class="value">{{ $table->occupied_at ? $table->occupied_at->diffForHumans(null, true) : '-' }}</div>
                            <div class="label">Tempo Ocupada</div>
                        </div>
                    </div>
                    
                    @if($table->current_user_name)
                        <div class="mt-4 p-3 bg-light rounded">
                            <strong><i class="fas fa-user me-2"></i>Responsável:</strong>
                            <p class="mb-0 mt-1">{{ $table->current_user_name }}</p>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Participantes -->
            @if($participants->count() > 0)
                <div class="detail-card">
                    <div class="card-header">
                        <h5><i class="fas fa-users me-2"></i>Participantes ({{ $participants->count() }})</h5>
                    </div>
                    <div class="card-body p-0">
                        @foreach($participants as $participant)
                            <div class="participant-item">
                                <i class="fas fa-user-circle"></i>
                                <span class="participant-name">{{ $participant->name }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <!-- Total Pendente -->
            @php
                $pendingTotal = $orders->where('payment_status', 'pending')->sum('total');
            @endphp
            @if($pendingTotal > 0)
                <div class="grand-total">
                    <div class="label">Total Pendente</div>
                    <div class="amount">R$ {{ number_format($pendingTotal, 2, ',', '.') }}</div>
                </div>
            @endif
        </div>
        
        <!-- Coluna de pedidos -->
        <div class="col-lg-8">
            <div class="detail-card">
                <div class="card-header">
                    <h5><i class="fas fa-receipt me-2"></i>Pedidos da Mesa</h5>
                </div>
                <div class="card-body">
                    @if($orders->count() > 0)
                        @foreach($orders as $order)
                            <div class="order-card">
                                <div class="order-card-header">
                                    <div>
                                        <span class="order-number">#{{ $order->order_number }}</span>
                                        @if($order->participant)
                                            <div class="order-customer">
                                                <i class="fas fa-user me-1"></i>{{ $order->participant->name }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-{{ $order->status === 'Aguardando pagamento' ? 'warning' : 
                                                               ($order->status === 'Em produção' ? 'info' : 
                                                               ($order->status === 'Finalizado' ? 'success' : 
                                                               ($order->status === 'Cancelado' ? 'danger' : 'primary'))) }}">
                                            {{ $order->status }}
                                        </span>
                                        <div class="text-muted mt-1" style="font-size: 0.8rem;">
                                            {{ $order->created_at->format('d/m H:i') }}
                                        </div>
                                    </div>
                                </div>
                                
                                <ul class="order-items-list">
                                    @foreach($order->items as $item)
                                        <li class="order-item">
                                            <div class="item-info">
                                                <span class="item-qty">{{ $item->quantity }}</span>
                                                <div>
                                                    <div class="item-name">{{ $item->product->name }}</div>
                                                    @if($item->notes)
                                                        <div class="item-notes">{{ $item->notes }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                            <span class="item-price">
                                                R$ {{ number_format($item->price * $item->quantity, 2, ',', '.') }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                                
                                @if($order->notes)
                                    <div class="alert alert-warning mt-3 mb-0">
                                        <strong><i class="fas fa-sticky-note me-1"></i>Obs:</strong> {{ $order->notes }}
                                    </div>
                                @endif
                                
                                <div class="order-total">
                                    <span>Total do Pedido</span>
                                    <span>R$ {{ number_format($order->total, 2, ',', '.') }}</span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Nenhum pedido para esta mesa.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection










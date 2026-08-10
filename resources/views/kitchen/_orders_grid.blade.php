@if ($orders->count() > 0)
    <div class="orders-grid">
        @foreach ($orders as $order)
            <div class="order-card {{ $order->status === 'Aguardando pagamento' ? 'waiting' : 'in-production' }}"
                data-order-id="{{ $order->id }}" data-order-status="{{ $order->status }}">
                <div class="order-card-header">
                    <div>
                        <div class="order-number">#{{ $order->order_number }}</div>
                        <div class="order-time {{ $order->created_at->diffInMinutes(now()) > 15 ? 'urgent' : '' }}">
                            <i class="fas fa-clock me-1"></i>
                            {{ $order->created_at->diffForHumans() }}
                            @if ($order->created_at->diffInMinutes(now()) > 15)
                                <span class="ms-2">({{ $order->created_at->diffInMinutes(now()) }} min)</span>
                            @endif
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="order-table">
                            <i
                                class="fas fa-{{ $order->table ? 'chair' : 'store' }} me-1"></i>{{ $order->table ? 'Mesa ' . $order->table->number : 'Balcão' }}
                        </span>
                        <div class="mt-2">
                            <span
                                class="status-badge {{ $order->status === 'Aguardando pagamento' ? 'waiting' : 'in-production' }}">
                                {{ $order->status }}
                            </span>
                            <!-- printed badge placeholder -->
                            <span class="printed-badge ms-2 d-none"
                                style="background:#2ecc71;color:#fff;padding:6px 10px;border-radius:20px;font-weight:600;font-size:0.8rem;margin-left:8px;">Impresso</span>
                        </div>
                    </div>
                </div>

                <div class="order-card-body">
                    @if ($order->participant)
                        <div class="order-customer">
                            <i class="fas fa-user me-2"></i>
                            <strong>{{ $order->participant->name }}</strong>
                        </div>
                    @endif

                    <ul class="order-items">
                        @foreach ($order->items as $item)
                            <li class="order-item">
                                <span class="item-quantity">{{ $item->quantity }}</span>
                                <div class="item-details">
                                    <div class="item-name">
                                        {{ $item->product->name }}
                                        @if ($item->product->is_quick_item)
                                            <span class="item-quick">
                                                <i class="fas fa-bolt"></i> Rápido
                                            </span>
                                        @endif
                                    </div>
                                    @if ($item->notes)
                                        <div class="item-notes">
                                            <i class="fas fa-comment me-1"></i>{{ $item->notes }}
                                        </div>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    @if ($order->notes)
                        <div class="order-notes">
                            <strong><i class="fas fa-sticky-note me-1"></i>Observação do Pedido:</strong>
                            {{ $order->notes }}
                        </div>
                    @endif

                    <!-- Per-order print failure indicator + retry (populated client-side) -->
                    <div class="order-print-failed alert alert-warning d-none mt-3"
                        data-order-id="{{ $order->id }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>Falha ao imprimir este pedido.</div>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-primary order-retry-btn">Reenviar
                                    Impressão</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="order-card-footer">
                    @if ($order->status === 'Aguardando pagamento')
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

@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        Meu Histórico de Pedidos
                    </h4>
                </div>

                <div class="card-body">
                    <!-- Filtros -->
                    <form method="GET" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="">Todos os Status</option>
                                    <option value="Aguardando pagamento" {{ request('status') == 'Aguardando pagamento' ? 'selected' : '' }}>Aguardando pagamento</option>
                                    <option value="Em produção" {{ request('status') == 'Em produção' ? 'selected' : '' }}>Em produção</option>
                                    <option value="Finalizado" {{ request('status') == 'Finalizado' ? 'selected' : '' }}>Finalizado</option>
                                    <option value="Pago" {{ request('status') == 'Pago' ? 'selected' : '' }}>Pago</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="start_date" class="form-label">Data Inicial</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" 
                                       value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="end_date" class="form-label">Data Final</label>
                                <input type="date" name="end_date" id="end_date" class="form-control" 
                                       value="{{ request('end_date') }}">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search"></i> Filtrar
                                </button>
                                <a href="{{ route('orders.history') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Limpar
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Lista de Pedidos -->
                    @if($orders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Pedido #</th>
                                        <th>Restaurante</th>
                                        <th>Mesa</th>
                                        <th>Data/Hora</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        <tr>
                                            <td>
                                                <strong>{{ $order->order_number }}</strong>
                                            </td>
                                            <td>{{ $order->store->name }}</td>
                                            <td>{{ $order->table->name }}</td>
                                            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <span class="badge 
                                                    @if($order->status === 'Aguardando pagamento') bg-warning
                                                    @elseif($order->status === 'Em produção') bg-info
                                                    @elseif($order->status === 'Finalizado') bg-success
                                                    @elseif($order->status === 'Pago') bg-secondary
                                                    @endif">
                                                    {{ $order->status }}
                                                </span>
                                            </td>
                                            <td>R$ {{ number_format($order->total, 2, ',', '.') }}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#orderModal{{ $order->id }}">
                                                    <i class="fas fa-eye"></i> Ver Detalhes
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginação -->
                        <div class="d-flex justify-content-center">
                            {{ $orders->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Nenhum pedido encontrado</h5>
                            <p class="text-muted">Você ainda não fez nenhum pedido ou nenhum pedido corresponde aos filtros aplicados.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modais de Detalhes dos Pedidos -->
@foreach($orders as $order)
    <div class="modal fade" id="orderModal{{ $order->id }}" tabindex="-1" aria-labelledby="orderModalLabel{{ $order->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="orderModalLabel{{ $order->id }}">
                        Pedido {{ $order->order_number }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Restaurante:</strong> {{ $order->store->name }}
                        </div>
                        <div class="col-md-6">
                            <strong>Mesa:</strong> {{ $order->table->name }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Data/Hora:</strong> {{ $order->created_at->format('d/m/Y H:i') }}
                        </div>
                        <div class="col-md-6">
                            <strong>Status:</strong> 
                            <span class="badge 
                                @if($order->status === 'Aguardando pagamento') bg-warning
                                @elseif($order->status === 'Em produção') bg-info
                                @elseif($order->status === 'Finalizado') bg-success
                                @elseif($order->status === 'Pago') bg-secondary
                                @endif">
                                {{ $order->status }}
                            </span>
                        </div>
                    </div>
                    
                    @if($order->notes)
                        <div class="mb-3">
                            <strong>Observações do Pedido:</strong>
                            <p class="mb-0">{{ $order->notes }}</p>
                        </div>
                    @endif

                    <h6>Itens do Pedido:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Preço Unit.</th>
                                    <th>Qtd.</th>
                                    <th>Subtotal</th>
                                    <th>Observações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    <tr>
                                        <td>{{ $item->product->name }}</td>
                                        <td>R$ {{ number_format($item->price, 2, ',', '.') }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>R$ {{ number_format($item->price * $item->quantity, 2, ',', '.') }}</td>
                                        <td>{{ $item->notes ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <th colspan="3">Total:</th>
                                    <th>R$ {{ number_format($order->total, 2, ',', '.') }}</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection 
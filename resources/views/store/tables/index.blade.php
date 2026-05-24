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
    
    /* Container principal */
    .tables-container {
        background: transparent;
        padding: 20px 0;
        margin-top: 0;
    }
    
    /* Título principal */
    .tables-title {
        color: #e8e8e9;
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 20px;
        text-align: left;
        letter-spacing: -0.02em;
    }
    
    [data-bs-theme="light"] .tables-title {
        color: #000000;
    }
    
    /* Botão de nova mesa */
    .btn-new-table {
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
    
    .btn-new-table:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.4);
        color: #e8e8e9;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        text-decoration: none;
    }
    
    /* Card principal */
    .tables-card {
        background: white;
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }
    
    .tables-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
    }
    
    .tables-card .card-body {
        padding: 40px;
    }
    
    /* Alerta de sucesso */
    .alert-success {
        background: #d4edda;
        border: 1px solid #c3e6cb;
        border-radius: 12px;
        color: #155724;
        padding: 20px;
        margin-bottom: 20px;
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
    
    .badge.bg-success {
        background: #27ae60 !important;
    }
    
    .badge.bg-danger {
        background: #e74c3c !important;
    }
    
    /* Botões modernos */
    .btn-sm {
        border-radius: 8px;
        padding: 8px 16px;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        margin: 2px;
    }
    
    .btn-sm:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    }
    
    .btn-info {
        background: #3498db;
        color: white;
    }
    
    .btn-info:hover {
        background: #2980b9;
        color: white;
    }
    
    .btn-primary {
        background: #3498db;
        color: white;
    }
    
    .btn-primary:hover {
        background: #2980b9;
        color: white;
    }
    
    .btn-warning {
        background: #f39c12;
        color: white;
    }
    
    .btn-warning:hover {
        background: #e67e22;
        color: white;
    }
    
    .btn-danger {
        background: #e74c3c;
        color: white;
    }
    
    .btn-danger:hover {
        background: #c0392b;
        color: white;
    }
    
    /* Estado vazio */
    .text-center.py-4 {
        padding: 60px 20px !important;
    }
    
    .text-center.py-4 p {
        color: #7f8c8d;
        font-weight: 600;
        font-size: 1.1rem;
    }
    
    /* Grupo de botões */
    .btn-group {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }
    
    .btn-group .btn-sm {
        margin: 0;
    }
</style>

<div class="container tables-container">
    <div class="row justify-content-between mb-4">
        <div class="col-auto">
            <h1 class="tables-title">Mesas</h1>
        </div>
        <div class="col-auto d-flex gap-2">
            <a href="{{ route('store.counter.qrcode') }}" class="btn btn-new-table">
                <i class="fas fa-qrcode me-2"></i> QR Code Balcão
            </a>
            <a href="{{ route('store.tables.create') }}" class="btn btn-new-table">
                <i class="fas fa-plus-circle me-2"></i> Nova Mesa
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card tables-card">
        <div class="card-body">
            @if($tables->isEmpty())
                <div class="text-center py-4">
                    <p class="mb-0">Nenhuma mesa cadastrada.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Número</th>
                                <th>Status</th>
                                <th>Participantes</th>
                                <th>Tempo</th>
                                <th>QR Code</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tables as $table)
                                <tr>
                                    <td>{{ $table->number }}</td>
                                    <td>
                                        @if($table->occupied)
                                            <span class="badge bg-danger">Ocupada</span>
                                        @else
                                            <span class="badge bg-success">Livre</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($table->participants->count() > 0)
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-primary me-2">
                                                    <i class="fas fa-users me-1"></i>
                                                    {{ $table->participants->count() }}
                                                </span>
                                                <div class="participant-names">
                                                    @foreach($table->participants->take(3) as $participant)
                                                        <span class="badge bg-{{ $participant->is_owner ? 'warning' : 'info' }} me-1 mb-1">
                                                            {{ $participant->name }}
                                                            @if($participant->is_owner)
                                                                <i class="fas fa-crown"></i>
                                                            @endif
                                                        </span>
                                                    @endforeach
                                                    @if($table->participants->count() > 3)
                                                        <span class="badge bg-secondary">+{{ $table->participants->count() - 3 }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($table->occupied && $table->occupied_at)
                                            {{ $table->occupied_at->diffForHumans(null, true) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('store.tables.qrcode', $table) }}" class="btn btn-sm btn-info">
                                            Ver QR Code
                                        </a>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('store.tables.edit', $table) }}" 
                                               class="btn btn-sm btn-primary">
                                                Editar
                                            </a>
                                            @if($table->occupied)
                                                <form action="{{ route('store.tables.clear', $table) }}" 
                                                      method="POST" 
                                                      class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-warning">
                                                        Desocupar
                                                    </button>
                                                </form>
                                            @endif
                                            <form action="{{ route('store.tables.destroy', $table) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Tem certeza que deseja excluir esta mesa?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    Excluir
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $tables->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 
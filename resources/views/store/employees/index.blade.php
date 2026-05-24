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
    .employees-container {
        background: transparent;
        padding: 20px 0;
        margin-top: 0;
    }
    
    /* Título principal */
    .employees-title {
        color: #e8e8e9;
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 20px;
        text-align: left;
        letter-spacing: -0.02em;
    }
    
    [data-bs-theme="light"] .employees-title {
        color: #000000;
    }
    
    /* Botão de novo funcionário */
    .btn-new-employee {
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
    
    .btn-new-employee:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.4);
        color: #e8e8e9;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        text-decoration: none;
    }
    
    /* Card principal */
    .employees-card {
        background: white;
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .employees-card .card-body {
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
    }
    
    /* Badges */
    .badge {
        border-radius: 8px;
        padding: 8px 12px;
        font-weight: 600;
        font-size: 0.75rem;
    }
    
    .badge-kitchen {
        background: #e74c3c !important;
        color: white;
    }
    
    .badge-waiter {
        background: #3498db !important;
        color: white;
    }
    
    /* Botões de ação */
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
    
    /* Modal */
    .modal-content {
        border: none;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }
    
    .modal-header {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        color: white;
        border: none;
        padding: 25px 30px;
        border-radius: 20px 20px 0 0;
    }
    
    .modal-title {
        font-weight: 700;
    }
    
    .btn-close {
        filter: invert(1);
    }
    
    .modal-body {
        padding: 30px;
    }
    
    .modal-footer {
        border: none;
        padding: 20px 30px 30px;
    }
    
    /* Forms */
    .form-label {
        color: #2c3e50;
        font-weight: 600;
        margin-bottom: 8px;
    }
    
    .form-control, .form-select {
        border: 2px solid #ecf0f1;
        border-radius: 10px;
        padding: 12px 15px;
        transition: all 0.3s ease;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #3498db;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }
    
    /* Estado vazio */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }
    
    .empty-state i {
        font-size: 4rem;
        color: #bdc3c7;
        margin-bottom: 20px;
    }
    
    .empty-state p {
        color: #7f8c8d;
        font-size: 1.1rem;
    }
    
    /* Alertas */
    .alert-success {
        background: #d4edda;
        border: 1px solid #c3e6cb;
        border-radius: 12px;
        color: #155724;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .alert-danger {
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        border-radius: 12px;
        color: #721c24;
        padding: 15px;
        margin-bottom: 20px;
    }
    
    .alert-danger ul {
        margin-bottom: 0;
        padding-left: 20px;
    }
    
    .alert-danger ul li {
        margin-top: 5px;
    }
    
    .invalid-feedback {
        display: block;
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
    
    .form-control.is-invalid, .form-select.is-invalid {
        border-color: #dc3545;
    }
    
    .form-control.is-invalid:focus, .form-select.is-invalid:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
    }
</style>

<div class="container employees-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="employees-title">Funcionários</h1>
        <button type="button" class="btn btn-new-employee" data-bs-toggle="modal" data-bs-target="#newEmployeeModal">
            <i class="fas fa-user-plus me-2"></i> Novo Funcionário
        </button>
    </div>

    <div class="card employees-card">
        <div class="card-body">
            @if($employees->count() > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Função</th>
                                <th>Cadastrado em</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($employees as $employee)
                                <tr>
                                    <td><strong>{{ $employee->name }}</strong></td>
                                    <td>{{ $employee->email }}</td>
                                    <td>
                                        @if($employee->hasRole('kitchen'))
                                            <span class="badge badge-kitchen">
                                                <i class="fas fa-utensils me-1"></i> Cozinha
                                            </span>
                                        @elseif($employee->hasRole('waiter'))
                                            <span class="badge badge-waiter">
                                                <i class="fas fa-concierge-bell me-1"></i> Garçom
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ $employee->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editEmployeeModal{{ $employee->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('store.employees.destroy', $employee) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('Tem certeza que deseja excluir este funcionário?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Modal de Edição -->
                                <div class="modal fade" id="editEmployeeModal{{ $employee->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Editar Funcionário</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('store.employees.update', $employee) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body">
                                                    @if($errors->any() && old('_method') == 'PUT')
                                                        <div class="alert alert-danger">
                                                            <strong><i class="fas fa-exclamation-triangle me-2"></i>Erro ao atualizar:</strong>
                                                            <ul class="mb-0 mt-2">
                                                                @foreach($errors->all() as $error)
                                                                    <li>{{ $error }}</li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Nome</label>
                                                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                                               name="name" 
                                                               value="{{ old('name', $employee->name) }}" 
                                                               required>
                                                        @error('name')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Email</label>
                                                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                                               name="email" 
                                                               value="{{ old('email', $employee->email) }}" 
                                                               required>
                                                        @error('email')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Nova Senha (deixe em branco para manter)</label>
                                                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                                               name="password" 
                                                               minlength="6">
                                                        @error('password')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Função</label>
                                                        <select class="form-select @error('role') is-invalid @enderror" name="role" required>
                                                            <option value="kitchen" {{ old('role', $employee->hasRole('kitchen') ? 'kitchen' : '') == 'kitchen' ? 'selected' : '' }}>
                                                                Cozinha
                                                            </option>
                                                            <option value="waiter" {{ old('role', $employee->hasRole('waiter') ? 'waiter' : '') == 'waiter' ? 'selected' : '' }}>
                                                                Garçom
                                                            </option>
                                                        </select>
                                                        @error('role')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-primary">Salvar</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <p>Nenhum funcionário cadastrado ainda.</p>
                    <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#newEmployeeModal">
                        <i class="fas fa-user-plus me-2"></i> Cadastrar Primeiro Funcionário
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Novo Funcionário -->
<div class="modal fade" id="newEmployeeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Novo Funcionário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('store.employees.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <strong><i class="fas fa-exclamation-triangle me-2"></i>Erro ao cadastrar:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               name="name" 
                               value="{{ old('name') }}" 
                               required 
                               placeholder="Nome completo">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required 
                               placeholder="email@exemplo.com">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Senha</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               name="password" 
                               required 
                               minlength="6" 
                               placeholder="Mínimo 6 caracteres">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Função</label>
                        <select class="form-select @error('role') is-invalid @enderror" name="role" required>
                            <option value="">Selecione a função</option>
                            <option value="kitchen" {{ old('role') == 'kitchen' ? 'selected' : '' }}>Cozinha - Visualiza e gerencia pedidos</option>
                            <option value="waiter" {{ old('role') == 'waiter' ? 'selected' : '' }}>Garçom - Visualiza mesas e pedidos</option>
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <strong>Permissões:</strong>
                        <ul class="mb-0 mt-2">
                            <li><strong>Cozinha:</strong> Vê pedidos em produção/aguardando e pode alterar status</li>
                            <li><strong>Garçom:</strong> Vê todas as mesas, pedidos e histórico completo</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus me-2"></i> Cadastrar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Verificar se é erro de edição ou novo cadastro
            var isUpdate = '{{ old("_method") }}' === 'PUT';
            
            if (isUpdate) {
                // Se for update, não fazemos nada aqui pois não sabemos qual modal abrir
                // Mostramos os erros inline nos campos
            } else {
                // Se for novo cadastro, reabre o modal de novo funcionário
                var modal = new bootstrap.Modal(document.getElementById('newEmployeeModal'));
                modal.show();
            }
        });
    </script>
@endif
@endsection










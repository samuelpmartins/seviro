@extends('layouts.store-base')

@section('content')
<style>
    /* Fundo escuro para toda a página */
    body {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        min-height: 100vh;
    }
    
    /* Container principal */
    .create-category-container {
        background: transparent;
        padding: 20px 0;
        margin-top: 0;
    }
    
    /* Card de criação */
    .create-category-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .create-category-card .card-header {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        color: white;
        border: none;
        padding: 25px 30px;
    }
    
    .create-category-card .card-header h1 {
        color: white;
        font-weight: 700;
        margin: 0;
    }
    
    .create-category-card .card-body {
        padding: 40px;
    }
    
    /* Formulários modernos */
    .form-group label {
        color: #2c3e50;
        font-weight: 600;
        margin-bottom: 8px;
        display: block;
    }
    
    .form-control, .form-select {
        border: 2px solid #ecf0f1;
        border-radius: 10px;
        padding: 15px 20px;
        font-size: 16px;
        transition: all 0.3s ease;
        background: #f8f9fa;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #3498db;
        background: white;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        outline: none;
    }
    
    .form-control.is-invalid {
        border-color: #e74c3c;
        background: #fdf2f2;
    }
    
    .invalid-feedback {
        color: #e74c3c;
        font-size: 14px;
        margin-top: 5px;
    }
    
    /* Input group */
    .input-group-text {
        background: #f8f9fa;
        border: 2px solid #ecf0f1;
        border-right: none;
        border-radius: 10px 0 0 10px;
        padding: 15px 20px;
    }
    
    .input-group .form-control {
        border-left: none;
        border-radius: 0 10px 10px 0;
    }
    
    .input-group .form-control:focus {
        border-left: 2px solid #3498db;
    }
    
    /* Botões modernos */
    .btn-primary, .btn-secondary {
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
    
    .btn-secondary {
        background: #95a5a6;
        color: white;
    }
    
    .btn-secondary:hover {
        background: #7f8c8d;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }
    
    /* Texto de ajuda */
    .form-text {
        color: #7f8c8d;
        font-size: 0.875rem;
        margin-top: 8px;
    }
    
    .form-text a {
        color: #3498db;
        text-decoration: none;
    }
    
    .form-text a:hover {
        color: #2980b9;
        text-decoration: underline;
    }
    
    /* Alerta de erro */
    .alert-danger {
        background: #fdf2f2;
        border: 1px solid #f5c6cb;
        border-radius: 12px;
        color: #721c24;
        padding: 20px;
    }
    
    .alert-danger ul {
        margin: 0;
        padding-left: 20px;
    }
</style>

<div class="container create-category-container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card create-category-card">
                <div class="card-header">
                    <h1 class="h4 mb-0">Nova Categoria</h1>
                </div>

                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('store.categories.store') }}" method="POST">
                        @csrf

                        <div class="form-group mb-3">
                            <label for="name">Nome da Categoria</label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label for="description">Descrição (opcional)</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('store.categories.index') }}" class="btn btn-secondary">
                                Voltar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Criar Categoria
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection 
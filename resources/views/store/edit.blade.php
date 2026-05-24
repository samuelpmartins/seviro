@extends('layouts.store-base')

@section('content')
<style>
    /* Fundo escuro para toda a página */
    body {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        min-height: 100vh;
    }
    
    /* Navbar - usar o estilo padrão do layout */
    
    /* Container */
    .edit-container {
        padding: 20px 0;
        margin-top: 0;
    }
    
    /* Card de edição */
    .edit-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        padding: 40px;
        margin-bottom: 30px;
    }
    
    .edit-title {
        color: #2c3e50;
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 10px;
    }
    
    .edit-subtitle {
        color: #7f8c8d;
        margin-bottom: 30px;
    }
    
    /* Formulário */
    .form-group {
        margin-bottom: 25px;
    }
    
    .form-label {
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
    
    /* Botões */
    .btn-save {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        color: white;
        border: none;
        border-radius: 10px;
        padding: 15px 40px;
        font-size: 16px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.3s ease;
    }
    
    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(44, 62, 80, 0.3);
        background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%);
        color: white;
    }
    
    .btn-cancel {
        background: white;
        color: #7f8c8d;
        border: 2px solid #ecf0f1;
        border-radius: 10px;
        padding: 15px 40px;
        font-size: 16px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.3s ease;
    }
    
    .btn-cancel:hover {
        border-color: #bdc3c7;
        color: #2c3e50;
        transform: translateY(-2px);
    }
    
    /* Preview de imagem */
    .image-preview-container {
        margin-top: 15px;
    }
    
    .image-preview {
        max-width: 200px;
        max-height: 200px;
        border-radius: 10px;
        border: 3px solid #ecf0f1;
        object-fit: cover;
    }
    
    /* Responsividade */
    @media (max-width: 768px) {
        .edit-card {
            padding: 25px;
        }
        
        .edit-title {
            font-size: 1.5rem;
        }
    }
</style>

<div class="edit-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="edit-card">
                    <h1 class="edit-title">Editar Informações do Estabelecimento</h1>
                    <p class="edit-subtitle">Atualize as informações básicas do seu restaurante</p>
                    
                    <form method="POST" action="{{ route('store.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <!-- Nome -->
                        <div class="form-group">
                            <label for="name" class="form-label">Nome do Estabelecimento</label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $store->name) }}" 
                                   required>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        <!-- Telefone -->
                        <div class="form-group">
                            <label for="phone" class="form-label">Telefone</label>
                            <input type="text" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone', $store->phone) }}" 
                                   required>
                            @error('phone')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        <!-- Endereço -->
                        <div class="form-group">
                            <label for="address" class="form-label">Endereço</label>
                            <input type="text" 
                                   class="form-control @error('address') is-invalid @enderror" 
                                   id="address" 
                                   name="address" 
                                   value="{{ old('address', $store->address) }}" 
                                   required>
                            @error('address')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        <!-- Documento -->
                        <div class="form-group">
                            <label for="document" class="form-label">CNPJ/CPF</label>
                            <input type="text" 
                                   class="form-control @error('document') is-invalid @enderror" 
                                   id="document" 
                                   name="document" 
                                   value="{{ old('document', $store->document) }}" 
                                   required>
                            @error('document')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        
                        <!-- Logo -->
                        <div class="form-group">
                            <label for="logo" class="form-label">Logo do Estabelecimento</label>
                            <input type="file" 
                                   class="form-control @error('logo') is-invalid @enderror" 
                                   id="logo" 
                                   name="logo"
                                   accept="image/*">
                            @error('logo')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            
                            @if($store->logo)
                                <div class="image-preview-container">
                                    <p class="text-muted mb-2">Logo atual:</p>
                                    <img src="{{ asset('storage/' . $store->logo) }}" 
                                         alt="Logo atual" 
                                         class="image-preview">
                                </div>
                            @endif
                        </div>
                        
                        <!-- Imagem de Capa -->
                        <div class="form-group">
                            <label for="cover_image" class="form-label">Imagem de Capa</label>
                            <input type="file" 
                                   class="form-control @error('cover_image') is-invalid @enderror" 
                                   id="cover_image" 
                                   name="cover_image"
                                   accept="image/*">
                            @error('cover_image')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            
                            @if($store->cover_image)
                                <div class="image-preview-container">
                                    <p class="text-muted mb-2">Capa atual:</p>
                                    <img src="{{ asset('storage/' . $store->cover_image) }}" 
                                         alt="Capa atual" 
                                         class="image-preview" 
                                         style="max-width: 100%; max-height: 300px;">
                                </div>
                            @endif
                        </div>
                        
                        <!-- Botões -->
                        <div class="d-flex gap-3 mt-4">
                            <button type="submit" class="btn btn-save">
                                <i class="fas fa-save me-2"></i> Salvar Alterações
                            </button>
                            <a href="{{ route('store.manage') }}" class="btn btn-cancel">
                                <i class="fas fa-arrow-left me-2"></i> Voltar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Garantir que main não tenha padding */
    main.py-4 {
        padding: 0 !important;
    }
</style>
@endpush
@endsection


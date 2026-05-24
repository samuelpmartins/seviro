@extends('layouts.store-base')

@section('content')
<style>
    /* Fundo escuro para toda a página */
    body {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        min-height: 100vh;
    }
    
    /* Container principal */
    .edit-product-container {
        background: transparent;
        padding: 20px 0;
        margin-top: 0;
    }
    
    /* Card de edição */
    .edit-product-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .edit-product-card .card-header {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        color: white;
        border: none;
        padding: 25px 30px;
    }
    
    .edit-product-card .card-header h1 {
        color: white;
        font-weight: 700;
        margin: 0;
    }
    
    .edit-product-card .card-body {
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
        font-weight: 600;
        color: #2c3e50;
    }
    
    .input-group .form-control {
        border-left: none;
        border-radius: 0 10px 10px 0;
    }
    
    .input-group .form-control:focus {
        border-left: 2px solid #3498db;
    }
    
    /* Checkbox moderno */
    .form-check {
        padding-left: 0;
    }
    
    .form-check-input {
        width: 20px;
        height: 20px;
        margin-right: 10px;
        border: 2px solid #ecf0f1;
        border-radius: 6px;
        background: #f8f9fa;
        transition: all 0.3s ease;
    }
    
    .form-check-input:checked {
        background: #3498db;
        border-color: #3498db;
    }
    
    .form-check-input:focus {
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        outline: none;
    }
    
    .form-check-label {
        color: #2c3e50;
        font-weight: 600;
        cursor: pointer;
    }
    
    /* Imagem atual */
    .img-thumbnail {
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }
    
    .img-thumbnail:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
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

<div class="container edit-product-container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card edit-product-card">
                <div class="card-header">
                    <h1 class="h4 mb-0">Editar Produto</h1>
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

                    <form action="{{ route('store.products.update', $product) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="form-group mb-3">
                            <label for="category_id">Categoria</label>
                            <select class="form-select @error('category_id') is-invalid @enderror" 
                                    id="category_id" 
                                    name="category_id" 
                                    required>
                                <option value="">Selecione uma categoria</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" 
                                            {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="name">Nome do Produto</label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $product->name) }}" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="price">Preço</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="text" 
                                       class="form-control @error('price') is-invalid @enderror" 
                                       id="price" 
                                       name="price" 
                                       value="{{ old('price', number_format($product->price, 2, ',', '.')) }}" 
                                       required>
                            </div>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="description">Descrição</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3">{{ old('description', $product->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="image">Imagem</label>
                            <input type="file" 
                                   class="form-control @error('image') is-invalid @enderror" 
                                   id="image" 
                                   name="image"
                                   accept="image/*">
                            @if($product->image)
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $product->image) }}" 
                                         alt="{{ $product->name }}"
                                         class="img-thumbnail"
                                         style="max-height: 100px;">
                                </div>
                            @endif
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" 
                                   class="form-check-input @error('active') is-invalid @enderror" 
                                   id="active" 
                                   name="active" 
                                   value="1"
                                   {{ old('active', $product->active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="active">
                                Produto ativo
                            </label>
                            @error('active')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-4">
                            <input type="checkbox" 
                                   class="form-check-input @error('is_quick_item') is-invalid @enderror" 
                                   id="is_quick_item" 
                                   name="is_quick_item" 
                                   value="1"
                                   {{ old('is_quick_item', $product->is_quick_item) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_quick_item">
                                Item rápido
                            </label>
                            <small class="d-block text-muted mt-1">Itens rápidos (ex: refrigerantes) não vão para a cozinha, são entregues diretamente pelo garçom.</small>
                            @error('is_quick_item')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('store.products.index') }}" class="btn btn-secondary">
                                Voltar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Atualizar Produto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/cleave.js@1.6.0/dist/cleave.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Máscara para preço
    new Cleave('#price', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand',
        numeralDecimalMark: ',',
        delimiter: '.',
        numeralDecimalScale: 2,
        numeralPositiveOnly: true
    });

    // Garantir que sempre tenha 2 casas decimais ao perder o foco
    document.getElementById('price').addEventListener('blur', function(e) {
        let value = e.target.value.replace(/\./g, '').replace(',', '.');
        value = parseFloat(value);
        if (!isNaN(value)) {
            e.target.value = value.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    });
});
</script>
@endpush
@endsection 
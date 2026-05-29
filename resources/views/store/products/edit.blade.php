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

        .form-control,
        .form-select {
            border: 2px solid #ecf0f1;
            border-radius: 10px;
            padding: 15px 20px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control:focus,
        .form-select:focus {
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
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            white-space: nowrap;
            margin-bottom: 0;
            margin-right: 1.2rem;
        }

        .form-check-input {
            width: 20px;
            height: 20px;
            margin: 0;
            border: 2px solid #ecf0f1;
            border-radius: 6px;
            background: #f8f9fa;
            transition: all 0.3s ease;
            vertical-align: middle;
            flex-shrink: 0;
        }

        .form-check-label {
            display: inline-flex;
            align-items: center;
            margin-bottom: 0;
            color: #2c3e50;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            line-height: 1;
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
        .btn-primary,
        .btn-secondary {
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

        .ingredients-card {
            background: #f8fbff;
            border: 1px solid #dfe9f5;
            border-radius: 18px;
            padding: 24px;
            margin-bottom: 24px;
        }

        .ingredients-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .ingredients-card-header h2 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
            color: #243447;
        }

        .ingredients-card-header p {
            margin: 0.35rem 0 0;
            color: #6f7d95;
            font-size: 0.95rem;
            max-width: 520px;
        }

        .btn-add-ingredient {
            min-width: 220px;
        }

        .ingredients-table {
            background: white;
            border: 1px solid #e6edf7;
            border-radius: 16px;
            overflow: hidden;
        }

        .ingredients-table .ingredients-row {
            display: grid;
            grid-template-columns: 1.2fr 1fr 0.9fr 0.8fr;
            gap: 16px;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid #f1f5fb;
        }

        .ingredients-table .ingredients-row:last-child {
            border-bottom: none;
        }

        .ingredients-table .ingredients-header {
            background: #f5f9ff;
            font-weight: 700;
            color: #5f7088;
        }

        .ingredient-label {
            display: none;
            margin-bottom: 0.5rem;
            color: #6f7d95;
            font-size: 0.9rem;
        }

        .ingredient-actions {
            display: flex;
            justify-content: flex-end;
        }

        .btn-remove-ingredient {
            width: 100%;
            border-radius: 12px;
            padding: 12px 16px;
        }

        @media (max-width: 767.98px) {
            .ingredients-table .ingredients-row {
                grid-template-columns: 1fr;
            }

            .ingredient-label {
                display: block;
            }

            .ingredient-actions {
                justify-content: flex-start;
            }
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
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('store.products.update', $product) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="form-group mb-3">
                                <label for="category_id">Categoria</label>
                                <select class="form-select @error('category_id') is-invalid @enderror" id="category_id"
                                    name="category_id" required>
                                    <option value="">Selecione uma categoria</option>
                                    @foreach ($categories as $category)
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
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name', $product->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="price">Preço</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="text" class="form-control @error('price') is-invalid @enderror"
                                        id="price" name="price"
                                        value="{{ old('price', number_format($product->price, 2, ',', '.')) }}" required>
                                </div>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="description">Descrição</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                    rows="3">{{ old('description', $product->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="image">Imagem</label>
                                <input type="file" class="form-control @error('image') is-invalid @enderror"
                                    id="image" name="image" accept="image/*">
                                @if ($product->image)
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                            class="img-thumbnail" style="max-height: 100px;">
                                    </div>
                                @endif
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-4">
                                <label class="form-label">Produto customizável</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="customizable"
                                            id="customizable_yes" value="1"
                                            {{ old('customizable', $product->customizable) == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="customizable_yes">Sim</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="customizable"
                                            id="customizable_no" value="0"
                                            {{ old('customizable', $product->customizable) == '0' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="customizable_no">Não</label>
                                    </div>
                                </div>
                                @error('customizable')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="ingredients-card" id="ingredients-card"
                                style="display: {{ old('customizable', $product->customizable) == '1' ? 'block' : 'none' }};">
                                <div class="ingredients-card-header">
                                    <div>
                                        <h2>Ingredientes</h2>
                                        <p>Adicione os ingredientes que compõem este produto.</p>
                                    </div>

                                    <button type="button" class="btn btn-primary btn-add-ingredient">
                                        + Adicionar Ingrediente
                                    </button>
                                </div>

                                <div class="ingredients-table">
                                    <div class="ingredients-row ingredients-header">
                                        <div>Nome do Ingrediente</div>
                                        <div>Valor Unitário</div>
                                        <div>Qtd. no produto</div>
                                        <div>Ações</div>
                                    </div>

                                    <div id="ingredient-rows">
                                        @php
                                            $storedIngredients = old('ingredients');
                                            if ($storedIngredients === null) {
                                                $storedIngredients = $product->additionalIngredients
                                                    ->map(function ($ingredient) {
                                                        return [
                                                            'name' => $ingredient->name,
                                                            'additional_price' => number_format(
                                                                $ingredient->additional_price,
                                                                2,
                                                                ',',
                                                                '.',
                                                            ),
                                                            'amount_item' => $ingredient->amount_item,
                                                        ];
                                                    })
                                                    ->toArray();

                                                if (empty($storedIngredients)) {
                                                    $storedIngredients = [
                                                        ['name' => '', 'additional_price' => '', 'amount_item' => 0],
                                                    ];
                                                }
                                            }
                                        @endphp

                                        @foreach ($storedIngredients as $index => $ingredient)
                                            <div class="ingredients-row ingredient-row">
                                                <div>
                                                    <label class="ingredient-label">Nome do Ingrediente</label>
                                                    <input type="text" name="ingredients[{{ $index }}][name]"
                                                        class="form-control" value="{{ $ingredient['name'] ?? '' }}"
                                                        placeholder="Nome do Ingrediente">
                                                </div>
                                                <div>
                                                    <label class="ingredient-label">Valor Unitário</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">R$</span>
                                                        <input type="text"
                                                            name="ingredients[{{ $index }}][additional_price]"
                                                            class="form-control ingredient-price"
                                                            value="{{ $ingredient['additional_price'] ?? '' }}"
                                                            placeholder="0,00">
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="ingredient-label">Qtd. no produto</label>
                                                    <input type="number" min="0"
                                                        name="ingredients[{{ $index }}][amount_item]"
                                                        class="form-control"
                                                        value="{{ $ingredient['amount_item'] ?? 0 }}">
                                                </div>
                                                <div class="ingredient-actions">
                                                    <button type="button"
                                                        class="btn btn-outline-danger btn-remove-ingredient">
                                                        <i class="fas fa-trash-alt"></i> Remover
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <template id="ingredient-template">
                                <div class="ingredients-row ingredient-row">
                                    <div>
                                        <label class="ingredient-label">Nome do Ingrediente</label>
                                        <input type="text" name="ingredients[__INDEX__][name]" class="form-control"
                                            placeholder="Nome do Ingrediente">
                                    </div>
                                    <div>
                                        <label class="ingredient-label">Valor Unitário</label>
                                        <div class="input-group">
                                            <span class="input-group-text">R$</span>
                                            <input type="text" name="ingredients[__INDEX__][additional_price]"
                                                class="form-control ingredient-price" placeholder="0,00">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="ingredient-label">Qtd. no produto</label>
                                        <input type="number" min="0" name="ingredients[__INDEX__][amount_item]"
                                            class="form-control" value="0">
                                    </div>
                                    <div class="ingredient-actions">
                                        <button type="button" class="btn btn-outline-danger btn-remove-ingredient">
                                            <i class="fas fa-trash-alt"></i> Remover
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input @error('active') is-invalid @enderror"
                                    id="active" name="active" value="1"
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
                                    id="is_quick_item" name="is_quick_item" value="1"
                                    {{ old('is_quick_item', $product->is_quick_item) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_quick_item">
                                    Item rápido
                                </label>
                                <small class="d-block text-muted mt-1">Itens rápidos (ex: refrigerantes) não vão para a
                                    cozinha, são entregues diretamente pelo garçom.</small>
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

                function formatIngredientPriceField(field) {
                    new Cleave(field, {
                        numeral: true,
                        numeralDecimalMark: ',',
                        delimiter: '.',
                        numeralDecimalScale: 2,
                        numeralThousandsGroupStyle: 'thousand',
                        numeralPositiveOnly: true
                    });
                }

                function updateIngredientIndexes() {
                    document.querySelectorAll('#ingredient-rows .ingredient-row').forEach(function(row, index) {
                        row.querySelectorAll('input').forEach(function(input) {
                            var name = input.getAttribute('name');
                            if (!name) return;
                            var updatedName = name.replace(/ingredients\[\d+\]/, 'ingredients[' +
                                index + ']');
                            input.setAttribute('name', updatedName);
                        });
                    });
                }

                function setIngredientsInputsDisabled(disabled) {
                    document.querySelectorAll('#ingredients-card input').forEach(function(field) {
                        field.disabled = disabled;
                    });
                    document.querySelectorAll('#ingredients-card button').forEach(function(button) {
                        if (!button.classList.contains('btn-add-ingredient')) {
                            button.disabled = disabled;
                        }
                    });
                }

                function toggleIngredientsCard() {
                    var customizable = document.querySelector('input[name="customizable"]:checked').value === '1';
                    var card = document.getElementById('ingredients-card');
                    card.style.display = customizable ? 'block' : 'none';
                    setIngredientsInputsDisabled(!customizable);
                }

                function addIngredientRow(name = '', price = '', amount = 0) {
                    var template = document.getElementById('ingredient-template').innerHTML;
                    var rowCount = document.querySelectorAll('#ingredient-rows .ingredient-row').length;
                    var html = template.replace(/__INDEX__/g, rowCount);
                    var container = document.createElement('div');
                    container.innerHTML = html;
                    var row = container.firstElementChild;
                    row.querySelector('input[name="ingredients[' + rowCount + '][name]"]').value = name;
                    row.querySelector('input[name="ingredients[' + rowCount + '][additional_price]"]').value = price;
                    row.querySelector('input[name="ingredients[' + rowCount + '][amount_item]"]').value = amount;
                    document.getElementById('ingredient-rows').appendChild(row);
                    formatIngredientPriceField(row.querySelector('.ingredient-price'));
                    updateIngredientIndexes();
                }

                toggleIngredientsCard();

                document.querySelectorAll('input[name="customizable"]').forEach(function(radio) {
                    radio.addEventListener('change', toggleIngredientsCard);
                });

                document.querySelector('.btn-add-ingredient').addEventListener('click', function() {
                    addIngredientRow();
                });

                document.getElementById('ingredient-rows').addEventListener('click', function(event) {
                    var removeButton = event.target.closest('.btn-remove-ingredient');
                    if (!removeButton) return;
                    var row = removeButton.closest('.ingredient-row');
                    if (!row) return;
                    row.remove();
                    if (document.querySelectorAll('#ingredient-rows .ingredient-row').length === 0) {
                        addIngredientRow();
                    }
                    updateIngredientIndexes();
                });

                document.querySelectorAll('.ingredient-price').forEach(function(field) {
                    formatIngredientPriceField(field);
                });
            });
        </script>
    @endpush
@endsection

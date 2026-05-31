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
        .products-container {
            background: transparent;
            padding: 20px 0;
            margin-top: 0;
        }

        /* Título principal */
        .products-title {
            color: #e8e8e9;
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-align: left;
            letter-spacing: -0.02em;
        }

        [data-bs-theme="light"] .products-title {
            color: #000000;
        }

        /* Botão de novo produto */
        .btn-new-product {
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

        .btn-new-product:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.4);
            color: #e8e8e9;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            text-decoration: none;
        }

        /* Card principal */
        .products-card {
            background: white;
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .products-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
        }

        .products-card .card-body {
            padding: 40px;
        }
    </style>

    <div class="container products-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="products-title">Produtos</h1>
            <a href="{{ route('store.products.create') }}" class="btn btn-new-product">
                <i class="fas fa-plus-circle me-2"></i> Novo Produto
            </a>
        </div>

        <div class="card products-card">
            <div class="card-body">
                @if ($categories->isEmpty())
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Você precisa criar pelo menos uma categoria antes de adicionar produtos.
                        <a href="{{ route('store.categories.create') }}" class="alert-link">Criar categoria</a>
                    </div>
                @endif

                @if ($products->isEmpty())
                    <div class="text-center py-4">
                        <i class="fas fa-box-open text-muted mb-3" style="font-size: 3rem;"></i>
                        <p class="text-muted">Nenhum produto cadastrado.</p>
                        @if (!$categories->isEmpty())
                            <a href="{{ route('store.products.create') }}" class="btn btn-primary">
                                Criar Primeiro Produto
                            </a>
                        @endif
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th style="width: 100px;">Imagem</th>
                                    <th>Nome</th>
                                    <th>Categoria</th>
                                    <th>Preço</th>
                                    <th>Descrição</th>
                                    <th>Status</th>
                                    <th style="width: 200px;">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="sortable-products">
                                @foreach ($products as $product)
                                    <tr data-id="{{ $product->id }}">
                                        <td>
                                            <i class="fas fa-grip-vertical text-muted cursor-move"></i>
                                        </td>
                                        <td>
                                            @if ($product->image)
                                                <img src="{{ asset('storage/' . $product->image) }}"
                                                    alt="{{ $product->name }}" class="img-thumbnail"
                                                    style="max-height: 50px;">
                                            @else
                                                <div class="text-muted text-center">
                                                    <i class="fas fa-image"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $product->name }}
                                            @if ($product->is_quick_item)
                                                <span class="badge bg-info ms-1"
                                                    title="Item rápido - não vai para a cozinha">
                                                    <i class="fas fa-bolt"></i>
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($product->category)
                                                {{ $product->category->name }}
                                            @else
                                                <span class="text-muted">Sem categoria</span>
                                            @endif
                                        </td>
                                        <td>R$ {{ number_format($product->price, 2, ',', '.') }}</td>
                                        <td>{{ Str::limit($product->description, 50) }}</td>
                                        <td>
                                            @if ($product->active)
                                                <span class="badge bg-success">Ativo</span>
                                            @else
                                                <span class="badge bg-danger">Inativo</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('store.products.edit', $product) }}"
                                                    class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    onclick="confirmDelete({{ $product->id }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                            <form id="delete-form-{{ $product->id }}"
                                                action="{{ route('store.products.destroy', $product) }}" method="POST"
                                                class="d-none">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Inicializar Sortable
                new Sortable(document.querySelector('.sortable-products'), {
                    handle: '.cursor-move',
                    animation: 150,
                    onEnd: function(evt) {
                        const items = [...evt.to.children].map((tr, index) => ({
                            id: tr.dataset.id,
                            order: index
                        }));

                        // Enviar nova ordem para o servidor
                        fetch('{{ route('store.products.reorder') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                products: items
                            })
                        });
                    }
                });
            });

            function confirmDelete(id) {
                if (confirm('Tem certeza que deseja excluir este produto?')) {
                    document.getElementById('delete-form-' + id).submit();
                }
            }
        </script>
    @endpush

    @push('styles')
        <style>
            /* Alertas modernos */
            .alert-success {
                background: #d4edda;
                border: 1px solid #c3e6cb;
                border-radius: 12px;
                color: #155724;
                padding: 20px;
                margin-bottom: 20px;
            }

            .alert-warning {
                background: #fff3cd;
                border: 1px solid #ffeaa7;
                border-radius: 12px;
                color: #856404;
                padding: 20px;
                margin-bottom: 20px;
            }

            .alert-link {
                color: #3498db;
                text-decoration: none;
                font-weight: 600;
            }

            .alert-link:hover {
                color: #2980b9;
                text-decoration: underline;
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

            /* Imagens dos produtos */
            .img-thumbnail {
                border-radius: 12px;
                border: none;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                transition: all 0.3s ease;
            }

            .img-thumbnail:hover {
                transform: scale(1.1);
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
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
            }

            .btn-sm:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            }

            .btn-outline-primary {
                border: 2px solid #3498db;
                color: #3498db;
            }

            .btn-outline-primary:hover {
                background: #3498db;
                border-color: #3498db;
                color: white;
            }

            .btn-outline-danger {
                border: 2px solid #e74c3c;
                color: #e74c3c;
            }

            .btn-outline-danger:hover {
                background: #e74c3c;
                border-color: #e74c3c;
                color: white;
            }

            /* Estado vazio */
            .text-center.py-4 {
                padding: 60px 20px !important;
            }

            .text-center.py-4 i {
                color: #bdc3c7;
                margin-bottom: 20px;
            }

            .text-center.py-4 p {
                color: #7f8c8d;
                font-weight: 600;
                font-size: 1.1rem;
            }

            .text-center.py-4 .btn-primary {
                background: #3498db;
                border: none;
                border-radius: 12px;
                padding: 12px 24px;
                font-weight: 600;
                transition: all 0.3s ease;
            }

            .text-center.py-4 .btn-primary:hover {
                background: #2980b9;
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            }

            /* Cursor para arrastar */
            .cursor-move {
                cursor: move;
                color: #bdc3c7;
                transition: color 0.3s ease;
            }

            .cursor-move:hover {
                color: #3498db;
            }

            /* Placeholder para imagem */
            .text-muted.text-center {
                background: #f8f9fa;
                border-radius: 8px;
                padding: 15px;
                color: #bdc3c7 !important;
            }
        </style>
    @endpush
@endsection

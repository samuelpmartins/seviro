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
        .categories-container {
            background: transparent;
            padding: 20px 0;
            margin-top: 0;
        }

        /* Título principal */
        .categories-title {
            color: #e8e8e9;
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-align: left;
            letter-spacing: -0.02em;
        }

        [data-bs-theme="light"] .categories-title {
            color: #000000;
        }

        /* Botão de nova categoria */
        .btn-new-category {
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

        .btn-new-category:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.4);
            color: #e8e8e9;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            text-decoration: none;
        }

        /* Card principal */
        .categories-card {
            background: white;
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .categories-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
        }

        .categories-card .card-body {
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
            transition: all 0.2s ease;
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
    </style>

    <div class="container categories-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="categories-title">Categorias</h1>
            <a href="{{ route('store.categories.create') }}" class="btn btn-new-category">
                <i class="fas fa-plus-circle me-2"></i> Nova Categoria
            </a>
        </div>

        <div class="card categories-card">
            <div class="card-body">
                @if ($categories->isEmpty())
                    <div class="text-center py-4">
                        <i class="fas fa-folder-open text-muted mb-3" style="font-size: 3rem;"></i>
                        <p class="text-muted">Nenhuma categoria cadastrada.</p>
                        <a href="{{ route('store.categories.create') }}" class="btn btn-primary">
                            Criar Primeira Categoria
                        </a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Nome</th>
                                    <th>Descrição</th>
                                    <th>Produtos</th>
                                    <th>Status</th>
                                    <th style="width: 200px;">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="sortable-categories">
                                @foreach ($categories as $category)
                                    <tr data-id="{{ $category->id }}">
                                        <td>
                                            <i class="fas fa-grip-vertical text-muted cursor-move"></i>
                                        </td>
                                        <td>{{ $category->name }}</td>
                                        <td>{{ $category->description ?? '-' }}</td>
                                        <td>{{ $category->products_count ?? 0 }}</td>
                                        <td>
                                            @if ($category->suspended)
                                                <span class="badge bg-warning text-dark">Suspensa</span>
                                            @else
                                                <span class="badge bg-success">Ativa</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('store.categories.edit', $category) }}"
                                                    class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    onclick="confirmDelete({{ $category->id }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                            <form id="delete-form-{{ $category->id }}"
                                                action="{{ route('store.categories.destroy', $category) }}" method="POST"
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

                    {{ $categories->links() }}
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
        <script>
            // Inicializar Sortable
            new Sortable(document.getElementById('sortable-categories'), {
                handle: '.cursor-move',
                animation: 150,
                onEnd: function(evt) {
                    const items = [...evt.to.children].map((tr, index) => ({
                        id: tr.dataset.id,
                        order: index
                    }));

                    // Enviar nova ordem para o servidor
                    fetch('{{ route('store.categories.reorder') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            categories: items
                        })
                    });
                }
            });

            // Função para confirmar exclusão
            function confirmDelete(id) {
                if (confirm('Tem certeza que deseja excluir esta categoria?')) {
                    document.getElementById('delete-form-' + id).submit();
                }
            }
        </script>
    @endpush
@endsection

@extends('layouts.store-base')

@section('content')
    <style>
        /* Fundo preto por padrão */
        body {
            background: #000000;
            color: #e8e8e9;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        /* Tema light - fundo cinza claro */
        [data-bs-theme="light"] body {
            background: #e8e8e9;
            color: #000000;
        }

        /* Navbar - Nova Identidade Visual */
        .navbar {
            background: #000000 !important;
            backdrop-filter: blur(10px);
            border: none !important;
            border-bottom: 2px solid #9da1a1;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand,
        .nav-link,
        .navbar-toggler-icon {
            color: #e8e8e9 !important;
        }

        .navbar-brand:hover,
        .nav-link:hover {
            color: #9da1a1 !important;
        }

        /* Container principal */
        .restaurant-edit-container {
            display: flex;
            min-height: calc(100vh - 120px);
            padding: 20px 0;
            gap: 0;
            justify-content: center;
            align-items: flex-start;
        }

        /* Conteúdo principal (lado esquerdo) - OCULTO */
        .main-content {
            display: none;
        }

        /* Menu de edição - Página inteira */
        .sidebar-menu {
            width: 100%;
            max-width: 900px;
            background: white;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
            padding: 0;
            margin: 0 30px;
            border-radius: 20px;
            overflow: hidden;
        }


        /* Accordion (sanfona) */
        .accordion {
            border: none;
        }

        .accordion-item {
            border: none;
            border-bottom: 1px solid #ecf0f1;
            background: transparent;
        }

        .accordion-button {
            background: white;
            color: #2c3e50;
            font-weight: 600;
            font-size: 1rem;
            padding: 20px 25px;
            border: none;
            box-shadow: none !important;
            transition: all 0.3s ease;
        }

        .accordion-item:first-child .accordion-button {
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
        }

        .accordion-button:not(.collapsed) {
            background: #f8f9fa;
            color: #3498db;
        }

        .accordion-button::after {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%232c3e50'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
        }

        .accordion-button:not(.collapsed)::after {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%233498db'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
        }

        .accordion-body {
            padding: 0;
            background: white;
        }

        /* Conteúdo do accordion */
        .accordion-content {
            padding: 15px 25px 20px;
        }

        /* Estabelecimento */
        .establishment-info {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .establishment-logo {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .establishment-logo-placeholder {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            background: linear-gradient(135deg, #3498db, #2c3e50);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            border: 3px solid white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .establishment-name {
            flex: 1;
        }

        .establishment-name h4 {
            margin: 0;
            color: #2c3e50;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .establishment-name p {
            margin: 5px 0 0;
            color: #7f8c8d;
            font-size: 0.85rem;
        }

        /* Lista de categorias/produtos */
        .category-item {
            border-bottom: 1px solid #ecf0f1;
            padding: 15px 0;
        }

        .category-item:last-child {
            border-bottom: none;
        }

        .category-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .category-name {
            flex: 1;
            font-weight: 600;
            color: #2c3e50;
            font-size: 1rem;
        }

        .product-list {
            margin-left: 45px;
            margin-top: 10px;
        }

        .product-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .product-item:last-child {
            border-bottom: none;
        }

        .product-image {
            width: 40px;
            height: 40px;
            border-radius: 6px;
            object-fit: cover;
        }

        .product-image-placeholder {
            width: 40px;
            height: 40px;
            border-radius: 6px;
            background: #ecf0f1;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #95a5a6;
            font-size: 1rem;
        }

        .product-info {
            flex: 1;
        }

        .product-name {
            font-size: 0.9rem;
            color: #2c3e50;
            margin: 0;
        }

        .product-price {
            font-size: 0.85rem;
            color: #27ae60;
            font-weight: 600;
            margin: 0;
        }

        /* Lista de mesas */
        .table-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .table-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .table-card.available {
            border-color: #27ae60;
        }

        .table-card.occupied {
            border-color: #e74c3c;
            background: #fdf2f2;
        }

        .table-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .table-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }

        .table-status {
            font-size: 0.8rem;
            margin: 5px 0 0;
        }

        .table-status.available {
            color: #27ae60;
        }

        .table-status.occupied {
            color: #e74c3c;
        }

        /* Lista de pedidos */
        .order-item {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }

        .order-item:hover {
            background: #ecf0f1;
            transform: translateX(3px);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .order-table-number {
            font-weight: 600;
            color: #2c3e50;
        }

        .order-total {
            font-weight: 700;
            color: #27ae60;
            font-size: 1rem;
        }

        .order-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
        }

        .order-date {
            color: #7f8c8d;
        }

        /* Botões de ação */
        .action-btn {
            border-radius: 8px;
            font-weight: 600;
            padding: 10px 20px;
            transition: all 0.3s ease;
            border: none;
            width: 100%;
            margin-top: 15px;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .action-btn-primary {
            background: #3498db;
            color: white;
        }

        .action-btn-primary:hover {
            background: #2980b9;
            color: white;
        }

        .action-btn-success {
            background: #27ae60;
            color: white;
        }

        .action-btn-success:hover {
            background: #229954;
            color: white;
        }

        /* Badges */
        .badge {
            font-size: 0.7rem;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 600;
        }

        /* Filtro de pedidos */
        .filter-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .filter-input {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .filter-input input {
            flex: 1;
            border: 2px solid #ecf0f1;
            border-radius: 8px;
            padding: 10px 15px;
            font-size: 0.9rem;
        }

        .filter-input input:focus {
            border-color: #3498db;
            outline: none;
        }

        .filter-btn {
            background: #3498db;
            color: white;
            border: none;
            border-radius: 8px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-btn:hover {
            background: #2980b9;
            transform: scale(1.05);
        }

        /* Conteúdo principal - cards de boas-vindas */
        .welcome-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .welcome-title {
            color: #2c3e50;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .welcome-text {
            color: #7f8c8d;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        /* Contador vazio */
        .empty-state {
            text-align: center;
            padding: 30px;
            color: #95a5a6;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .empty-state p {
            margin: 0;
            font-size: 0.9rem;
        }

        /* Responsividade */
        @media (max-width: 992px) {
            .sidebar-menu {
                margin: 15px;
                border-radius: 15px;
            }
        }

        @media (max-width: 576px) {
            .sidebar-menu {
                margin: 10px;
                border-radius: 10px;
            }
        }

        /* Scrollbar personalizada */
        .sidebar-menu::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar-menu::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .sidebar-menu::-webkit-scrollbar-thumb {
            background: #3498db;
            border-radius: 3px;
        }

        .sidebar-menu::-webkit-scrollbar-thumb:hover {
            background: #2980b9;
        }
    </style>

    <div class="restaurant-edit-container">
        <!-- Conteúdo Principal (Lado Esquerdo) -->
        <div class="main-content">
            <div class="welcome-card">
                <h1 class="welcome-title">Editar Restaurante</h1>
                <p class="welcome-text">
                    Use o menu lateral direito para gerenciar todos os aspectos do seu restaurante.
                    Você pode visualizar e editar informações do estabelecimento, gerenciar produtos e categorias,
                    controlar mesas e acompanhar pedidos em tempo real.
                </p>
            </div>

            <!-- Cards de estatísticas rápidas -->
            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="card stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-subtitle mb-2 text-muted">Categorias</h6>
                                    <h2 class="card-title mb-0">{{ $categories->count() }}</h2>
                                </div>
                                <div class="fs-1 text-success">
                                    <i class="fas fa-tags"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-4">
                    <div class="card stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-subtitle mb-2 text-muted">Produtos</h6>
                                    <h2 class="card-title mb-0">
                                        {{ $categories->sum(function ($cat) {return $cat->products->count();}) }}</h2>
                                </div>
                                <div class="fs-1 text-primary">
                                    <i class="fas fa-box"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-4">
                    <div class="card stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-subtitle mb-2 text-muted">Mesas</h6>
                                    <h2 class="card-title mb-0">{{ $tables->count() }}</h2>
                                </div>
                                <div class="fs-1 text-info">
                                    <i class="fas fa-chair"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-4">
                    <div class="card stats-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-subtitle mb-2 text-muted">Pedidos</h6>
                                    <h2 class="card-title mb-0">{{ $orders->total() }}</h2>
                                </div>
                                <div class="fs-1 text-warning">
                                    <i class="fas fa-receipt"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu Lateral Direito -->
        <div class="sidebar-menu">
            <!-- Accordion (Sanfona) -->
            <div class="accordion" id="editMenuAccordion">

                <!-- 1. Estabelecimento -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseEstablishment">
                            <i class="fas fa-store me-2"></i> Estabelecimento
                        </button>
                    </h2>
                    <div id="collapseEstablishment" class="accordion-collapse collapse show"
                        data-bs-parent="#editMenuAccordion">
                        <div class="accordion-body">
                            <div class="accordion-content">
                                <div class="establishment-info">
                                    @if ($store->logo)
                                        <img src="{{ asset('storage/' . $store->logo) }}" alt="{{ $store->name }}"
                                            class="establishment-logo">
                                    @else
                                        <div class="establishment-logo-placeholder">
                                            <i class="fas fa-store"></i>
                                        </div>
                                    @endif
                                    <div class="establishment-name">
                                        <h4>{{ $store->name }}</h4>
                                        <p>{{ $store->address }}</p>
                                    </div>
                                </div>
                                <a href="{{ route('store.edit') }}" class="btn action-btn action-btn-primary">
                                    <i class="fas fa-edit me-2"></i> Editar Informações Básicas
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Produtos -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseProducts">
                            <i class="fas fa-box me-2"></i> Produtos
                        </button>
                    </h2>
                    <div id="collapseProducts" class="accordion-collapse collapse" data-bs-parent="#editMenuAccordion">
                        <div class="accordion-body">
                            <div class="accordion-content">
                                <div id="sortable-categories">
                                    @forelse($categories as $category)
                                        <div class="category-item" data-id="{{ $category->id }}">
                                            <div class="category-header">
                                                <i class="fas fa-grip-vertical text-muted cursor-move me-2"
                                                    style="cursor: move;"></i>
                                                <span class="category-name">{{ $category->name }}</span>
                                                <span class="badge bg-primary">{{ $category->products->count() }}</span>
                                                <button type="button" class="btn btn-sm btn-link text-primary ms-2"
                                                    style="text-decoration: none; padding: 2px 8px;"
                                                    data-category-id="{{ $category->id }}"
                                                    data-category-name="{{ $category->name }}"
                                                    data-category-description="{{ $category->description }}"
                                                    onclick="openEditCategoryModal(this)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>

                                            @if ($category->products->count() > 0)
                                                <div class="product-list sortable-products"
                                                    data-category-id="{{ $category->id }}">
                                                    @foreach ($category->products as $product)
                                                        <div class="product-item" data-id="{{ $product->id }}">
                                                            <i class="fas fa-grip-vertical text-muted cursor-move me-2"
                                                                style="cursor: move; font-size: 0.8rem;"></i>
                                                            @if ($product->image)
                                                                <img src="{{ asset('storage/' . $product->image) }}"
                                                                    alt="{{ $product->name }}" class="product-image">
                                                            @else
                                                                <div class="product-image-placeholder">
                                                                    <i class="fas fa-image"></i>
                                                                </div>
                                                            @endif
                                                            <div class="product-info">
                                                                <p class="product-name">{{ $product->name }}</p>
                                                                <p class="product-price">R$
                                                                    {{ number_format($product->price, 2, ',', '.') }}</p>
                                                            </div>
                                                            @if ($product->is_quick_item)
                                                                <span class="badge bg-info me-1"
                                                                    title="Item rápido - entrega direta pelo garçom">
                                                                    <i class="fas fa-bolt"></i>
                                                                </span>
                                                            @endif
                                                            @if (!$product->active)
                                                                <span class="badge bg-secondary">Inativo</span>
                                                            @endif
                                                            <button type="button"
                                                                class="btn btn-sm btn-link text-primary"
                                                                style="text-decoration: none; padding: 2px 8px;"
                                                                data-product-id="{{ $product->id }}"
                                                                data-product-category-id="{{ $product->category_id }}"
                                                                data-product-name="{{ $product->name }}"
                                                                data-product-price="{{ number_format($product->price, 2, ',', '.') }}"
                                                                data-product-description="{{ $product->description }}"
                                                                data-product-image="{{ $product->image }}"
                                                                data-product-customizable="{{ $product->customizable ? '1' : '0' }}"
                                                                @php
$productIngredients = $product->additionalIngredients->map(function ($ingredient) {
                                                                        return [
                                                                            'name' => $ingredient->name,
                                                                            'additional_price' => number_format($ingredient->additional_price, 2, ',', '.'),
                                                                            'amount_item' => $ingredient->amount_item,
                                                                        ];
                                                                    }); @endphp
                                                                data-product-ingredients="{{ $productIngredients->toJson() }}"
                                                                data-product-active="{{ $product->active ? 'true' : 'false' }}"
                                                                data-product-is-quick-item="{{ $product->is_quick_item ? 'true' : 'false' }}"
                                                                onclick="openEditProductModal(this)">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="empty-state" style="padding: 15px;">
                                                    <p style="font-size: 0.8rem; margin: 0;">Nenhum produto nesta categoria
                                                    </p>
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="empty-state">
                                            <i class="fas fa-box-open"></i>
                                            <p>Nenhuma categoria cadastrada</p>
                                        </div>
                                    @endforelse
                                </div>

                                <button type="button" class="btn action-btn action-btn-success" data-bs-toggle="modal"
                                    data-bs-target="#newCategoryModal">
                                    <i class="fas fa-plus-circle me-2"></i> Nova Categoria
                                </button>
                                <button type="button" class="btn action-btn action-btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#newProductModal">
                                    <i class="fas fa-plus-circle me-2"></i> Novo Produto
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Mesas -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseTables">
                            <i class="fas fa-chair me-2"></i> Mesas
                        </button>
                    </h2>
                    <div id="collapseTables" class="accordion-collapse collapse" data-bs-parent="#editMenuAccordion">
                        <div class="accordion-body">
                            <div class="accordion-content">
                                @if ($tables->count() > 0)
                                    <div class="table-grid">
                                        @foreach ($tables as $table)
                                            <a href="{{ route('store.tables.edit', $table->id) }}"
                                                style="text-decoration: none;">
                                                <div class="table-card {{ $table->occupied ? 'occupied' : 'available' }}">
                                                    <p class="table-number">Mesa {{ $table->number }}</p>
                                                    <p
                                                        class="table-status {{ $table->occupied ? 'occupied' : 'available' }}">
                                                        {{ $table->occupied ? 'Ocupada' : 'Disponível' }}
                                                    </p>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="empty-state">
                                        <i class="fas fa-chair"></i>
                                        <p>Nenhuma mesa cadastrada</p>
                                    </div>
                                @endif

                                <button type="button" class="btn action-btn action-btn-success" data-bs-toggle="modal"
                                    data-bs-target="#newTableModal">
                                    <i class="fas fa-plus-circle me-2"></i> Nova Mesa
                                </button>
                                <a href="{{ route('store.tables.index') }}" class="btn action-btn action-btn-primary">
                                    <i class="fas fa-list me-2"></i> Ver Todas as Mesas
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Pedidos -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseOrders">
                            <i class="fas fa-receipt me-2"></i> Pedidos
                        </button>
                    </h2>
                    <div id="collapseOrders" class="accordion-collapse collapse" data-bs-parent="#editMenuAccordion">
                        <div class="accordion-body">
                            <div class="accordion-content">
                                <!-- Filtro de Pedidos -->
                                <div class="filter-section">
                                    <div class="filter-input">
                                        <input type="text" id="orderFilter" placeholder="Buscar por mesa ou valor..."
                                            onkeyup="filterOrders()">
                                        <button class="filter-btn" onclick="filterOrders()">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Lista de Pedidos -->
                                <div id="ordersList">
                                    @forelse($orders as $order)
                                        <div class="order-item"
                                            data-table="{{ $order->table ? $order->table->number : 'Balcão' }}"
                                            data-total="{{ $order->total }}" data-bs-toggle="modal"
                                            data-bs-target="#orderDetailModal{{ $order->id }}"
                                            style="cursor: pointer;">
                                            <div class="order-header">
                                                <span class="order-table-number">
                                                    <i class="fas fa-receipt me-1"></i> {{ $order->order_number }} -
                                                    {{ $order->table ? 'Mesa ' . $order->table->number : 'Balcão' }}
                                                </span>
                                                <span class="order-total">R$
                                                    {{ number_format($order->total, 2, ',', '.') }}</span>
                                            </div>
                                            <div class="order-info">
                                                <span
                                                    class="order-date">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                                                <span
                                                    class="badge bg-{{ $order->status === 'Aguardando pagamento'
                                                        ? 'warning'
                                                        : ($order->status === 'Em produção'
                                                            ? 'info'
                                                            : ($order->status === 'Finalizado'
                                                                ? 'success'
                                                                : 'primary')) }}">
                                                    {{ $order->status }}
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Modal de Detalhes do Pedido -->
                                        <div class="modal fade" id="orderDetailModal{{ $order->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content" style="border-radius: 15px; border: none;">
                                                    <div class="modal-header" style="border-bottom: 2px solid #ecf0f1;">
                                                        <h5 class="modal-title" style="color: #2c3e50; font-weight: 700;">
                                                            <i class="fas fa-receipt me-2"></i> Pedido
                                                            {{ $order->order_number }} -
                                                            {{ $order->table ? 'Mesa ' . $order->table->number : 'Balcão' }}
                                                        </h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body" style="padding: 25px;">
                                                        <div class="mb-3">
                                                            <strong style="color: #2c3e50;">Status:</strong>
                                                            <span
                                                                class="badge bg-{{ $order->status === 'Aguardando pagamento'
                                                                    ? 'warning'
                                                                    : ($order->status === 'Em produção'
                                                                        ? 'info'
                                                                        : ($order->status === 'Finalizado'
                                                                            ? 'success'
                                                                            : 'primary')) }}">
                                                                {{ $order->status }}
                                                            </span>
                                                        </div>
                                                        <div class="mb-3">
                                                            <strong style="color: #2c3e50;">Data:</strong>
                                                            {{ $order->created_at->format('d/m/Y H:i') }}
                                                        </div>
                                                        @if ($order->notes)
                                                            <div class="mb-3">
                                                                <strong style="color: #2c3e50;">Observações:</strong>
                                                                <p class="mb-0" style="color: #7f8c8d;">
                                                                    {{ $order->notes }}</p>
                                                            </div>
                                                        @endif
                                                        <div class="mb-3">
                                                            <strong style="color: #2c3e50;">Itens do Pedido:</strong>
                                                            <div style="margin-top: 10px;">
                                                                @foreach ($order->items as $item)
                                                                    <div
                                                                        style="display: flex; align-items: center; gap: 10px; padding: 10px; background: #f8f9fa; border-radius: 8px; margin-bottom: 8px;">
                                                                        @if ($item->product->image)
                                                                            <img src="{{ asset('storage/' . $item->product->image) }}"
                                                                                alt="{{ $item->product->name }}"
                                                                                style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px;">
                                                                        @endif
                                                                        <div style="flex: 1;">
                                                                            <strong
                                                                                style="color: #2c3e50;">{{ $item->product->name }}</strong>
                                                                            <br>
                                                                            <small style="color: #7f8c8d;">
                                                                                {{ $item->quantity }}x R$
                                                                                {{ number_format($item->price, 2, ',', '.') }}
                                                                            </small>
                                                                            @if ($item->notes)
                                                                                <br>
                                                                                <small
                                                                                    style="color: #7f8c8d; font-style: italic;">
                                                                                    <strong>Obs.:</strong>
                                                                                    {{ $item->notes }}
                                                                                </small>
                                                                            @endif
                                                                        </div>
                                                                        <span style="font-weight: 700; color: #27ae60;">
                                                                            R$
                                                                            {{ number_format($item->price * $item->quantity, 2, ',', '.') }}
                                                                        </span>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                        <div
                                                            style="text-align: right; padding-top: 15px; border-top: 2px solid #ecf0f1; margin-top: 15px;">
                                                            <strong style="color: #2c3e50; font-size: 1.2rem;">
                                                                Total: <span style="color: #27ae60;">R$
                                                                    {{ number_format($order->total, 2, ',', '.') }}</span>
                                                            </strong>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer" style="border-top: none; padding: 20px;">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal" style="border-radius: 8px;">
                                                            Fechar
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="empty-state">
                                            <i class="fas fa-receipt"></i>
                                            <p>Nenhum pedido encontrado</p>
                                        </div>
                                    @endforelse
                                </div>

                                @if ($orders->hasPages())
                                    <div class="mt-3">
                                        {{ $orders->links() }}
                                    </div>
                                @endif

                                <a href="{{ route('store.orders.history') }}" class="btn action-btn action-btn-primary">
                                    <i class="fas fa-history me-2"></i> Histórico Completo
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal Nova Categoria -->
    <div class="modal fade" id="newCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nova Categoria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="newCategoryForm" action="{{ route('store.categories.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="category_name" class="form-label">Nome da Categoria</label>
                            <input type="text" class="form-control" id="category_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="category_description" class="form-label">Descrição (opcional)</label>
                            <textarea class="form-control" id="category_description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Criar Categoria</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Novo Produto -->
    <div class="modal fade" id="newProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Novo Produto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="newProductForm" action="{{ route('store.products.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="product_category_id" class="form-label">Categoria</label>
                            <select class="form-select" id="product_category_id" name="category_id" required>
                                <option value="">Selecione uma categoria</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="product_name" class="form-label">Nome do Produto</label>
                            <input type="text" class="form-control" id="product_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="product_price" class="form-label">Preço</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="text" class="form-control" id="product_price" name="price" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="product_description" class="form-label">Descrição</label>
                            <textarea class="form-control" id="product_description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="product_image" class="form-label">Imagem</label>
                            <input type="file" class="form-control" id="product_image" name="image"
                                accept="image/*">
                        </div>
                        <div class="form-check mb-2">
                            <input type="checkbox" class="form-check-input" id="product_active" name="active"
                                value="1" checked>
                            <label class="form-check-label" for="product_active">
                                Produto ativo
                            </label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="product_is_quick_item"
                                name="is_quick_item" value="1">
                            <label class="form-check-label" for="product_is_quick_item">
                                Item rápido
                            </label>
                            <small class="d-block text-muted">Itens rápidos (ex: refrigerantes) não vão para a cozinha, são
                                entregues diretamente pelo garçom.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Produto customizável</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="customizable"
                                        id="product_customizable_yes" value="1">
                                    <label class="form-check-label" for="product_customizable_yes">Sim</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="customizable"
                                        id="product_customizable_no" value="0" checked>
                                    <label class="form-check-label" for="product_customizable_no">Não</label>
                                </div>
                            </div>
                        </div>

                        <div class="ingredients-card" id="product-ingredients-card" style="display: none;">
                            <div class="ingredients-card-header d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <h6 class="mb-0">Ingredientes</h6>
                                    <p class="mb-0 small text-muted">Adicione ingredientes para este produto.</p>
                                </div>
                                <button type="button" class="btn btn-sm btn-primary btn-add-ingredient-product">+
                                    Adicionar Ingrediente</button>
                            </div>

                            <div class="ingredients-table">
                                <div class="ingredients-row ingredients-header d-flex gap-2 p-2" style="font-weight:700">
                                    <div style="flex:1">Nome do Ingrediente</div>
                                    <div style="width:160px">Valor Unitário</div>
                                    <div style="width:120px">Qtd. no produto</div>
                                    <div style="width:100px">Ações</div>
                                </div>

                                <div id="product-ingredient-rows"></div>
                            </div>
                        </div>

                        <template id="product-ingredient-template">
                            <div class="ingredients-row ingredient-row d-flex gap-2 p-2 align-items-center">
                                <div style="flex:1">
                                    <input type="text" name="ingredients[__INDEX__][name]" class="form-control"
                                        placeholder="Nome do Ingrediente">
                                </div>
                                <div style="width:160px">
                                    <div class="input-group">
                                        <span class="input-group-text">R$</span>
                                        <input type="text" name="ingredients[__INDEX__][additional_price]"
                                            class="form-control ingredient-price" placeholder="0,00">
                                    </div>
                                </div>
                                <div style="width:120px">
                                    <input type="number" min="0" name="ingredients[__INDEX__][amount_item]"
                                        class="form-control" value="0">
                                </div>
                                <div style="width:100px">
                                    <button type="button"
                                        class="btn btn-outline-danger btn-remove-ingredient">Remover</button>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Criar Produto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Nova Mesa -->
    <div class="modal fade" id="newTableModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nova Mesa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="newTableForm" action="{{ route('store.tables.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="table_number" class="form-label">Número da Mesa</label>
                            <input type="text" class="form-control" id="table_number" name="number" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Criar Mesa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Categoria -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Categoria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editCategoryForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_category_name" class="form-label">Nome da Categoria</label>
                            <input type="text" class="form-control" id="edit_category_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_category_description" class="form-label">Descrição (opcional)</label>
                            <textarea class="form-control" id="edit_category_description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Atualizar Categoria</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Produto -->
    <div class="modal fade" id="editProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Produto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editProductForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_product_category_id" class="form-label">Categoria</label>
                            <select class="form-select" id="edit_product_category_id" name="category_id" required>
                                <option value="">Selecione uma categoria</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_product_name" class="form-label">Nome do Produto</label>
                            <input type="text" class="form-control" id="edit_product_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_product_price" class="form-label">Preço</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="text" class="form-control" id="edit_product_price" name="price"
                                    required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_product_description" class="form-label">Descrição</label>
                            <textarea class="form-control" id="edit_product_description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_product_image" class="form-label">Imagem</label>
                            <input type="file" class="form-control" id="edit_product_image" name="image"
                                accept="image/*">
                            <div id="current_image_preview" class="mt-2" style="display: none;">
                                <small class="text-muted">Imagem atual:</small><br>
                                <img id="current_image" src="" alt="Imagem atual" class="img-thumbnail"
                                    style="max-height: 100px;">
                            </div>
                        </div>
                        <div class="form-check mb-2">
                            <input type="checkbox" class="form-check-input" id="edit_product_active" name="active"
                                value="1">
                            <label class="form-check-label" for="edit_product_active">
                                Produto ativo
                            </label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="edit_product_is_quick_item"
                                name="is_quick_item" value="1">
                            <label class="form-check-label" for="edit_product_is_quick_item">
                                Item rápido
                            </label>
                            <small class="d-block text-muted">Itens rápidos (ex: refrigerantes) não vão para a cozinha, são
                                entregues diretamente pelo garçom.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Produto customizável</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="customizable"
                                        id="edit_product_customizable_yes" value="1">
                                    <label class="form-check-label" for="edit_product_customizable_yes">Sim</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="customizable"
                                        id="edit_product_customizable_no" value="0">
                                    <label class="form-check-label" for="edit_product_customizable_no">Não</label>
                                </div>
                            </div>
                        </div>

                        <div class="ingredients-card" id="edit-ingredients-card" style="display: none;">
                            <div class="ingredients-card-header d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <h6 class="mb-0">Ingredientes</h6>
                                    <p class="mb-0 small text-muted">Adicione ingredientes para este produto.</p>
                                </div>
                                <button type="button" class="btn btn-sm btn-primary btn-add-ingredient-edit">+ Adicionar
                                    Ingrediente</button>
                            </div>

                            <div class="ingredients-table">
                                <div class="ingredients-row ingredients-header d-flex gap-2 p-2" style="font-weight:700">
                                    <div style="flex:1">Nome do Ingrediente</div>
                                    <div style="width:160px">Valor Unitário</div>
                                    <div style="width:120px">Qtd. no produto</div>
                                    <div style="width:100px">Ações</div>
                                </div>

                                <div id="edit-ingredient-rows"></div>
                            </div>
                        </div>

                        <template id="edit-ingredient-template">
                            <div class="ingredients-row ingredient-row d-flex gap-2 p-2 align-items-center">
                                <div style="flex:1">
                                    <input type="text" name="ingredients[__INDEX__][name]" class="form-control"
                                        placeholder="Nome do Ingrediente">
                                </div>
                                <div style="width:160px">
                                    <div class="input-group">
                                        <span class="input-group-text">R$</span>
                                        <input type="text" name="ingredients[__INDEX__][additional_price]"
                                            class="form-control ingredient-price" placeholder="0,00">
                                    </div>
                                </div>
                                <div style="width:120px">
                                    <input type="number" min="0" name="ingredients[__INDEX__][amount_item]"
                                        class="form-control" value="0">
                                </div>
                                <div style="width:100px">
                                    <button type="button"
                                        class="btn btn-outline-danger btn-remove-ingredient">Remover</button>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Atualizar Produto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            /* Garantir que main não tenha padding */
            main.py-4 {
                padding: 0 !important;
            }

            /* Cards de estatísticas */
            .stats-card {
                background: white;
                border: none;
                border-radius: 16px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                transition: all 0.3s ease;
                overflow: hidden;
                border: 1px solid rgba(0, 0, 0, 0.05);
            }

            .stats-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            }

            .stats-card .card-body {
                padding: 25px;
            }

            .stats-card .card-subtitle {
                color: #6b7280;
                font-size: 0.875rem;
                font-weight: 500;
                margin-bottom: 8px;
            }

            .stats-card .card-title {
                color: #1f2937;
                font-size: 2rem;
                font-weight: 700;
                margin: 0;
            }

            .stats-card .fs-1 {
                opacity: 0.6;
                transition: all 0.3s ease;
                font-size: 2.5rem !important;
            }

            .stats-card:hover .fs-1 {
                opacity: 0.8;
                transform: scale(1.05);
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/cleave.js@1.6.0/dist/cleave.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
        <script>
            // Função para filtrar pedidos
            function filterOrders() {
                const filterValue = document.getElementById('orderFilter').value.toLowerCase();
                const orderItems = document.querySelectorAll('.order-item');

                orderItems.forEach(item => {
                    const table = item.getAttribute('data-table').toLowerCase();
                    const total = item.getAttribute('data-total').toLowerCase();
                    const text = item.textContent.toLowerCase();

                    if (table.includes(filterValue) || total.includes(filterValue) || text.includes(filterValue)) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            }

            // Garantir que os accordions funcionem corretamente
            document.addEventListener('DOMContentLoaded', function() {
                // Inicializar Bootstrap Accordion
                const accordionElements = document.querySelectorAll('.accordion-button');

                accordionElements.forEach(button => {
                    button.addEventListener('click', function() {
                        const target = this.getAttribute('data-bs-target');
                        const collapse = document.querySelector(target);

                        if (collapse) {
                            const bsCollapse = new bootstrap.Collapse(collapse, {
                                toggle: true
                            });
                        }
                    });
                });

                // Máscara para preço no modal de produto (criar)
                new Cleave('#product_price', {
                    numeral: true,
                    numeralThousandsGroupStyle: 'thousand',
                    numeralDecimalMark: ',',
                    delimiter: '.'
                });

                // Máscara para preço no modal de produto (editar)
                new Cleave('#edit_product_price', {
                    numeral: true,
                    numeralThousandsGroupStyle: 'thousand',
                    numeralDecimalMark: ',',
                    delimiter: '.'
                });

                // Limpar formulários quando modais são fechados
                document.getElementById('newCategoryModal').addEventListener('hidden.bs.modal', function() {
                    document.getElementById('newCategoryForm').reset();
                });

                document.getElementById('newProductModal').addEventListener('hidden.bs.modal', function() {
                    const form = document.getElementById('newProductForm');
                    form.reset();
                    const rowsContainer = document.getElementById('product-ingredient-rows');
                    if (rowsContainer) {
                        rowsContainer.innerHTML = '';
                        addIngredientRow('#product-ingredient-rows', 'product-ingredient-template');
                    }
                    document.getElementById('product_customizable_no').checked = true;
                    document.getElementById('product-ingredients-card').style.display = 'none';
                });

                document.getElementById('newTableModal').addEventListener('hidden.bs.modal', function() {
                    document.getElementById('newTableForm').reset();
                });

                // Inicializar Sortable para categorias
                const categoriesContainer = document.getElementById('sortable-categories');
                if (categoriesContainer) {
                    new Sortable(categoriesContainer, {
                        handle: '.cursor-move',
                        animation: 150,
                        onEnd: function(evt) {
                            const items = [...evt.to.children].map((div, index) => ({
                                id: div.dataset.id,
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
                }

                // Inicializar Sortable para produtos
                document.querySelectorAll('.sortable-products').forEach(function(productList) {
                    new Sortable(productList, {
                        handle: '.cursor-move',
                        animation: 150,
                        onEnd: function(evt) {
                            const items = [...evt.to.children].map((div, index) => ({
                                id: div.dataset.id,
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

                // Interceptar submissão dos formulários para mostrar feedback
                document.getElementById('newCategoryForm').addEventListener('submit', function(e) {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Criando...';
                });

                document.getElementById('newProductForm').addEventListener('submit', function(e) {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Criando...';
                });

                document.getElementById('newTableForm').addEventListener('submit', function(e) {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Criando...';
                });
            });

            // Função para abrir modal de edição de categoria
            function openEditCategoryModal(button) {
                const id = button.getAttribute('data-category-id');
                const name = button.getAttribute('data-category-name');
                const description = button.getAttribute('data-category-description');

                document.getElementById('edit_category_name').value = name;
                document.getElementById('edit_category_description').value = description || '';

                // Configurar action do formulário
                document.getElementById('editCategoryForm').action = `/store/categories/${id}`;

                // Abrir modal
                new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
            }

            // Helpers para ingredientes (novo produto)
            function formatIngredientPriceField(field) {
                if (!field) return;
                new Cleave(field, {
                    numeral: true,
                    numeralDecimalMark: ',',
                    delimiter: '.',
                    numeralDecimalScale: 2,
                    numeralThousandsGroupStyle: 'thousand',
                    numeralPositiveOnly: true
                });
            }

            function updateIndexes(rowsContainerId) {
                document.querySelectorAll(rowsContainerId + ' .ingredient-row').forEach(function(row, index) {
                    row.querySelectorAll('input').forEach(function(input) {
                        var name = input.getAttribute('name');
                        if (!name) return;
                        var updatedName = name.replace(/ingredients\[\d+\]/, 'ingredients[' + index + ']');
                        input.setAttribute('name', updatedName);
                    });
                });
            }

            function addIngredientRow(rowsContainerId, templateId, name = '', price = '', amount = 0) {
                var template = document.getElementById(templateId).innerHTML;
                var rowCount = document.querySelectorAll(rowsContainerId + ' .ingredient-row').length;
                var html = template.replace(/__INDEX__/g, rowCount);
                var container = document.createElement('div');
                container.innerHTML = html;
                var row = container.firstElementChild;
                var nameInput = row.querySelector('input[name="ingredients[' + rowCount + '][name]"]');
                var priceInput = row.querySelector('input[name="ingredients[' + rowCount + '][additional_price]"]');
                var amountInput = row.querySelector('input[name="ingredients[' + rowCount + '][amount_item]"]');
                if (nameInput) nameInput.value = name;
                if (priceInput) priceInput.value = price;
                if (amountInput) amountInput.value = amount;
                document.querySelector(rowsContainerId).appendChild(row);
                var priceField = row.querySelector('.ingredient-price');
                if (priceField) formatIngredientPriceField(priceField);
                updateIndexes(rowsContainerId);
            }

            function setupIngredientControls(config) {
                // config: { radioYesId, radioNoId, cardId, rowsId, templateId, addBtnSelector }
                var radioYes = document.getElementById(config.radioYesId);
                var radioNo = document.getElementById(config.radioNoId);
                var card = document.getElementById(config.cardId);

                function toggle() {
                    var customizable = document.querySelector('#' + config.radioYesId).checked;
                    card.style.display = customizable ? 'block' : 'none';
                    // disable inputs when hidden
                    document.querySelectorAll('#' + config.cardId + ' input').forEach(function(inp) {
                        inp.disabled = !customizable;
                    });
                    document.querySelectorAll('#' + config.cardId + ' button').forEach(function(btn) {
                        if (!btn.matches(config.addBtnSelector)) {
                            btn.disabled = !customizable;
                        }
                    });
                    if (customizable) {
                        var rowsContainer = document.getElementById(config.rowsId);
                        if (rowsContainer && rowsContainer.querySelectorAll('.ingredient-row').length === 0) {
                            addIngredientRow('#' + config.rowsId, config.templateId);
                        }
                    }
                }

                if (radioYes) radioYes.addEventListener('change', toggle);
                if (radioNo) radioNo.addEventListener('change', toggle);
                // apply initial state
                toggle();

                // Add button
                var addBtn = document.querySelector(config.addBtnSelector);
                if (addBtn) addBtn.addEventListener('click', function() {
                    addIngredientRow('#' + config.rowsId, config.templateId);
                });

                // Remove handler
                var rowsContainer = document.getElementById(config.rowsId);
                if (rowsContainer) {
                    rowsContainer.addEventListener('click', function(event) {
                        var removeButton = event.target.closest('.btn-remove-ingredient');
                        if (!removeButton) return;
                        var row = removeButton.closest('.ingredient-row');
                        if (!row) return;
                        row.remove();
                        if (document.querySelectorAll('#' + config.rowsId + ' .ingredient-row').length === 0) {
                            addIngredientRow('#' + config.rowsId, config.templateId);
                        }
                        updateIndexes('#' + config.rowsId);
                    });
                }
            }

            // Inicializa controles para New e Edit modals
            document.addEventListener('DOMContentLoaded', function() {
                setupIngredientControls({
                    radioYesId: 'product_customizable_yes',
                    radioNoId: 'product_customizable_no',
                    cardId: 'product-ingredients-card',
                    rowsId: 'product-ingredient-rows',
                    templateId: 'product-ingredient-template',
                    addBtnSelector: '.btn-add-ingredient-product'
                });

                setupIngredientControls({
                    radioYesId: 'edit_product_customizable_yes',
                    radioNoId: 'edit_product_customizable_no',
                    cardId: 'edit-ingredients-card',
                    rowsId: 'edit-ingredient-rows',
                    templateId: 'edit-ingredient-template',
                    addBtnSelector: '.btn-add-ingredient-edit'
                });

                // ensure at least one row exists when shown
                if (document.getElementById('product-ingredient-rows') && document.getElementById(
                        'product-ingredient-rows').children.length === 0) {
                    addIngredientRow('#product-ingredient-rows', 'product-ingredient-template');
                }
            });

            // Função para abrir modal de edição de produto
            function openEditProductModal(button) {
                const id = button.getAttribute('data-product-id');
                const categoryId = button.getAttribute('data-product-category-id');
                const name = button.getAttribute('data-product-name');
                const price = button.getAttribute('data-product-price');
                const description = button.getAttribute('data-product-description');
                const image = button.getAttribute('data-product-image');
                const active = button.getAttribute('data-product-active') === 'true';
                const isQuickItem = button.getAttribute('data-product-is-quick-item') === 'true';
                const customizable = button.getAttribute('data-product-customizable') === '1';
                const ingredientsJson = button.getAttribute('data-product-ingredients');

                document.getElementById('edit_product_category_id').value = categoryId;
                document.getElementById('edit_product_name').value = name;
                document.getElementById('edit_product_price').value = price;
                document.getElementById('edit_product_description').value = description || '';
                document.getElementById('edit_product_active').checked = active;
                document.getElementById('edit_product_is_quick_item').checked = isQuickItem;

                // Set customizable radios
                if (customizable) {
                    document.getElementById('edit_product_customizable_yes').checked = true;
                } else {
                    document.getElementById('edit_product_customizable_no').checked = true;
                }

                // Mostrar imagem atual se existir
                const imagePreview = document.getElementById('current_image_preview');
                const currentImage = document.getElementById('current_image');

                if (image && image !== 'null' && image !== '') {
                    currentImage.src = `/storage/${image}`;
                    imagePreview.style.display = 'block';
                } else {
                    imagePreview.style.display = 'none';
                }

                // Popular ingredientes
                var rowsContainer = document.getElementById('edit-ingredient-rows');
                if (rowsContainer) {
                    rowsContainer.innerHTML = '';
                    var ingredients = [];
                    try {
                        ingredients = ingredientsJson ? JSON.parse(ingredientsJson) : [];
                    } catch (e) {
                        ingredients = [];
                    }
                    if (customizable && ingredients.length > 0) {
                        ingredients.forEach(function(ing) {
                            addIngredientRow('#edit-ingredient-rows', 'edit-ingredient-template', ing.name || '', ing
                                .additional_price || '', ing.amount_item || 0);
                        });
                    } else if (customizable) {
                        addIngredientRow('#edit-ingredient-rows', 'edit-ingredient-template');
                    }
                }

                // Forçar toggle do card e habilitar/desabilitar inputs apropriadamente
                var editCard = document.getElementById('edit-ingredients-card');
                editCard.style.display = customizable ? 'block' : 'none';
                // habilitar/desabilitar inputs e botões dentro do card
                document.querySelectorAll('#edit-ingredients-card input').forEach(function(inp) {
                    inp.disabled = !customizable;
                });
                document.querySelectorAll('#edit-ingredients-card button').forEach(function(btn) {
                    if (!btn.classList.contains('btn-add-ingredient')) btn.disabled = !customizable;
                });

                // atualizar indexes caso tenhamos inserido linhas
                updateIndexes('#edit-ingredient-rows');

                // Configurar action do formulário
                document.getElementById('editProductForm').action = `/store/products/${id}`;

                // Abrir modal
                new bootstrap.Modal(document.getElementById('editProductModal')).show();
            }
        </script>
    @endpush
@endsection

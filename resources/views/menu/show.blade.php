@extends('layouts.app')

@section('content')
    <div class="menu-wrapper">

        <!-- Seção de Capa e Header -->
        <div class="profile-section">
            <!-- Imagem de capa do restaurante -->
            <div class="cover-image-wrapper">
                @if (isset($store) && $store->cover_image)
                    <img src="{{ asset('storage/' . $store->cover_image) }}" alt="Capa {{ $store->name }}"
                        class="cover-image">
                    <div class="cover-overlay"></div>
                @else
                    <!-- Placeholder para capa -->
                    <div class="cover-placeholder">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <div class="cover-overlay"></div>
                @endif

                @if (isset($table) || (isset($isCounter) && $isCounter))
                    <!-- Botão para visualizar pedidos da mesa ou balcão -->
                    <button type="button" class="btn position-absolute top-0 end-0 m-3 btn-orders" id="viewOrdersBtn">
                        <i class="fas fa-receipt me-1"></i> Pedido
                    </button>
                @endif
            </div>

            <!-- Header do Cardápio -->
            <div class="menu-header px-3 text-center position-relative">
                @if ($store->logo)
                    <img src="{{ asset('storage/' . $store->logo) }}" alt="{{ $store->name }}"
                        class="rounded-circle store-logo">
                @endif
                <h1 class="mb-1">{{ $store->name }}</h1>
                @if ($store->description)
                    <p class="mb-0 small text-muted">{{ $store->description }}</p>
                @endif
            </div>
        </div>

        <!-- Seção de Participantes da Mesa -->
        @if (isset($table))
            <div class="container-fluid px-3 py-2">
                <div class="participants-card">
                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                        <div class="d-flex align-items-center mb-2 mb-md-0">
                            <i class="fas fa-users me-2"></i>
                            <strong>Participantes:</strong>
                        </div>
                        <div id="participants-list" class="d-flex flex-wrap gap-2">
                            <!-- Participantes serão carregados aqui via JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Navegação das Categorias (Sticky Tabs) -->
        @if ($categories && $categories->count() > 0)
            <div class="category-nav sticky-top">
                <div class="container-fluid px-3">
                    <div class="nav-scroller">
                        <nav class="nav py-3 flex-nowrap">
                            @foreach ($categories as $category)
                                <a class="nav-link category-tab {{ $loop->first ? 'active' : '' }}"
                                    data-category="category-{{ $category->id }}">
                                    {{ $category->name }}
                                </a>
                            @endforeach
                        </nav>
                    </div>
                </div>
            </div>
        @endif

        <!-- Conteúdo do Cardápio -->
        <div class="container-fluid px-0" id="menu-content">
            @foreach ($categories as $category)
                <!-- Divisor de Categoria com Cor -->
                <div class="category-divider c-{{ $loop->iteration % 5 }}">
                    <h2 class="mb-0">
                        {{ $category->name }}
                    </h2>
                </div>

                <!-- Lista de Produtos da Categoria -->
                <div id="category-{{ $category->id }}" class="menu-category">
                    @foreach ($category->products as $product)
                        <div class="product-item" data-product-id="{{ $product->id }}"
                            data-product-name="{{ $product->name }}" data-product-price="{{ $product->price }}"
                            data-product-image="{{ $product->image ? asset('storage/' . $product->image) : '' }}">

                            <!-- Coluna 1: Imagem (Esquerda) -->
                            <div class="product-image">
                                @if ($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                                @else
                                    <div class="product-img-placeholder">
                                        <i class="fas fa-utensils"></i>
                                    </div>
                                @endif
                            </div>

                            <!-- Coluna 2: Informações do Produto (Centro) -->
                            <div class="product-info">
                                <h3 class="product-name">
                                    {{ $product->name }}
                                </h3>

                                @if ($product->description)
                                    <p class="product-description">
                                        {{ $product->description }}
                                    </p>
                                @endif

                                @php
                                    $ingredientText = '';
                                    if (!empty($product->additionalIngredients)) {
                                        $ingredientText = collect($product->additionalIngredients)
                                            ->pluck('name')
                                            ->filter()
                                            ->implode(', ');
                                    }
                                    if (!$ingredientText) {
                                        $ingredientText = $product->ingredients;
                                        $decodedIngredients = json_decode($product->ingredients, true);
                                        if (is_array($decodedIngredients)) {
                                            $ingredientText = collect($decodedIngredients)
                                                ->pluck('name')
                                                ->filter()
                                                ->implode(', ');
                                        }
                                    }
                                @endphp

                                @if ($ingredientText)
                                    <p class="product-ingredients">
                                        {{ Str::limit($ingredientText, 60) }}
                                    </p>
                                @endif

                                <div class="product-price">
                                    R$ {{ number_format($product->price, 2, ',', '.') }}
                                </div>
                            </div>

                            <!-- Coluna 3: Controles e Botão (Direita) -->
                            <div class="product-controls">
                                <!-- Controles de Quantidade (Compacto) -->
                                <div class="product-quick-add" data-product-id="{{ $product->id }}">
                                    <button type="button" class="btn btn-sm quick-decr">−</button>
                                    <span class="quick-qty">0</span>
                                    <button type="button" class="btn btn-sm quick-incr">+</button>
                                </div>

                                <!-- Botão "Personalização" (Se customizável) -->
                                @if ($product->customizable)
                                    <button type="button" class="btn btn-sm product-detail-btn"
                                        data-product-id="{{ $product->id }}">
                                        Personalização
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach

            <!-- Espaçamento no final para não ficar atrás do carrinho flutuante -->
            <div class="bottom-spacer"></div>
        </div>
    </div>

    <!-- Painel Lateral de Detalhes do Produto (Slide da Direita) -->
    <div id="product-detail-panel" class="product-detail-panel">
        <div class="panel-content">
            <!-- Header do Painel -->
            <div class="panel-header">
                <button type="button" id="close-panel" class="btn">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <h2 class="mb-0">
                    Detalhes do Produto</h2>
                <div class="panel-spacer"></div>
            </div>

            <!-- Imagem Grande do Produto -->
            <div id="panel-image-container" class="panel-image">
                <!-- Imagem será inserida aqui -->
            </div>

            <!-- Corpo do Painel -->
            <div class="panel-body">
                <h2 id="panel-product-name" class="mb-2"></h2>

                <div id="panel-product-description" class="mb-3"></div>

                <div id="panel-product-ingredient-customization" class="mb-3"></div>

                <div class="price-section mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Preço</span>
                        <span id="panel-product-price"></span>
                    </div>
                </div>

                <!-- Seletor de Quantidade -->
                <div class="quantity-section mb-4">
                    <label class="form-label">Quantidade</label>
                    <div class="d-flex align-items-center gap-3">
                        <button type="button" id="decrease-quantity" class="btn circle-btn">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" id="product-quantity" value="1" min="1"
                            class="form-control text-center product-qty-input">
                        <button type="button" id="increase-quantity" class="btn circle-btn">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>

                <!-- Observações Automáticas -->
                <div class="notes-section mb-4">
                    <label for="product-notes" class="form-label">Observações</label>
                    <textarea id="product-notes" class="form-control" rows="3"
                        placeholder="As observações serão preenchidas automaticamente..." class="notes-section" readonly></textarea>
                </div>
            </div>

            <!-- Footer Fixo com Botão Adicionar -->
            <div class="panel-footer">
                <button type="button" id="add-to-cart-btn" class="btn w-100 btn-add-cart">
                    <i class="fas fa-shopping-cart me-2"></i>
                    <span id="add-to-cart-text">Adicionar ao Pedido</span>
                    <span id="add-to-cart-total" class="ms-2"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Overlay Escuro -->
    <!-- Modal de Detalhes do Produto -->
    <div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-right">
            <div class="modal-content modal-content-right">
                <div class="modal-header modal-header-right">
                    <div>
                        <h5 id="modal-product-name" class="modal-title"></h5>
                        <div id="modal-product-description" class="small text-muted mt-2"></div>
                        <div class="small text-muted mt-2">Preço unitário: <span id="modal-unit-price">R$ 0,00</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body modal-body-right p-0 d-flex flex-column">
                    <div class="overflow-auto">
                        <div id="modal-product-image" class="modal-product-image"></div>
                        <div id="modal-product-ingredients" class="mb-3"></div>
                        <div id="modal-ingredient-customization" class="mb-3"></div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="fw-semibold">Quantidade</div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" id="modal-decrease-quantity"
                                    class="btn btn-outline-secondary btn-sm">-</button>
                                <input type="number" id="modal-product-quantity" value="1" min="1"
                                    class="modal-qty-input" />
                                <button type="button" id="modal-increase-quantity"
                                    class="btn btn-outline-secondary btn-sm">+</button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Observações</label>
                            <textarea id="modal-product-notes" class="form-control modal-product-notes" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer modal-footer-right d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-muted">Total</div>
                            <div id="modal-total-price">R$ 0,00</div>
                        </div>
                        <button type="button" id="modal-add-to-cart-btn"
                            class="btn btn-primary modal-add-btn">Adicionar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <div id="panel-overlay"></div>

    <!-- Resumo Flutuante do Carrinho (Mobile) -->
    <div id="floating-cart-summary" class="floating-cart-summary">
        <div class="floating-cart-header" id="floating-cart-toggle">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-shopping-cart"></i>
                <span id="floating-cart-count" class="fw-bold">0 itens</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span id="floating-cart-total" class="fw-bold fs-5">R$ 0,00</span>
                <i class="fas fa-chevron-up" id="floating-cart-icon"></i>
            </div>
        </div>
        <div class="floating-cart-content" id="floating-cart-content">
            <div class="floating-cart-items p-3" id="floating-cart-items">
                <!-- Itens do carrinho serão exibidos aqui -->
            </div>
            <div class="floating-cart-footer">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 cart-footer-row">
                    <span>
                        <i class="fas fa-calculator me-2"></i>Total
                    </span>
                    <span id="floating-cart-total-footer" class="fw-bold">R$ 0,00</span>
                </div>
                <div class="mb-3">
                    <label for="floating-order-notes" class="form-label">
                        <i class="fas fa-comment-dots me-1"></i>
                        Observações do Pedido
                    </label>
                    <textarea id="floating-order-notes" class="form-control floating-order-notes" rows="2"
                        placeholder="Alguma observação? (opcional)"></textarea>
                </div>
                <button id="floating-checkout-btn" class="btn w-100 btn-checkout" disabled>
                    <i class="fas fa-check me-2"></i> Finalizar Pedido
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de Autenticação da Mesa -->
    @if (isset($table))
        <div class="modal fade" id="authModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content orders-modal">
                    <div class="modal-header orders-modal">
                        <h5 class="modal-title" id="authModalTitle">
                            <i class="fas fa-lock me-2"></i>
                            Acesso à Mesa
                        </h5>
                    </div>
                    <div class="modal-body">
                        <!-- Etapa 1: Criar senha (primeiro usuário) -->
                        <div id="createPasswordStep" class="d-none">
                            <p class="text-center mb-4">Olá, Seja Bem-vindo!</p>
                            <form id="createPasswordForm">
                                <div class="mb-3">
                                    <label for="ownerName" class="form-label">Digite seu nome para continuarmos</label>
                                    <input type="text" class="form-control" id="ownerName"
                                        placeholder="Digite seu nome" required>
                                </div>
                                <button type="submit" class="btn-auth">
                                    <i class="fas fa-check me-2"></i>Entrar
                                </button>
                            </form>
                        </div>

                        <!-- Etapa 2: Validar senha (demais usuários) -->
                        <div id="validatePasswordStep" class="d-none">
                            <p class="text-center mb-4">Digite a senha da mesa para continuar.</p>
                            <form id="validatePasswordForm">
                                <div class="mb-3">
                                    <label for="tablePassword" class="form-label">Senha da Mesa</label>
                                    <input type="text" class="form-control table-password-input" id="tablePassword"
                                        maxlength="4" pattern="[0-9]{4}" placeholder="0000" required>
                                </div>
                                <button type="submit" class="btn-auth">
                                    <i class="fas fa-unlock me-2"></i>Validar Senha
                                </button>
                            </form>
                            <div id="passwordError" class="alert alert-danger mt-3 d-none"></div>
                        </div>

                        <!-- Etapa 3: Digitar nome (após validar senha) -->
                        <div id="enterNameStep" class="d-none">
                            <p class="text-center mb-4">Senha validada! Agora digite seu nome para entrar na mesa.</p>
                            <form id="enterNameForm">
                                <div class="mb-3">
                                    <label for="participantName" class="form-label">Seu Nome</label>
                                    <input type="text" class="form-control" id="participantName"
                                        placeholder="Digite seu nome" required>
                                </div>
                                <button type="submit" class="btn-auth">
                                    <i class="fas fa-sign-in-alt me-2"></i>Entrar na Mesa
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de Pedidos da Mesa ou Balcão -->
    @if (isset($table) || (isset($isCounter) && $isCounter))
        <div class="modal fade" id="ordersModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-scrollable modal-lg">
                <div class="modal-content orders-modal">
                    <div class="modal-header orders-modal">
                        <h5 class="modal-title">
                            <i class="fas fa-receipt me-2"></i>
                            @if (isset($table))
                                Pedidos da Mesa {{ $table->number }}
                            @else
                                Meus Pedidos
                            @endif
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body orders-modal-body" id="ordersModalBody">
                        <!-- Pedidos serão carregados aqui -->
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        /* Estilos para as tabs de categoria */
        .category-tab {
            transition: all 0.3s ease;
        }

        .category-tab.active {
            background: #000 !important;
            color: white !important;
        }

        .category-tab:hover:not(.active) {
            background: #f0f0f0;
        }

        /* Efeito hover nos produtos */
        .product-item:hover {
            background: #f8f9fa !important;
        }

        .product-item:active {
            background: #f0f0f0 !important;
        }

        /* Hover effect na parte esquerda do produto */
        .product-left:hover {
            opacity: 0.85;
        }

        /* Scrollbar personalizada */
        .nav-scroller::-webkit-scrollbar {
            height: 4px;
        }

        .nav-scroller::-webkit-scrollbar-track {
            background: #f0f0f0;
        }

        .nav-scroller::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 4px;
        }

        /* Animação de entrada do painel */
        .product-detail-panel.active {
            transform: translateX(0) !important;
        }

        #panel-overlay.active {
            opacity: 1 !important;
            pointer-events: auto !important;
        }

        /* Estilo dos itens do carrinho */
        .cart-item {
            padding: 0.75rem 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        /* Botões de quantidade */
        #decrease-quantity:hover,
        #increase-quantity:hover {
            background: #e0e0e0 !important;
        }

        #decrease-quantity:active,
        #increase-quantity:active {
            transform: scale(0.95);
        }

        /* Botão adicionar ao carrinho */
        #add-to-cart-btn:hover {
            background: #333 !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        #add-to-cart-btn:active {
            transform: translateY(0);
        }

        /* Animação de loading */
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .spinner {
            animation: spin 1s linear infinite;
        }

        /* Badge de participante */
        .participant-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.75rem;
            background: #f0f0f0;
            color: #000;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        /* Badge do owner (dono da mesa) */
        .participant-badge.owner-badge {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            color: #000;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(255, 215, 0, 0.3);
        }

        .participant-badge.owner-badge i {
            color: #d4af37;
        }

        /* Estilos do modal de autenticação */
        #authModal .form-control:focus {
            border-color: #000;
            box-shadow: 0 0 0 0.2rem rgba(0, 0, 0, 0.1);
        }

        #authModal .btn:hover {
            background: #333 !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        #authModal .btn:active {
            transform: translateY(0);
        }

        /* Responsividade */
        @media (min-width: 768px) {
            .product-detail-panel {
                width: 450px !important;
                box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
            }
        }

        /* Scrollbar para o painel */
        .product-detail-panel::-webkit-scrollbar {
            width: 6px;
        }

        .product-detail-panel::-webkit-scrollbar-track {
            background: #f0f0f0;
        }

        .product-detail-panel::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 3px;
        }

        /* Remover setas do input number */
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type="number"] {
            -moz-appearance: textfield;
        }

        .product-item {
            cursor: default;
        }

        .product-card-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .product-card-actions .card-quantity {
            min-width: 34px;
            text-align: center;
            font-weight: 700;
            color: #000;
            font-size: 0.95rem;
        }

        .product-card-actions .btn.card-action {
            width: 34px;
            height: 34px;
            padding: 0;
            border: none;
            border-radius: 50%;
            background: #f0f0f0;
            color: #333;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .product-card-actions .btn.card-remove-btn {
            width: 34px;
            height: 34px;
            background: transparent;
            color: #ef4444;
        }

        .product-item .detail-button {
            color: #111;
            background: #f3f4f6;
            border: none;
            border-radius: 999px;
            padding: 0.35rem 0.85rem;
            font-size: 0.82rem;
            font-weight: 600;
        }

        .product-item .detail-button:hover {
            background: #e5e7eb;
        }

        /* Estilos para Lista do Menu */
        .product-item {
            position: relative;
            transition: background 0.2s ease;
        }

        .product-item:hover {
            background: #f8f9fa !important;
        }

        .product-item:active {
            background: #f0f0f0 !important;
        }

        /* Hover effect na parte esquerda do produto */
        .product-left:hover {
            opacity: 0.85;
        }

        /* Estilos dos botões */
        .product-quick-add button {
            transition: all 0.2s ease;
        }

        .product-quick-add button:hover {
            background-color: #d0d0d0 !important;
        }

        .product-quick-add button:active {
            transform: scale(0.95);
        }

        .product-detail-btn {
            transition: all 0.2s ease !important;
        }

        .product-detail-btn:hover {
            background-color: #e0e0e0 !important;
            border-color: #d0d0d0 !important;
            transform: translateY(-1px);
        }

        /* Scrollbar personalizada */
        .nav-scroller::-webkit-scrollbar {
            height: 4px;
        }

        .nav-scroller::-webkit-scrollbar-track {
            background: #f0f0f0;
        }

        .nav-scroller::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 4px;
        }

        /* Responsividade para Mobile */
        @media (max-width: 576px) {
            .product-item {
                padding: 1rem !important;
                grid-template-columns: 70px 1fr auto !important;
                gap: 0.75rem !important;
            }

            .product-image img {
                width: 70px !important;
                height: 70px !important;
            }

            .product-image div {
                width: 70px !important;
                height: 70px !important;
            }

            .product-name {
                font-size: 0.95rem !important;
            }

            .product-description {
                font-size: 0.8rem !important;
            }

            .product-price {
                font-size: 0.95rem !important;
            }

            .product-controls {
                gap: 0.5rem !important;
            }

            .product-quick-add {
                gap: 0.2rem !important;
                padding: 0.2rem !important;
            }

            .product-quick-add button {
                width: 26px !important;
                height: 26px !important;
                font-size: 0.85rem !important;
            }

            .quick-qty {
                width: 35px !important;
                font-size: 0.85rem !important;
            }

            .product-detail-btn {
                padding: 0.35rem 0.6rem !important;
                font-size: 0.8rem !important;
            }

            .category-divider {
                padding: 0.6rem 1rem !important;
            }

            .category-divider h2 {
                font-size: 0.95rem !important;
            }
        }

        /* Responsividade para Tablet */
        @media (min-width: 577px) and (max-width: 768px) {
            .product-item {
                padding: 1.1rem !important;
                grid-template-columns: 75px 1fr auto !important;
                gap: 1rem !important;
            }

            .product-image img {
                width: 75px !important;
                height: 75px !important;
            }

            .product-image div {
                width: 75px !important;
                height: 75px !important;
            }

            .product-quick-add button {
                width: 27px !important;
                height: 27px !important;
            }

            .quick-qty {
                width: 38px !important;
            }
        }

        /* Responsividade para Desktop */
        @media (min-width: 769px) {
            .product-item {
                padding: 1.25rem !important;
                grid-template-columns: 80px 1fr auto !important;
                gap: 1.25rem !important;
            }
        }

        /* Ajustes gerais de responsividade */
        @media (max-width: 992px) {
            .menu-wrapper {
                min-height: 100vh;
            }

            .product-controls {
                min-width: auto;
            }
        }

        /* Ensure grid layout works on all devices */
        .product-item {
            display: grid !important;
        }

        .product-info {
            overflow: hidden;
            word-break: break-word;
        }

        .product-quick-add button:active {
            transform: scale(0.95);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Configurações
            const storeId = {{ $store->id }};
            const tableId =
                @if (isset($table))
                    {{ $table->id }}
                @else
                    null
                @endif ;
            const qrCode =
                @if (isset($table))
                    '{{ $table->qr_code }}'
                @elseif (isset($isCounter) && $isCounter)
                    '{{ session('counter_qr_code') }}'
                @else
                    null
                @endif ;
            const isCounter =
                @if (isset($isCounter) && $isCounter)
                    true
                @else
                    false
                @endif ;

            // Carrinho
            let cart = [];
            let currentProduct = null;

            // Verificar autenticação da mesa
            @if (isset($table))
                setTimeout(checkTableAuthentication, 120);
            @endif

            function showAuthModal() {
                const authModalEl = document.getElementById('authModal');
                if (!authModalEl) return;

                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const authModal = bootstrap.Modal.getOrCreateInstance(authModalEl, {
                        backdrop: 'static',
                        keyboard: false
                    });
                    authModal.show();
                } else {
                    authModalEl.classList.add('show');
                    authModalEl.style.display = 'block';
                    document.body.classList.add('modal-open');

                    if (!document.querySelector('.modal-backdrop')) {
                        const backdrop = document.createElement('div');
                        backdrop.className = 'modal-backdrop fade show';
                        document.body.appendChild(backdrop);
                    }
                }
            }

            function checkTableAuthentication() {
                fetch(`/api/table/${qrCode}/status`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.is_authenticated) {
                            const authModalEl = document.getElementById('authModal');
                            const createStep = document.getElementById('createPasswordStep');
                            const validateStep = document.getElementById('validatePasswordStep');
                            const enterNameStep = document.getElementById('enterNameStep');

                            if (createStep) createStep.style.display = 'none';
                            if (validateStep) validateStep.style.display = 'none';
                            if (enterNameStep) enterNameStep.style.display = 'none';

                            if (!data.has_password && !data.has_participants) {
                                // Primeira pessoa - criar senha
                                if (authModalEl) {
                                    showAuthModal();
                                    setTimeout(() => {
                                        if (!authModalEl.classList.contains('show')) {
                                            showAuthModal();
                                        }
                                    }, 150);
                                }
                                createStep.style.display = 'block';
                            } else if (!data.has_password && data.has_participants) {
                                // Mesa ocupada sem senha - liberar acesso direto ao cardápio
                                if (data.participant_name) {
                                    showToast(
                                        `${data.participant_name} já está na mesa. Acesso liberado ao cardápio.`,
                                        'success');
                                }
                            } else if (data.has_password) {
                                // Demais pessoas - validar senha
                                if (authModalEl) {
                                    showAuthModal();
                                    setTimeout(() => {
                                        if (!authModalEl.classList.contains('show')) {
                                            showAuthModal();
                                        }
                                    }, 150);
                                }
                                validateStep.style.display = 'block';
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao verificar autenticação:', error);
                    });
            }

            // Form: Criar senha (primeiro usuário)
            document.getElementById('createPasswordForm')?.addEventListener('submit', function(e) {
                e.preventDefault();

                const name = document.getElementById('ownerName').value;
                const submitBtn = this.querySelector('button[type="submit"]');

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Criando...';

                fetch('/api/table/create-password', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            qr_code: qrCode,
                            name: name
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('Bem-vindo à mesa!', 'success');
                            // Fechar modal e recarregar página
                            bootstrap.Modal.getInstance(document.getElementById('authModal')).hide();
                            setTimeout(() => location.reload(), 500);
                        } else {
                            showToast(data.message || 'Erro ao entrar', 'error');
                            submitBtn.disabled = false;
                            submitBtn.innerHTML =
                                '<i class="fas fa-check me-2"></i>Entrar';
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        showToast('Erro ao entrar na mesa', 'error');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-check me-2"></i>Entrar';
                    });
            });

            // Form: Validar senha
            document.getElementById('validatePasswordForm')?.addEventListener('submit', function(e) {
                e.preventDefault();

                const password = document.getElementById('tablePassword').value;
                const submitBtn = this.querySelector('button[type="submit"]');
                const errorDiv = document.getElementById('passwordError');

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Validando...';
                errorDiv.style.display = 'none';

                fetch('/api/table/validate-password', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            qr_code: qrCode,
                            password: password
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Senha validada, mostrar etapa de digitar nome
                            document.getElementById('validatePasswordStep').style.display = 'none';
                            document.getElementById('enterNameStep').style.display = 'block';
                        } else {
                            errorDiv.textContent = data.message || 'Senha incorreta';
                            errorDiv.style.display = 'block';
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = '<i class="fas fa-unlock me-2"></i>Validar Senha';
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        errorDiv.textContent = error;
                        errorDiv.style.display = 'block';
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-unlock me-2"></i>Validar Senha';
                    });
            });

            // Form: Digitar nome (após validar senha)
            document.getElementById('enterNameForm')?.addEventListener('submit', function(e) {
                e.preventDefault();

                const name = document.getElementById('participantName').value;
                const submitBtn = this.querySelector('button[type="submit"]');

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Entrando...';

                fetch('/api/table/add-participant', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            qr_code: qrCode,
                            name: name
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('Bem-vindo à mesa!', 'success');
                            // Fechar modal e recarregar página
                            bootstrap.Modal.getInstance(document.getElementById('authModal')).hide();
                            setTimeout(() => location.reload(), 500);
                        } else {
                            showToast(data.message || 'Erro ao entrar na mesa', 'error');
                            submitBtn.disabled = false;
                            submitBtn.innerHTML =
                                '<i class="fas fa-sign-in-alt me-2"></i>Entrar na Mesa';
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        showToast('Erro ao entrar na mesa', 'error');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i>Entrar na Mesa';
                    });
            });

            document.getElementById('tablePassword')?.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
            });

            // Elementos do DOM
            const menuContent = document.getElementById('menu-content');
            const detailPanel = document.getElementById('product-detail-panel');
            const panelOverlay = document.getElementById('panel-overlay');
            const closePanel = document.getElementById('close-panel');
            const floatingCart = document.getElementById('floating-cart-summary');
            const floatingCartToggle = document.getElementById('floating-cart-toggle');
            const floatingCartContent = document.getElementById('floating-cart-content');
            const floatingCartIcon = document.getElementById('floating-cart-icon');

            // Event Listeners para abrir detalhes do produto (apenas clicando na esquerda)
            const productLefts = document.querySelectorAll('.product-left');
            productLefts.forEach(left => {
                left.addEventListener('click', function() {
                    const productItem = this.closest('.product-item');
                    const productId = productItem.dataset.productId;
                    openProductDetail(productId);
                });
            });

            // Botões de detalhe "Saiba mais"
            const productDetailButtons = document.querySelectorAll('.product-detail-btn');
            productDetailButtons.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    openProductDetail(this.dataset.productId);
                });
            });

            // Fechar painel
            closePanel?.addEventListener('click', closeProductDetail);
            panelOverlay?.addEventListener('click', closeProductDetail);

            // Toggle do carrinho flutuante
            floatingCartToggle?.addEventListener('click', function() {
                const isOpen = floatingCartContent.style.display !== 'none';
                floatingCartContent.style.display = isOpen ? 'none' : 'block';
                floatingCartIcon.className = isOpen ? 'fas fa-chevron-up' : 'fas fa-chevron-down';
            });

            // Quick add controls
            const quickAddPanels = document.querySelectorAll('.product-quick-add');
            quickAddPanels.forEach(panel => {
                const productId = panel.dataset.productId;
                const qtyDisplay = panel.querySelector('.quick-qty');
                const decrBtn = panel.querySelector('.quick-decr');
                const incrBtn = panel.querySelector('.quick-incr');
                const removeBtn = panel.closest('.product-controls').querySelector('.quick-remove');

                function getPanelQuantity() {
                    return parseInt(panel.dataset.quantity || qtyDisplay.textContent) || 0;
                }

                function setPanelQuantity(value) {
                    panel.dataset.quantity = value;
                    qtyDisplay.textContent = value;
                    if (removeBtn) {
                        removeBtn.style.display = value > 0 ? 'block' : 'none';
                    }
                }

                setPanelQuantity(0);

                decrBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    let quantity = getPanelQuantity();
                    if (quantity > 0) {
                        quantity--;
                        setPanelQuantity(quantity);

                        // Remover 1 item do carrinho
                        const existingItemIndex = cart.findIndex(item => String(item.id) === String(
                            productId));
                        if (existingItemIndex > -1) {
                            if (cart[existingItemIndex].quantity > 1) {
                                cart[existingItemIndex].quantity--;
                            } else {
                                cart.splice(existingItemIndex, 1);
                            }
                            updateCart();
                        }
                    }
                });

                incrBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    let quantity = getPanelQuantity() + 1;
                    setPanelQuantity(quantity);

                    const productElement = panel.closest('.product-item');
                    const name = productElement.dataset.productName || '';
                    const price = parseFloat(productElement.dataset.productPrice) || 0;
                    const image = productElement.dataset.productImage || null;

                    addToCartQuick({
                        id: productId,
                        name: name,
                        price: price,
                        image: image,
                        quantity: 1
                    });
                });

                removeBtn?.addEventListener('click', function(e) {
                    e.stopPropagation();
                    // Remover todos os itens desse produto do carrinho
                    cart = cart.filter(item => String(item.id) !== String(productId));
                    updateCart();
                    setPanelQuantity(0);
                    showToast('Item removido do carrinho');
                });
            });

            // Navegação das categorias
            const categoryTabs = document.querySelectorAll('.category-tab');
            const categories = document.querySelectorAll('.menu-category');

            categoryTabs.forEach(tab => {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    const categoryId = this.dataset.category;
                    const targetCategory = document.getElementById(categoryId);

                    // Atualizar tab ativa
                    categoryTabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');

                    // Scroll suave até a categoria
                    if (targetCategory) {
                        const categoryDivider = targetCategory.previousElementSibling;
                        const offset = 60; // altura do header sticky
                        const elementPosition = categoryDivider.getBoundingClientRect().top;
                        const offsetPosition = elementPosition + window.pageYOffset - offset;

                        window.scrollTo({
                            top: offsetPosition,
                            behavior: 'smooth'
                        });
                    }
                });
            });

            // Highlight da categoria ao fazer scroll
            const observerOptions = {
                root: null,
                rootMargin: '-80px 0px -70% 0px',
                threshold: 0
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const categoryId = entry.target.id;
                        categoryTabs.forEach(tab => {
                            if (tab.dataset.category === categoryId) {
                                categoryTabs.forEach(t => t.classList.remove('active'));
                                tab.classList.add('active');

                                // Scroll horizontal da tab se necessário
                                tab.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'nearest',
                                    inline: 'center'
                                });
                            }
                        });
                    }
                });
            }, observerOptions);

            categories.forEach(category => {
                observer.observe(category);
            });

            // Botão de checkout
            document.getElementById('floating-checkout-btn')?.addEventListener('click', function() {
                if (cart.length === 0) return;
                checkout();
            });

            // Botão de adicionar ao carrinho no painel lateral
            document.getElementById('add-to-cart-btn')?.addEventListener('click', function() {
                if (!currentProduct) return;
                const quantity = parseInt(quantityInput.value) || 1;
                const notes = document.getElementById('product-notes').value || '';

                const selectedIngredients = currentProduct.selectedIngredients || [];
                const ingredientChanges = selectedIngredients.map(ingredient => {
                    const baseAmount = parseInt(ingredient.amount_item) || 0;
                    const selectedAmount = parseInt(ingredient.selectedAmount) || 0;
                    return {
                        id: ingredient.id,
                        name: ingredient.name,
                        baseAmount: baseAmount,
                        selectedAmount: selectedAmount,
                        diff: selectedAmount - baseAmount,
                        additional_price: parseFloat(ingredient.additional_price) || 0
                    };
                });
                const addedIngredients = ingredientChanges.filter(i => i.diff > 0);
                const removedIngredients = ingredientChanges.filter(i => i.diff < 0);
                const unitPrice = calculateProductUnitPrice();
                const summary = generateObservationText(addedIngredients, removedIngredients);
                const finalNotes = summary ? (notes ? notes + ' | ' + summary : summary) : notes;

                addToCart({
                    id: currentProduct.id,
                    name: currentProduct.name,
                    price: unitPrice,
                    image: currentProduct.image,
                    quantity: quantity,
                    notes: finalNotes,
                    selectedIngredients: selectedIngredients,
                    addedIngredients: addedIngredients,
                    removedIngredients: removedIngredients
                });

                closeProductDetail();
            });

            function normalizeProductIngredients(product) {
                let ingredients = product.additional_ingredients ?? product.additionalIngredients;
                const hasAdditional = Array.isArray(ingredients) ? ingredients.length > 0 : !!ingredients;
                if (!hasAdditional) {
                    ingredients = product.ingredients;
                }

                if (typeof ingredients === 'string') {
                    try {
                        const parsed = JSON.parse(ingredients);
                        ingredients = Array.isArray(parsed) ? parsed : [parsed];
                    } catch (error) {
                        const names = ingredients.split(',').map(name => name.trim()).filter(Boolean);
                        ingredients = names.map(name => ({
                            name,
                            amount_item: 0,
                            additional_price: 0
                        }));
                    }
                }

                if (!Array.isArray(ingredients)) {
                    if (ingredients && typeof ingredients === 'object') {
                        ingredients = [ingredients];
                    } else {
                        ingredients = [];
                    }
                }

                product.selectedIngredients = ingredients.map((ing, index) => ({
                    id: ing.id ?? ing.name ?? index,
                    name: typeof ing === 'string' ? ing : (ing.name ?? String(ing)),
                    amount_item: parseInt(ing.amount_item) || 0,
                    selectedAmount: parseInt(ing.selectedAmount ?? ing.amount_item) || parseInt(ing
                        .amount_item) || 0,
                    additional_price: parseFloat(ing.additional_price || 0) || 0
                }));
            }

            function showProductDetailPanel() {
                detailPanel.classList.add('active');
                panelOverlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            }

            function openProductDetail(productId) {
                fetch(`/api/products/${productId}`)
                    .then(response => response.json())
                    .then(product => {
                        currentProduct = product;
                        normalizeProductIngredients(currentProduct);

                        document.getElementById('panel-product-name').textContent = product.name;
                        document.getElementById('panel-product-description').textContent = product
                            .description ||
                            'Não há descrição para este produto.';
                        document.getElementById('panel-product-price').textContent =
                            `R$ ${parseFloat(product.price).toFixed(2).replace('.', ',')}`;

                        const panelImage = document.getElementById('panel-image-container');
                        panelImage.innerHTML = product.image ?
                            `<img src="/storage/${product.image}" alt="${product.name}" class="panel-image-img">` :
                            `<div class="panel-image-placeholder"><i class="fas fa-utensils"></i></div>`;

                        renderIngredientCustomization('panel-product-ingredient-customization');

                        document.getElementById('product-quantity').value = 1;
                        document.getElementById('product-notes').value = '';
                        updateAddToCartButton();
                        updatePanelPriceDisplay();

                        showProductDetailPanel();
                    })
                    .catch(error => {
                        console.error('Erro ao carregar produto:', error);
                        showToast('Erro ao carregar detalhes do produto', 'error');
                    });
            }

            // Renderiza lista simples de ingredientes no modal ou no painel lateral
            function renderIngredientSummary(containerId = 'modal-product-ingredients') {
                const el = document.getElementById(containerId);
                if (!el) return;
                if (!currentProduct) {
                    el.innerHTML = '';
                    return;
                }
                const ings = currentProduct.selectedIngredients || [];
                if (!ings.length) {
                    el.innerHTML = '<div class="small text-muted">Sem ingredientes.</div>';
                    return;
                }

                const html = ings.map(ing => {
                    const qty = parseInt(ing.selectedAmount) || 0;
                    const baseQty = parseInt(ing.amount_item) || 0;
                    const price = parseFloat(ing.additional_price || 0).toFixed(2).replace('.', ',');
                    return `
                        <div class="ingredient-row">
                            <div class="min-w-0">
                                <div class="ingredient-name">${ing.name}</div>
                                <div class="small text-muted">Padrão: ${baseQty} • +R$ ${price} cada</div>
                            </div>
                            <div class="text-end">
                                <div class="small text-muted">Qtd</div>
                                <div class="fw-bold">${qty}</div>
                            </div>
                        </div>`;
                }).join('');

                el.innerHTML = html || '';
            }

            function renderIngredientCustomization(containerId = 'modal-ingredient-customization') {
                const container = document.getElementById(containerId);
                if (!container) return;
                if (!currentProduct || !currentProduct.selectedIngredients || !currentProduct.selectedIngredients
                    .length) {
                    container.innerHTML = '<p class="small text-muted">Sem ingredientes configuráveis.</p>';
                    return;
                }

                const rows = currentProduct.selectedIngredients.map(ing => {
                    const selected = parseInt(ing.selectedAmount) || 0;
                    const base = parseInt(ing.amount_item) || 0;
                    return `
                        <div class="d-flex align-items-center justify-content-between mb-2" data-ing-id="${ing.id}">
                            <div class="min-w-0">
                                <div class="ingredient-name">${ing.name}</div>
                                <div class="small text-muted">Padrão: ${base} • +R$ ${parseFloat(ing.additional_price || 0).toFixed(2).replace('.', ',')} cada</div>
                            </div>
                            <div class="ingredient-controls">
                                <button type="button" class="btn btn-sm ingredient-decr" data-id="${ing.id}">−</button>
                                <input type="number" min="0" value="${selected}" class="form-control form-control-sm ingredient-qty-input" data-id="${ing.id}">
                                <button type="button" class="btn btn-sm ingredient-incr" data-id="${ing.id}">+</button>
                            </div>
                        </div>
                    `;
                }).join('');

                container.innerHTML = rows;
                attachIngredientControlListeners();
            }

            function attachIngredientControlListeners() {
                document.querySelectorAll('.ingredient-decr').forEach(btn => {
                    btn.onclick = function() {
                        const id = this.dataset.id;
                        const input = document.querySelector('.ingredient-qty-input[data-id="' + id +
                            '"]');
                        let val = parseInt(input.value) || 0;
                        if (val > 0) input.value = val - 1;
                        updateIngredientFromInput(id);
                    };
                });
                document.querySelectorAll('.ingredient-incr').forEach(btn => {
                    btn.onclick = function() {
                        const id = this.dataset.id;
                        const input = document.querySelector('.ingredient-qty-input[data-id="' + id +
                            '"]');
                        input.value = (parseInt(input.value) || 0) + 1;
                        updateIngredientFromInput(id);
                    };
                });
                document.querySelectorAll('.ingredient-qty-input').forEach(inp => {
                    inp.onchange = function() {
                        updateIngredientFromInput(this.dataset.id);
                    };
                });
            }

            function updateIngredientFromInput(id) {
                const input = document.querySelector('.ingredient-qty-input[data-id="' + id + '"]');
                const val = parseInt(input.value) || 0;
                const ing = (currentProduct.selectedIngredients || []).find(i => String(i.id) === String(id));
                if (ing) {
                    ing.selectedAmount = val;
                }

                // update observation summary and totals
                const added = (currentProduct.selectedIngredients || []).filter(i => (parseInt(i.selectedAmount) ||
                    0) > (parseInt(i.amount_item) || 0)).map(i => ({
                    name: i.name,
                    diff: (parseInt(i.selectedAmount) || 0) - (parseInt(i.amount_item) || 0)
                }));
                const removed = (currentProduct.selectedIngredients || []).filter(i => (parseInt(i
                    .selectedAmount) || 0) < (parseInt(i.amount_item) || 0)).map(i => ({
                    name: i.name,
                    diff: (parseInt(i.amount_item) || 0) - (parseInt(i.selectedAmount) || 0)
                }));
                document.getElementById('product-notes').value = generateObservationText(added, removed);
                updatePanelPriceDisplay();
                updateModalTotals();
            }

            function calculateProductUnitPrice() {
                if (!currentProduct) return 0;
                const basePrice = parseFloat(currentProduct.price) || 0;
                let extra = 0;
                (currentProduct.selectedIngredients || []).forEach(ing => {
                    const base = parseInt(ing.amount_item) || 0;
                    const selected = parseInt(ing.selectedAmount) || 0;
                    const diff = selected - base;
                    if (diff > 0) {
                        extra += diff * (parseFloat(ing.additional_price) || 0);
                    }
                });
                return +(basePrice + extra).toFixed(2);
            }

            function updateModalTotals() {
                const qty = parseInt(document.getElementById('modal-product-quantity').value) || 1;
                const unit = calculateProductUnitPrice();
                document.getElementById('modal-unit-price').textContent = `R$ ${unit.toFixed(2).replace('.', ',')}`;
                document.getElementById('modal-total-price').textContent =
                    `R$ ${(unit * qty).toFixed(2).replace('.', ',')}`;
            }

            function generateObservationText(addedArr, removedArr) {
                const parts = [];
                if (addedArr && addedArr.length) parts.push('Adicionados: ' + addedArr.map(a =>
                    `${a.name} x${a.diff}`).join('; '));
                if (removedArr && removedArr.length) parts.push('Removidos: ' + removedArr.map(r =>
                    `${r.name} x${r.diff}`).join('; '));
                return parts.join(' | ');
            }

            // modal quantity buttons
            const modalDecreaseBtn = document.getElementById('modal-decrease-quantity');
            const modalIncreaseBtn = document.getElementById('modal-increase-quantity');
            const modalQuantityInput = document.getElementById('modal-product-quantity');

            if (modalDecreaseBtn) {
                modalDecreaseBtn.onclick = function() {
                    if (!modalQuantityInput) return;
                    let v = parseInt(modalQuantityInput.value) || 1;
                    if (v > 1) modalQuantityInput.value = v - 1;
                    updateModalTotals();
                };
            }

            if (modalIncreaseBtn) {
                modalIncreaseBtn.onclick = function() {
                    if (!modalQuantityInput) return;
                    modalQuantityInput.value = (parseInt(modalQuantityInput.value) || 1) + 1;
                    updateModalTotals();
                };
            }

            if (modalQuantityInput) {
                modalQuantityInput.onchange = updateModalTotals;
            }

            // panel quantity buttons
            const quantityInput = document.getElementById('product-quantity');
            const decreaseBtn = document.getElementById('decrease-quantity');
            const increaseBtn = document.getElementById('increase-quantity');

            if (decreaseBtn) {
                decreaseBtn.onclick = function() {
                    if (!quantityInput) return;
                    let v = parseInt(quantityInput.value) || 1;
                    if (v > 1) quantityInput.value = v - 1;
                    updatePanelPriceDisplay();
                };
            }

            if (increaseBtn) {
                increaseBtn.onclick = function() {
                    if (!quantityInput) return;
                    quantityInput.value = (parseInt(quantityInput.value) || 1) + 1;
                    updatePanelPriceDisplay();
                };
            }

            if (quantityInput) {
                quantityInput.onchange = updatePanelPriceDisplay;
            }

            // Função para fechar detalhes do produto
            function closeProductDetail() {
                detailPanel.classList.remove('active');
                panelOverlay.classList.remove('active');
                document.body.style.overflow = '';
                currentProduct = null;
            }

            // Atualizar botão de adicionar ao carrinho
            function updateAddToCartButton() {
                if (!currentProduct) return;

                const quantity = parseInt(quantityInput.value);
                const unitPrice = calculateProductUnitPrice();
                const total = unitPrice * quantity;

                document.getElementById('add-to-cart-total').textContent =
                    `- R$ ${total.toFixed(2).replace('.', ',')}`;
            }

            // Atualizar display de preço no painel lateral
            function updatePanelPriceDisplay() {
                if (!currentProduct) return;
                const unitPrice = calculateProductUnitPrice();
                const quantity = parseInt(quantityInput.value) || 1;
                const total = unitPrice * quantity;

                const priceEl = document.getElementById('panel-product-price');
                if (priceEl) {
                    priceEl.textContent = `R$ ${unitPrice.toFixed(2).replace('.', ',')}`;
                }

                const totalEl = document.getElementById('add-to-cart-total');
                if (totalEl) {
                    totalEl.textContent = `- R$ ${total.toFixed(2).replace('.', ',')}`;
                }
            }

            function addToCartQuick(item) {
                addToCart({
                    id: item.id,
                    name: item.name,
                    price: item.price,
                    image: item.image,
                    quantity: item.quantity,
                    notes: '',
                    selectedIngredients: [],
                    addedIngredients: [],
                    removedIngredients: []
                });
            }

            // Adicionar item ao carrinho
            function addToCart(item) {
                const quantityToAdd = parseInt(item.quantity) || 1;

                // Verificar se o item já existe no carrinho
                const existingItemIndex = cart.findIndex(cartItem =>
                    cartItem.id === item.id && cartItem.notes === item.notes
                );

                if (existingItemIndex > -1) {
                    // Atualizar quantidade existente em vez de somar apenas 1
                    cart[existingItemIndex].quantity += quantityToAdd;
                } else {
                    const newItem = {
                        ...item,
                        quantity: quantityToAdd
                    };
                    cart.push(newItem);
                }

                updateCart();

                // Feedback visual
                showToast('Item adicionado ao carrinho!');
            }

            function updateCartItemQuantityByProductId(productId, quantity) {
                const index = cart.findIndex(item => item.id === parseInt(productId));
                if (index === -1) return;

                if (quantity < 1) {
                    removeFromCart(index);
                } else {
                    cart[index].quantity = quantity;
                    updateCart();
                }
            }

            function removeFromCartByProductId(productId) {
                const index = cart.findIndex(item => item.id === parseInt(productId));
                if (index === -1) return;
                removeFromCart(index);
            }

            // Remover item do carrinho
            function removeFromCart(index) {
                cart.splice(index, 1);
                updateCart();
            }

            function updateProductCards() {
                const quickAddPanels = document.querySelectorAll('.product-quick-add');
                quickAddPanels.forEach(panel => {
                    const productId = panel.dataset.productId;
                    const qtyDisplay = panel.querySelector('.quick-qty');
                    const removeBtn = panel.closest('.product-controls').querySelector('.quick-remove');
                    const totalQuantity = cart.reduce((sum, item) =>
                        String(item.id) === String(productId) ? sum + item.quantity : sum,
                        0
                    );

                    panel.dataset.quantity = totalQuantity;
                    if (qtyDisplay) {
                        qtyDisplay.textContent = totalQuantity;
                    }
                    if (removeBtn) {
                        removeBtn.style.display = totalQuantity > 0 ? 'block' : 'none';
                    }
                });
            }

            // Atualizar quantidade no carrinho
            function updateCartItemQuantity(index, quantity) {
                if (quantity < 1) {
                    removeFromCart(index);
                } else {
                    cart[index].quantity = quantity;
                    updateCart();
                }
            }

            // Atualizar visualização do carrinho
            function updateCart() {
                const cartItemsContainer = document.getElementById('floating-cart-items');
                const cartCount = document.getElementById('floating-cart-count');
                const cartTotal = document.getElementById('floating-cart-total');
                const cartTotalFooter = document.getElementById('floating-cart-total-footer');
                const checkoutBtn = document.getElementById('floating-checkout-btn');

                // Calcular totais
                const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
                const totalPrice = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

                // Atualizar contadores
                cartCount.textContent = `${totalItems} ${totalItems === 1 ? 'item' : 'itens'}`;
                cartTotal.textContent = `R$ ${totalPrice.toFixed(2).replace('.', ',')}`;
                cartTotalFooter.textContent = `R$ ${totalPrice.toFixed(2).replace('.', ',')}`;
                checkoutBtn.innerHTML =
                    `<i class="fas fa-check me-2"></i> Finalizar Pedido - R$ ${totalPrice.toFixed(2).replace('.', ',')}`;

                // Atualizar os cards dos produtos
                updateProductCards();

                // Habilitar/desabilitar botão de checkout
                checkoutBtn.disabled = cart.length === 0;

                // Mostrar/ocultar carrinho flutuante
                floatingCart.style.display = 'flex';
                floatingCart.style.flexDirection = 'column';
                if (cart.length === 0) {
                    floatingCartContent.style.display = 'none';
                    floatingCartIcon.className = 'fas fa-chevron-up';
                }

                // Renderizar itens do carrinho
                cartItemsContainer.innerHTML = '';
                if (cart.length === 0) {
                    cartItemsContainer.innerHTML = '<p class="text-muted text-center mb-0">Carrinho vazio</p>';
                } else {
                    cartItemsContainer.innerHTML = cart.map((item, index) => {
                        const addedHtml = (item.addedIngredients || []).length ?
                            `<div class="small text-success">Adicionados: ${item.addedIngredients.map(a => `${a.name} x${a.diff || 1}`).join('; ')}</div>` :
                            '';
                        const removedHtml = (item.removedIngredients || []).length ?
                            `<div class="small text-danger">Removidos: ${item.removedIngredients.map(r => `${r.name} x${r.diff || 1}`).join('; ')}</div>` :
                            '';
                        return `
                <div class="cart-item d-flex gap-3">
                    ${item.image ? `
                                                <img src="/storage/${item.image}" alt="${item.name}" class="cart-item-img">
                                                ` : `
                                                <div class="cart-item-placeholder"><i class="fas fa-utensils"></i></div>
                                                `}
                    <div class="flex-fill min-w-0">
                        <h6 class="mb-1 cart-item-name">${item.name}</h6>
                        ${item.notes ? `<p class="mb-1 small text-muted">${item.notes}</p>` : ''}
                        ${addedHtml}
                        ${removedHtml}
                        <div class="d-flex align-items-center justify-content-between mt-2">
                            <div class="d-flex align-items-center gap-2">
                                <button class="btn btn-sm cart-qty-btn" onclick="updateCartItemQuantity(${index}, ${item.quantity - 1})">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <span class="cart-qty-num">${item.quantity}</span>
                                <button class="btn btn-sm cart-qty-btn" onclick="updateCartItemQuantity(${index}, ${item.quantity + 1})">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <div class="cart-price-right">
                                    <div class="small text-muted">R$ ${(item.price).toFixed(2).replace('.', ',')} (unitário)</div>
                                    <div class="fw-semibold">R$ ${(item.price * item.quantity).toFixed(2).replace('.', ',')}</div>
                                </div>
                                <button class="btn btn-sm cart-remove-btn" onclick="removeFromCart(${index})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
                    }).join('');
                }
            }

            // Fazer checkout
            function checkout() {
                if (cart.length === 0) return;

                const notes = document.getElementById('floating-order-notes').value;
                const checkoutBtn = document.getElementById('floating-checkout-btn');

                // Desabilitar botão e mostrar loading
                checkoutBtn.disabled = true;
                checkoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Processando...';

                // Preparar dados do pedido (inclui ingredientes selecionados e preço unitário calculado no cliente, mas o servidor deve recalcular)
                const orderData = {
                    store_id: storeId,
                    table_id: tableId,
                    items: cart.map(item => ({
                        product_id: item.id,
                        quantity: item.quantity,
                        notes: item.notes,
                        unit_price: item.price,
                        selected_ingredients: item.selectedIngredients ? item.selectedIngredients
                            .map(i => ({
                                id: i.id,
                                selected_amount: i.selectedAmount || 0
                            })) : []
                    })),
                    notes: notes
                };

                // Enviar pedido
                fetch('/api/orders', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(orderData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success || data.id) {
                            showToast('Pedido realizado com sucesso!', 'success');

                            // Limpar carrinho
                            cart = [];
                            updateCart();

                            // Fechar carrinho flutuante
                            floatingCartContent.style.display = 'none';
                            floatingCartIcon.className = 'fas fa-chevron-up';

                            // Limpar observações
                            document.getElementById('floating-order-notes').value = '';
                        } else {
                            throw new Error(data.message || 'Erro ao processar pedido');
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao fazer pedido:', error);
                        showToast('Erro ao processar pedido. Tente novamente.', 'error');
                    })
                    .finally(() => {
                        // Restaurar botão
                        checkoutBtn.disabled = false;
                        checkoutBtn.innerHTML = '<i class="fas fa-check me-2"></i> Finalizar Pedido';
                    });
            }

            // Toast notification
            function showToast(message, type = 'info') {
                const toast = document.createElement('div');
                toast.style.cssText = `
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 9999;
            animation: slideDown 0.3s ease;
            font-weight: 500;
        `;
                toast.textContent = message;
                document.body.appendChild(toast);

                setTimeout(() => {
                    toast.style.animation = 'slideUp 0.3s ease';
                    setTimeout(() => toast.remove(), 300);
                }, 3000);
            }

            // Expor funções globalmente para uso inline
            window.removeFromCart = removeFromCart;
            window.updateCartItemQuantity = updateCartItemQuantity;

            // Carregar participantes se houver mesa
            @if (isset($table))
                loadParticipants();

                function loadParticipants() {
                    fetch(`/api/tables/${tableId}/participants`)
                        .then(response => response.json())
                        .then(data => {
                            const participantsList = document.getElementById('participants-list');
                            if (data.participants && data.participants.length > 0) {
                                participantsList.innerHTML = data.participants.map(p => {
                                    const icon = p.is_owner ? 'fa-crown' : 'fa-user';
                                    const badgeClass = p.is_owner ? 'participant-badge owner-badge' :
                                        'participant-badge';
                                    return `<span class="${badgeClass}"><i class="fas ${icon} me-1"></i>${p.name}</span>`;
                                }).join('');
                            } else {
                                participantsList.innerHTML =
                                    '<span class="text-muted small">Nenhum participante ainda</span>';
                            }
                        })
                        .catch(error => console.error('Erro ao carregar participantes:', error));
                }

                // Atualizar participantes a cada 30 segundos
                setInterval(loadParticipants, 30000);
            @endif

            // Botão de ver pedidos (para mesa ou balcão)
            @if (isset($table) || (isset($isCounter) && $isCounter))
                document.getElementById('viewOrdersBtn')?.addEventListener('click', function() {
                    loadOrders();
                    const modal = new bootstrap.Modal(document.getElementById('ordersModal'));
                    modal.show();
                });

                function loadOrders() {
                    const modalBody = document.getElementById('ordersModalBody');
                    modalBody.innerHTML =
                        '<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';

                    // URL diferente para mesa ou balcão
                    const ordersUrl = isCounter ? `/api/counter/${qrCode}/orders` : `/api/tables/${tableId}/orders`;

                    fetch(ordersUrl)
                        .then(response => response.json())
                        .then(data => {
                            if (data.orders && data.orders.length > 0) {
                                const hasPendingOrders = data.orders.some(order => order.payment_status ===
                                    'pending');

                                let html = data.orders.map(order => `
                        <div class="card mb-3 card-custom">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1">Pedido #${order.order_number || order.id}</h6>
                                        <small class="text-muted d-block">${new Date(order.created_at).toLocaleString('pt-BR')}</small>
                                        ${order.participant_name ? `
                                                                                                            <small class="text-muted">
                                                                                                            <i class="fas fa-user me-1"></i>
                                                                                                            <strong>${order.participant_name}</strong>
                                                                                                            </small>
                                                                                                            ` : ''}
                                    </div>
                                    <div class="d-flex gap-2">
                                        ${order.payment_status === 'paid' ? `
                                                                                                            <span class="badge badge-sm badge-paid">
                                                                                                            <i class="fas fa-check-circle me-1"></i>Pago
                                                                                                            </span>
                                                                                                            ` : `
                                                                                                            <span class="badge badge-sm badge-pending">
                                                                                                            <i class="fas fa-clock me-1"></i>Pendente
                                                                                                            </span>
                                                                                                            `}
                                        <span class="badge badge-sm ${order.status === 'Finalizado' ? 'badge-status-success' : order.status === 'Em produção' ? 'badge-status-warning' : order.status === 'Aguardando pagamento' ? 'badge-status-info' : order.status === 'Cancelado' ? 'badge-status-danger' : 'badge-status-secondary'}">
                                            ${order.status}
                                        </span>
                                    </div>
                                </div>
                                <div class="order-items">
                                    ${order.items.map(item => `
                                                                                                        <div class="d-flex justify-content-between py-1">
                                                                                                        <span>${item.quantity}x ${item.product_name}</span>
                                                                                                        <span>R$ ${parseFloat(item.price * item.quantity).toFixed(2).replace('.', ',')}</span>
                                                                                                        </div>
                                                                                                        `).join('')}
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>Total</strong>
                                    <strong>R$ ${parseFloat(order.total).toFixed(2).replace('.', ',')}</strong>
                                </div>
                            </div>
                        </div>
                    `).join('');

                                // Adicionar botão de pagar no final se houver pedidos pendentes
                                if (hasPendingOrders) {
                                    html += `
                            <div class="mt-3">
                                <a href="/payment/${qrCode}" class="btn w-100 btn-pay">
                                    <i class="fas fa-credit-card me-2"></i>Pagar
                                </a>
                            </div>
                        `;
                                }

                                modalBody.innerHTML = html;
                            } else {
                                modalBody.innerHTML =
                                    '<p class="text-muted text-center">Nenhum pedido ainda</p>';
                            }
                        })
                        .catch(error => {
                            console.error('Erro ao carregar pedidos:', error);
                            modalBody.innerHTML =
                                '<p class="text-danger text-center">Erro ao carregar pedidos</p>';
                        });
                }
            @endif
        });

        // Animações CSS
        const style = document.createElement('style');
        style.textContent = `
    @keyframes slideDown {
        from {
            transform: translate(-50%, -100%);
            opacity: 0;
        }
        to {
            transform: translate(-50%, 0);
            opacity: 1;
        }
    }
    
    @keyframes slideUp {
        from {
            transform: translate(-50%, 0);
            opacity: 1;
        }
        to {
            transform: translate(-50%, -100%);
            opacity: 0;
        }
    }
`;
        document.head.appendChild(style);
    </script>
@endsection

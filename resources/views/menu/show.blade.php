@extends('layouts.app')

@section('content')
    <div class="menu-wrapper" style="min-height: 100vh; background-color: #f8f9fa;">

        <!-- Seção de Capa e Header -->
        <div class="profile-section" style="position: relative;">
            <!-- Imagem de capa do restaurante -->
            <div class="cover-image-wrapper" style="height: 180px; overflow: hidden; position: relative;">
                @if (isset($store) && $store->cover_image)
                    <img src="{{ asset('storage/' . $store->cover_image) }}" alt="Capa {{ $store->name }}" class="cover-image"
                        style="width: 100%; height: 100%; object-fit: cover;">
                    <div class="cover-overlay"
                        style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(to bottom, rgba(0,0,0,0.2) 0%, rgba(0, 0, 0, 0.6) 100%);">
                    </div>
                @else
                    <!-- Placeholder para capa -->
                    <div
                        style="width: 100%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-utensils" style="font-size: 4rem; color: rgba(255, 255, 255, 0.3);"></i>
                    </div>
                    <div class="cover-overlay"
                        style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(to bottom, rgba(0,0,0,0.2) 0%, rgba(0, 0, 0, 0.6) 100%);">
                    </div>
                @endif

                @if (isset($table) || (isset($isCounter) && $isCounter))
                    <!-- Botão para visualizar pedidos da mesa ou balcão -->
                    <button type="button" class="btn position-absolute top-0 end-0 m-3" id="viewOrdersBtn"
                        style="background: rgba(0,0,0,0.5); border: none; color: white; border-radius: 20px; padding: 0.5rem 1rem; z-index: 10;">
                        <i class="fas fa-receipt me-1"></i> Pedido
                    </button>
                @endif
            </div>

            <!-- Header do Cardápio -->
            <div class="menu-header px-3 text-center position-relative"
                style="background: white; color: #333; padding-top: 50px; padding-bottom: 1rem; margin-top: -30px; border-radius: 20px 20px 0 0; position: relative; z-index: 5; box-shadow: 0 -2px 10px rgba(0,0,0,0.1);">
                @if ($store->logo)
                    <img src="{{ asset('storage/' . $store->logo) }}" alt="{{ $store->name }}"
                        class="rounded-circle store-logo"
                        style="width: 80px; height: 80px; object-fit: cover; position: absolute; top: -40px; left: 50%; transform: translateX(-50%); border: 4px solid white; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);">
                @endif
                <h1 class="mb-1" style="color: #333; font-weight: 700; font-size: 1.5rem;">{{ $store->name }}</h1>
                @if ($store->description)
                    <p class="mb-0 small text-muted">{{ $store->description }}</p>
                @endif
            </div>
        </div>

        <!-- Seção de Participantes da Mesa -->
        @if (isset($table))
            <div class="container-fluid px-3 py-2">
                <div class="participants-card"
                    style="background: white; border-radius: 12px; padding: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                        <div class="d-flex align-items-center mb-2 mb-md-0">
                            <i class="fas fa-users me-2" style="font-size: 1.1rem; color: #000;"></i>
                            <strong style="font-size: 0.9rem; color: #000; font-weight: 700;">Participantes:</strong>
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
            <div class="category-nav sticky-top"
                style="background: white; border-bottom: 1px solid #e0e0e0; top: 0; z-index: 1020; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <div class="container-fluid px-3">
                    <div class="nav-scroller" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                        <nav class="nav py-2 flex-nowrap" style="gap: 0.5rem;">
                            @foreach ($categories as $category)
                                <a class="nav-link category-tab {{ $loop->first ? 'active' : '' }}"
                                    data-category="category-{{ $category->id }}"
                                    style="color: #666; white-space: nowrap; transition: all 0.3s ease; cursor: pointer; padding: 0.4rem 0.875rem; border-radius: 20px; font-weight: 500; font-size: 0.85rem; background: transparent; border: none;">
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
                <div class="category-divider"
                    style="background: {{ $loop->iteration % 5 == 1 ? '#8b5cf6' : ($loop->iteration % 5 == 2 ? '#3b82f6' : ($loop->iteration % 5 == 3 ? '#10b981' : ($loop->iteration % 5 == 4 ? '#f59e0b' : '#ef4444'))) }}; padding: 0.5rem 1.25rem; margin-top: {{ $loop->first ? '0' : '0' }};">
                    <h2 class="mb-0" style="color: white; font-size: 0.95rem; font-weight: 600;">
                        {{ $category->name }}
                    </h2>
                </div>

                <!-- Lista de Produtos da Categoria -->
                <div id="category-{{ $category->id }}" class="menu-category" style="background: white;">
                    @foreach ($category->products as $product)
                        <div class="product-item" data-product-id="{{ $product->id }}"
                            data-product-name="{{ $product->name }}" data-product-price="{{ $product->price }}"
                            data-product-image="{{ $product->image }}"
                            style="border-bottom: 1px solid #f0f0f0; padding: 1rem 1.25rem; transition: background 0.2s ease; position: relative;">
                            <div class="d-flex gap-3">
                                <!-- Imagem do Produto (Pequena à Esquerda) -->
                                @if ($product->image)
                                    <div class="product-image" style="flex-shrink: 0;">
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                            style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                                    </div>
                                @else
                                    <div class="product-image"
                                        style="flex-shrink: 0; width: 80px; height: 80px; background: #f0f0f0; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-utensils" style="color: #ccc; font-size: 2rem;"></i>
                                    </div>
                                @endif

                                <!-- Informações do Produto -->
                                <div class="product-info" style="flex: 1; min-width: 0;">
                                    <h3 class="product-name mb-1"
                                        style="font-size: 1rem; font-weight: 600; color: #333; margin: 0;">
                                        {{ $product->name }}
                                    </h3>

                                    @if ($product->description)
                                        <p class="product-description mb-1"
                                            style="font-size: 0.85rem; color: #999; margin: 0; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                            {{ $product->description }}
                                        </p>
                                    @endif

                                    @if ($product->ingredients)
                                        <p class="product-ingredients mb-1"
                                            style="font-size: 0.8rem; color: #bbb; margin: 0;">
                                            {{ Str::limit($product->ingredients, 60) }}
                                        </p>
                                    @endif

                                    <div class="product-card-actions">
                                        <div>
                                            <div class="product-price"
                                                style="font-size: 1rem; font-weight: 600; color: #333;">
                                                R$ {{ number_format($product->price, 2, ',', '.') }}
                                            </div>
                                            <div class="item-total-price text-muted"
                                                style="font-size: 0.9rem; display: none;">
                                                R$ 0,00
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center gap-2">
                                            <button type="button" class="btn card-action card-decrease-btn"
                                                data-product-id="{{ $product->id }}" disabled>
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <span class="card-quantity" data-product-id="{{ $product->id }}">0</span>
                                            <button type="button" class="btn card-action card-increase-btn"
                                                data-product-id="{{ $product->id }}">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                            <button type="button" class="btn card-remove-btn"
                                                data-product-id="{{ $product->id }}" style="display: none;">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>

                                    @if ($product->description)
                                        <div class="text-end mt-2">
                                            <button type="button" class="detail-button"
                                                data-product-id="{{ $product->id }}">
                                                Saiba mais
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach

            <!-- Espaçamento no final para não ficar atrás do carrinho flutuante -->
            <div style="height: 100px;"></div>
        </div>
    </div>

    <!-- Painel Lateral de Detalhes do Produto (Slide da Direita) -->
    <div id="product-detail-panel" class="product-detail-panel"
        style="position: fixed; top: 0; right: 0; width: 100%; height: 100%; background: white; z-index: 2000; transform: translateX(100%); transition: transform 0.3s ease-in-out; overflow-y: auto;">
        <div class="panel-content">
            <!-- Header do Painel -->
            <div class="panel-header"
                style="position: sticky; top: 0; background: white; z-index: 10; padding: 1rem 1.25rem; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; justify-content: space-between;">
                <button type="button" id="close-panel" class="btn"
                    style="background: transparent; border: none; color: #000; padding: 0.5rem; margin-left: -0.5rem;">
                    <i class="fas fa-arrow-left" style="font-size: 1.2rem;"></i>
                </button>
                <h2 class="mb-0"
                    style="font-size: 1.1rem; font-weight: 700; flex: 1; text-align: center; padding: 0 1rem; color: #000;">
                    Detalhes do Produto</h2>
                <div style="width: 40px;"></div>
            </div>

            <!-- Imagem Grande do Produto -->
            <div id="panel-image-container" class="panel-image"
                style="width: 100%; height: 250px; overflow: hidden; background: #f0f0f0;">
                <!-- Imagem será inserida aqui -->
            </div>

            <!-- Corpo do Painel -->
            <div class="panel-body" style="padding: 1.5rem 1.25rem;">
                <h2 id="panel-product-name" class="mb-2" style="font-size: 1.5rem; font-weight: 700; color: #333;">
                </h2>

                <div id="panel-product-description" class="mb-3"
                    style="font-size: 0.95rem; color: #666; line-height: 1.6;"></div>

                <div id="panel-product-ingredients" class="mb-3" style="font-size: 0.85rem; color: #999;"></div>

                <div class="price-section mb-4" style="padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                    <div class="d-flex justify-content-between align-items-center">
                        <span style="font-size: 0.9rem; color: #666;">Preço</span>
                        <span id="panel-product-price" style="font-size: 1.5rem; font-weight: 700; color: #333;"></span>
                    </div>
                </div>

                <!-- Seletor de Quantidade -->
                <div class="quantity-section mb-4">
                    <label class="form-label"
                        style="font-size: 0.9rem; font-weight: 600; color: #333; margin-bottom: 0.75rem;">Quantidade</label>
                    <div class="d-flex align-items-center gap-3">
                        <button type="button" id="decrease-quantity" class="btn"
                            style="width: 40px; height: 40px; border-radius: 50%; background: #f0f0f0; border: none; color: #333; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" id="product-quantity" value="1" min="1"
                            class="form-control text-center"
                            style="width: 80px; height: 40px; border: 2px solid #f0f0f0; border-radius: 8px; font-weight: 600; font-size: 1.1rem;">
                        <button type="button" id="increase-quantity" class="btn"
                            style="width: 40px; height: 40px; border-radius: 50%; background: #f0f0f0; border: none; color: #333; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>

                <!-- Observações -->
                <div class="notes-section mb-4">
                    <label for="product-notes" class="form-label"
                        style="font-size: 0.9rem; font-weight: 600; color: #333; margin-bottom: 0.75rem;">Observações
                        (opcional)</label>
                    <textarea id="product-notes" class="form-control" rows="3" placeholder="Ex: Sem cebola, ponto da carne..."
                        style="border: 2px solid #f0f0f0; border-radius: 8px; font-size: 0.9rem;"></textarea>
                </div>
            </div>

            <!-- Footer Fixo com Botão Adicionar -->
            <div class="panel-footer"
                style="position: sticky; bottom: 0; background: white; padding: 1rem 1.25rem; border-top: 1px solid #f0f0f0; box-shadow: 0 -2px 10px rgba(0,0,0,0.05);">
                <button type="button" id="add-to-cart-btn" class="btn w-100"
                    style="background: #000; border: none; color: white; font-weight: 600; border-radius: 12px; padding: 1rem; font-size: 1rem; transition: all 0.3s ease;">
                    <i class="fas fa-shopping-cart me-2"></i>
                    <span id="add-to-cart-text">Adicionar ao Carrinho</span>
                    <span id="add-to-cart-total" style="margin-left: 0.5rem;"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Overlay Escuro -->
    <div id="panel-overlay"
        style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1999; opacity: 0; pointer-events: none; transition: opacity 0.3s ease;">
    </div>

    <!-- Resumo Flutuante do Carrinho (Mobile) -->
    <div id="floating-cart-summary" class="floating-cart-summary"
        style="display: none; position: fixed; bottom: 0; left: 0; right: 0; z-index: 1050; background: white; box-shadow: 0 -2px 10px rgba(0,0,0,0.1); transition: all 0.3s ease;">
        <div class="floating-cart-header" id="floating-cart-toggle"
            style="padding: 1rem 1.25rem; cursor: pointer; display: flex; justify-content: space-between; align-items: center; background: #000; color: white;">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-shopping-cart"></i>
                <span id="floating-cart-count" style="font-weight: 600;">0 itens</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span id="floating-cart-total" style="font-weight: 700; font-size: 1.1rem;">R$ 0,00</span>
                <i class="fas fa-chevron-up" id="floating-cart-icon"></i>
            </div>
        </div>
        <div class="floating-cart-content" id="floating-cart-content"
            style="display: none; max-height: 60vh; overflow-y: auto;">
            <div class="floating-cart-items" id="floating-cart-items" style="padding: 1rem 1.25rem;">
                <!-- Itens do carrinho serão exibidos aqui -->
            </div>
            <div class="floating-cart-footer"
                style="padding: 1rem 1.25rem; background: #f8f9fa; border-top: 1px solid #e0e0e0;">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3"
                    style="border-bottom: 2px solid #e0e0e0;">
                    <span style="font-size: 1rem; font-weight: 600; color: #333;">
                        <i class="fas fa-calculator me-2"></i>Total
                    </span>
                    <span id="floating-cart-total-footer" style="font-size: 1.3rem; font-weight: 700; color: #000;">R$
                        0,00</span>
                </div>
                <div class="mb-3">
                    <label for="floating-order-notes" class="form-label"
                        style="font-size: 0.9rem; font-weight: 700; color: #000;">
                        <i class="fas fa-comment-dots me-1"></i>
                        Observações do Pedido
                    </label>
                    <textarea id="floating-order-notes" class="form-control" rows="2" placeholder="Alguma observação? (opcional)"
                        style="border: 2px solid #e0e0e0; border-radius: 8px; font-size: 0.9rem;"></textarea>
                </div>
                <button id="floating-checkout-btn" class="btn w-100" disabled
                    style="background: #000; border: none; color: white; font-weight: 600; border-radius: 12px; padding: 0.875rem; font-size: 1rem;">
                    <i class="fas fa-check me-2"></i> Finalizar Pedido
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de Autenticação da Mesa -->
    @if (isset($table))
        <div class="modal fade" id="authModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius: 16px;">
                    <div class="modal-header" style="background: #000; color: white; border-radius: 16px 16px 0 0;">
                        <h5 class="modal-title" id="authModalTitle">
                            <i class="fas fa-lock me-2"></i>
                            Acesso à Mesa
                        </h5>
                    </div>
                    <div class="modal-body" style="padding: 2rem;">
                        <!-- Etapa 1: Criar senha (primeiro usuário) -->
                        <div id="createPasswordStep" style="display: none;">
                            <p class="text-center mb-4">Olá, Seja Bem-vindo!</p>
                            <form id="createPasswordForm">
                                <!-- <div class="mb-3">
                                                                        <label for="newPassword" class="form-label">Senha (4 dígitos)</label>
                                                                        <input type="text" class="form-control text-center" id="newPassword" maxlength="4" pattern="[0-9]{4}" placeholder="0000" style="font-size: 1.5rem; letter-spacing: 0.5rem; border: 2px solid #e0e0e0; border-radius: 8px;" required>
                                                                        <small class="text-muted">Digite uma senha numérica de 4 dígitos</small>
                                                                    </div> -->
                                <div class="mb-3">
                                    <label for="ownerName" class="form-label">Digite seu nome para continuarmos</label>
                                    <input type="text" class="form-control" id="ownerName"
                                        placeholder="Digite seu nome"
                                        style="border: 2px solid #e0e0e0; border-radius: 8px;" required>
                                </div>
                                <button type="submit" class="btn w-100"
                                    style="background: #000; color: white; border-radius: 8px; padding: 0.75rem; font-weight: 600;">
                                    <i class="fas fa-check me-2"></i>Entrar
                                </button>
                            </form>
                        </div>

                        <!-- Etapa 2: Validar senha (demais usuários) -->
                        <div id="validatePasswordStep" style="display: none;">
                            <p class="text-center mb-4">Digite a senha da mesa para continuar.</p>
                            <form id="validatePasswordForm">
                                <div class="mb-3">
                                    <label for="tablePassword" class="form-label">Senha da Mesa</label>
                                    <input type="text" class="form-control text-center" id="tablePassword"
                                        maxlength="4" pattern="[0-9]{4}" placeholder="0000"
                                        style="font-size: 1.5rem; letter-spacing: 0.5rem; border: 2px solid #e0e0e0; border-radius: 8px;"
                                        required>
                                </div>
                                <button type="submit" class="btn w-100"
                                    style="background: #000; color: white; border-radius: 8px; padding: 0.75rem; font-weight: 600;">
                                    <i class="fas fa-unlock me-2"></i>Validar Senha
                                </button>
                            </form>
                            <div id="passwordError" class="alert alert-danger mt-3" style="display: none;"></div>
                        </div>

                        <!-- Etapa 3: Digitar nome (após validar senha) -->
                        <div id="enterNameStep" style="display: none;">
                            <p class="text-center mb-4">Senha validada! Agora digite seu nome para entrar na mesa.</p>
                            <form id="enterNameForm">
                                <div class="mb-3">
                                    <label for="participantName" class="form-label">Seu Nome</label>
                                    <input type="text" class="form-control" id="participantName"
                                        placeholder="Digite seu nome"
                                        style="border: 2px solid #e0e0e0; border-radius: 8px;" required>
                                </div>
                                <button type="submit" class="btn w-100"
                                    style="background: #000; color: white; border-radius: 8px; padding: 0.75rem; font-weight: 600;">
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
                <div class="modal-content" style="border-radius: 16px;">
                    <div class="modal-header" style="background: #000; color: white; border-radius: 16px 16px 0 0;">
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
                    <div class="modal-body" id="ordersModalBody" style="padding: 1.5rem;">
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
                checkTableAuthentication();
            @endif

            function checkTableAuthentication() {
                fetch(`/api/table/${qrCode}/status`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.is_authenticated) {
                            // Mostrar modal de autenticação
                            const authModal = new bootstrap.Modal(document.getElementById('authModal'));
                            authModal.show();

                            // Verificar se precisa criar senha ou validar
                            if (!data.has_password) {
                                // Primeira pessoa - criar senha
                                document.getElementById('createPasswordStep').style.display = 'block';
                                document.getElementById('validatePasswordStep').style.display = 'none';
                                document.getElementById('enterNameStep').style.display = 'none';
                            } else {
                                // Demais pessoas - validar senha
                                document.getElementById('createPasswordStep').style.display = 'none';
                                document.getElementById('validatePasswordStep').style.display = 'block';
                                document.getElementById('enterNameStep').style.display = 'none';
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

                const password = document.getElementById('newPassword');
                const name = document.getElementById('ownerName').value;
                const submitBtn = this.querySelector('button[type="submit"]');

                // Validar senha
                /*if (!/^[0-9]{4}$/.test(password)) {
                    showToast('A senha deve conter exatamente 4 dígitos', 'error');
                    return;
                }*/

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
                            password: password,
                            name: name
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('Senha criada com sucesso!', 'success');
                            // Fechar modal e recarregar página
                            bootstrap.Modal.getInstance(document.getElementById('authModal')).hide();
                            setTimeout(() => location.reload(), 500);
                        } else {
                            showToast(data.message || 'Erro ao criar senha', 'error');
                            submitBtn.disabled = false;
                            submitBtn.innerHTML =
                                '<i class="fas fa-check me-2"></i>Criar Senha e Entrar';
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        showToast('Erro ao criar senha', 'error');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-check me-2"></i>Criar Senha e Entrar';
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
                        errorDiv.textContent = 'Erro ao validar senha';
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

            // Permitir apenas números nos campos de senha
            document.getElementById('newPassword')?.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
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

            // Event delegation para ações nos cards de produto
            menuContent.addEventListener('click', function(event) {
                const increaseBtn = event.target.closest('.card-increase-btn');
                const decreaseBtn = event.target.closest('.card-decrease-btn');
                const removeBtn = event.target.closest('.card-remove-btn');
                const detailBtn = event.target.closest('.detail-button');

                if (increaseBtn) {
                    const productId = increaseBtn.dataset.productId;
                    const product = getProductFromCard(productId);
                    if (product) {
                        addToCart({
                            ...product,
                            quantity: 1,
                            notes: ''
                        });
                    }
                    return;
                }

                if (decreaseBtn) {
                    const productId = decreaseBtn.dataset.productId;
                    const cartItem = cart.find(item => item.id === parseInt(productId));
                    if (cartItem) {
                        updateCartItemQuantityByProductId(productId, cartItem.quantity - 1);
                    }
                    return;
                }

                if (removeBtn) {
                    const productId = removeBtn.dataset.productId;
                    removeFromCartByProductId(productId);
                    return;
                }

                if (detailBtn) {
                    const productId = detailBtn.dataset.productId;
                    openProductDetail(productId);
                    return;
                }
            });

            // Fechar painel
            closePanel.addEventListener('click', closeProductDetail);
            panelOverlay.addEventListener('click', closeProductDetail);

            // Toggle do carrinho flutuante
            floatingCartToggle.addEventListener('click', function() {
                const isOpen = floatingCartContent.style.display !== 'none';
                floatingCartContent.style.display = isOpen ? 'none' : 'block';
                floatingCartIcon.className = isOpen ? 'fas fa-chevron-up' : 'fas fa-chevron-down';
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

            // Controles de quantidade
            const quantityInput = document.getElementById('product-quantity');
            const decreaseBtn = document.getElementById('decrease-quantity');
            const increaseBtn = document.getElementById('increase-quantity');

            decreaseBtn.addEventListener('click', function() {
                const currentValue = parseInt(quantityInput.value);
                if (currentValue > 1) {
                    quantityInput.value = currentValue - 1;
                    updateAddToCartButton();
                }
            });

            increaseBtn.addEventListener('click', function() {
                const currentValue = parseInt(quantityInput.value);
                quantityInput.value = currentValue + 1;
                updateAddToCartButton();
            });

            quantityInput.addEventListener('change', function() {
                if (this.value < 1) this.value = 1;
                updateAddToCartButton();
            });

            // Adicionar ao carrinho
            document.getElementById('add-to-cart-btn').addEventListener('click', function() {
                if (!currentProduct) return;

                const quantity = parseInt(quantityInput.value);
                const notes = document.getElementById('product-notes').value;

                addToCart({
                    id: currentProduct.id,
                    name: currentProduct.name,
                    price: currentProduct.price,
                    image: currentProduct.image,
                    quantity: quantity,
                    notes: notes
                });

                closeProductDetail();
            });

            // Botão de checkout
            document.getElementById('floating-checkout-btn').addEventListener('click', function() {
                if (cart.length === 0) return;
                checkout();
            });

            // Função para abrir detalhes do produto
            function openProductDetail(productId) {
                // Buscar dados do produto
                fetch(`/api/products/${productId}`)
                    .then(response => response.json())
                    .then(product => {
                        currentProduct = product;

                        // Preencher informações do painel
                        document.getElementById('panel-product-name').textContent = product.name;
                        document.getElementById('panel-product-price').textContent =
                            `R$ ${parseFloat(product.price).toFixed(2).replace('.', ',')}`;

                        // Descrição
                        const descriptionEl = document.getElementById('panel-product-description');
                        if (product.description) {
                            descriptionEl.textContent = product.description;
                            descriptionEl.style.display = 'block';
                        } else {
                            descriptionEl.style.display = 'none';
                        }

                        // Ingredientes
                        const ingredientsEl = document.getElementById('panel-product-ingredients');
                        if (product.ingredients) {
                            ingredientsEl.innerHTML =
                                `<i class="fas fa-utensils me-2"></i>${product.ingredients}`;
                            ingredientsEl.style.display = 'block';
                        } else {
                            ingredientsEl.style.display = 'none';
                        }

                        // Imagem
                        const imageContainer = document.getElementById('panel-image-container');
                        if (product.image) {
                            imageContainer.innerHTML =
                                `<img src="/storage/${product.image}" alt="${product.name}" style="width: 100%; height: 100%; object-fit: cover;">`;
                        } else {
                            imageContainer.innerHTML =
                                `<div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #f0f0f0;"><i class="fas fa-utensils" style="font-size: 4rem; color: #ccc;"></i></div>`;
                        }

                        // Reset quantidade e observações
                        quantityInput.value = 1;
                        document.getElementById('product-notes').value = '';

                        updateAddToCartButton();

                        // Abrir painel
                        detailPanel.classList.add('active');
                        panelOverlay.classList.add('active');
                        document.body.style.overflow = 'hidden';
                    })
                    .catch(error => {
                        console.error('Erro ao carregar produto:', error);
                        alert('Erro ao carregar detalhes do produto');
                    });
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
                const total = currentProduct.price * quantity;

                document.getElementById('add-to-cart-total').textContent =
                    `- R$ ${total.toFixed(2).replace('.', ',')}`;
            }

            function getProductCardById(productId) {
                return document.querySelector(`.product-item[data-product-id="${productId}"]`);
            }

            function getProductFromCard(productId) {
                const card = getProductCardById(productId);
                if (!card) return null;
                return {
                    id: parseInt(productId),
                    name: card.dataset.productName,
                    price: parseFloat(card.dataset.productPrice),
                    image: card.dataset.productImage || null
                };
            }

            function updateProductCard(productId) {
                const card = getProductCardById(productId);
                if (!card) return;

                const cartItem = cart.find(item => item.id === parseInt(productId));
                const quantityEl = card.querySelector('.card-quantity');
                const decreaseBtn = card.querySelector('.card-decrease-btn');
                const removeBtn = card.querySelector('.card-remove-btn');
                const totalPriceEl = card.querySelector('.item-total-price');
                const basePrice = parseFloat(card.dataset.productPrice);

                if (cartItem) {
                    quantityEl.textContent = cartItem.quantity;
                    decreaseBtn.disabled = cartItem.quantity <= 1;
                    removeBtn.style.display = 'inline-flex';
                    totalPriceEl.style.display = 'block';
                    totalPriceEl.textContent =
                        `R$ ${(cartItem.price * cartItem.quantity).toFixed(2).replace('.', ',')}`;
                } else {
                    quantityEl.textContent = '0';
                    decreaseBtn.disabled = true;
                    removeBtn.style.display = 'none';
                    totalPriceEl.style.display = 'none';
                    totalPriceEl.textContent = `R$ ${basePrice.toFixed(2).replace('.', ',')}`;
                }
            }

            function updateProductCards() {
                document.querySelectorAll('.product-item').forEach(card => {
                    updateProductCard(card.dataset.productId);
                });
            }

            function addToCart(item) {
                // Verificar se o item já existe no carrinho
                const existingItemIndex = cart.findIndex(cartItem =>
                    cartItem.id === item.id && cartItem.notes === item.notes
                );

                if (existingItemIndex > -1) {
                    // Atualizar quantidade
                    cart[existingItemIndex].quantity += item.quantity;
                } else {
                    // Adicionar novo item
                    cart.push(item);
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
                if (cart.length > 0) {
                    floatingCart.style.display = 'block';
                } else {
                    floatingCart.style.display = 'none';
                    floatingCartContent.style.display = 'none';
                    floatingCartIcon.className = 'fas fa-chevron-up';
                }

                // Renderizar itens do carrinho
                if (cart.length === 0) {
                    cartItemsContainer.innerHTML = '<p class="text-muted text-center mb-0">Carrinho vazio</p>';
                } else {
                    cartItemsContainer.innerHTML = cart.map((item, index) => `
                <div class="cart-item d-flex gap-3">
                    ${item.image ? `
                                                                    <img src="/storage/${item.image}" alt="${item.name}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px; flex-shrink: 0;">
                                                                ` : `
                                                                    <div style="width: 50px; height: 50px; background: #f0f0f0; border-radius: 6px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                                        <i class="fas fa-utensils" style="color: #ccc; font-size: 1.2rem;"></i>
                                                                    </div>
                                                                `}
                    <div style="flex: 1; min-width: 0;">
                        <h6 class="mb-1" style="font-size: 0.9rem; font-weight: 700; color: #000;">${item.name}</h6>
                        ${item.notes ? `<p class="mb-1 small text-muted">${item.notes}</p>` : ''}
                        <div class="d-flex align-items-center justify-content-between mt-2">
                            <div class="d-flex align-items-center gap-2">
                                <button class="btn btn-sm" onclick="updateCartItemQuantity(${index}, ${item.quantity - 1})" style="width: 28px; height: 28px; padding: 0; background: #f0f0f0; border: none; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-minus" style="font-size: 0.7rem;"></i>
                                </button>
                                <span style="font-weight: 700; min-width: 30px; text-align: center; color: #000; font-size: 0.95rem;">${item.quantity}</span>
                                <button class="btn btn-sm" onclick="updateCartItemQuantity(${index}, ${item.quantity + 1})" style="width: 28px; height: 28px; padding: 0; background: #f0f0f0; border: none; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-plus" style="font-size: 0.7rem;"></i>
                                </button>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span style="font-weight: 600; color: #000;">R$ ${(item.price * item.quantity).toFixed(2).replace('.', ',')}</span>
                                <button class="btn btn-sm" onclick="removeFromCart(${index})" style="width: 28px; height: 28px; padding: 0; background: transparent; border: none; color: #ef4444;">
                                    <i class="fas fa-trash" style="font-size: 0.8rem;"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
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

                // Preparar dados do pedido
                const orderData = {
                    store_id: storeId,
                    table_id: tableId,
                    items: cart.map(item => ({
                        product_id: item.id,
                        quantity: item.quantity,
                        notes: item.notes
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
                        <div class="card mb-3" style="border-radius: 12px; border: 2px solid #f0f0f0;">
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
                                                                                        <span class="badge" style="background: #10b981; font-size: 0.75rem; padding: 0.35rem 0.6rem;">
                                                                                            <i class="fas fa-check-circle me-1"></i>Pago
                                                                                        </span>
                                                                                    ` : `
                                                                                        <span class="badge" style="background: #ef4444; font-size: 0.75rem; padding: 0.35rem 0.6rem;">
                                                                                            <i class="fas fa-clock me-1"></i>Pendente
                                                                                        </span>
                                                                                    `}
                                        <span class="badge" style="background: ${
                                            order.status === 'Finalizado' ? '#10b981' : 
                                            order.status === 'Em produção' ? '#f59e0b' : 
                                            order.status === 'Aguardando pagamento' ? '#3b82f6' :
                                            order.status === 'Cancelado' ? '#ef4444' : '#6b7280'
                                        }; font-size: 0.75rem; padding: 0.35rem 0.6rem;">
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
                                <a href="/payment/${qrCode}" class="btn w-100" style="background: #000; color: white; border-radius: 12px; padding: 1rem; font-weight: 600; text-decoration: none; display: inline-block; text-align: center; font-size: 1rem;">
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

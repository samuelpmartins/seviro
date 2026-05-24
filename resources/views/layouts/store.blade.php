<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Estilo específico para garantir que o dropdown funcione -->
    <style>
        .dropdown-menu {
            margin-top: 0.125rem;
        }
        .show > .dropdown-menu {
            display: block !important;
        }
        
        /* Estilos para o logo na navbar */
        .navbar-brand img {
            height: 36px;
            width: auto;
            object-fit: contain;
            transition: all 0.3s ease;
        }
        
        .navbar-brand:hover img {
            transform: scale(1.05);
        }
        
        /* Responsividade para dispositivos móveis */
        @media (max-width: 768px) {
            .navbar-brand img {
                height: 29px;
            }
        }
        
        @media (max-width: 576px) {
            .navbar-brand img {
                height: 25px;
            }
        }
    </style>
    
    <!-- Estilos personalizados (empilhados pelas views) -->
    @stack('styles')
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ route('store.manage') }}">
                    <img src="{{ asset('storage/img/logo.png') }}" alt="Logo">
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('store.manage') }}">Restaurante</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('store.dashboard') }}">Dashboard</a>
                        </li>
                        
                        
                        <!-- Dropdown Telas Auxiliares -->
                        <li class="nav-item dropdown" id="auxiliarScreensDropdown">
                            <a id="auxiliarScreensToggle" class="nav-link dropdown-toggle" href="#" role="button">
                                <i class="fas fa-desktop me-1"></i> Telas Auxiliares
                            </a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{ route('store.orders.production') }}">
                                    <i class="fas fa-utensils me-1"></i> Pedidos em Produção
                                </a>
                                <a class="dropdown-item" href="{{ route('store.orders.history') }}">
                                    <i class="fas fa-history me-1"></i> Histórico de Pedidos
                                </a>
                                <a class="dropdown-item" href="{{ route('store.service.index') }}">
                                    <i class="fas fa-concierge-bell me-1"></i> Tela de Atendimento
                                </a>
                            </div>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('store.categories.index') }}">Categorias</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('store.products.index') }}">Produtos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('store.tables.index') }}">Mesas</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('store.employees.index') }}">Funcionários</a>
                        </li>
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown" id="userDropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button">
                                {{ Auth::user()->name }}
                            </a>

                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                    <i class="fas fa-user me-2"></i> Perfil
                                </a>
                                <a class="dropdown-item" href="{{ route('store.dashboard') }}">
                                    <i class="fas fa-cog me-2"></i> Configurações da Loja
                                </a>
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                   onclick="event.preventDefault();
                                                 document.getElementById('logout-form').submit();">
                                    <i class="fas fa-sign-out-alt me-2"></i> {{ __('Logout') }}
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @if (session('success'))
                <div class="container">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="container">
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        // Verifica o tema salvo no localStorage
        document.addEventListener('DOMContentLoaded', function() {
            const theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', theme);
            updateThemeIcon(theme);
            
            // Inicializar todos os dropdowns
            if (typeof bootstrap !== 'undefined') {
                var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
                dropdownElementList.map(function (dropdownToggleEl) {
                    return new bootstrap.Dropdown(dropdownToggleEl);
                });
            }
            
            // Função para configurar dropdown manualmente
            function setupDropdown(toggleId, dropdownId) {
                const toggle = document.getElementById(toggleId);
                const menu = toggle.nextElementSibling;
                
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    const isOpen = menu.classList.contains('show');
                    
                    // Fechar outros dropdowns abertos
                    document.querySelectorAll('.dropdown-menu.show').forEach(openMenu => {
                        if (openMenu !== menu) {
                            openMenu.classList.remove('show');
                        }
                    });
                    
                    // Alternar a visibilidade do dropdown atual
                    if (isOpen) {
                        menu.classList.remove('show');
                    } else {
                        menu.classList.add('show');
                    }
                });
                
                // Fechar dropdown quando clicar fora
                document.addEventListener('click', function(e) {
                    if (!document.getElementById(dropdownId).contains(e.target)) {
                        menu.classList.remove('show');
                    }
                });
            }
            
            // Implementação manual dos dropdowns
            setupDropdown('navbarDropdown', 'userDropdown');
            setupDropdown('auxiliarScreensToggle', 'auxiliarScreensDropdown');
        });

        // Função para alternar o tema
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            html.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        }

        // Atualiza o ícone do tema
        function updateThemeIcon(theme) {
            const icon = document.getElementById('theme-icon');
            if (icon) {
                icon.className = theme === 'light' ? 'fas fa-moon me-2' : 'fas fa-sun me-2';
            }
        }
    </script>
    
    <!-- Scripts personalizados (empilhados pelas views) -->
    @stack('scripts')
</body>
</html> 
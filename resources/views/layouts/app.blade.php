<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @auth
        <meta name="vapid-public-key" content="{{ config('webpush.vapid.public_key') }}">
    @endauth

    <title>
        @auth
            @role('store')
                {{ auth()->user()->store->name }}
            @else
                {{ config('app.name', 'Laravel') }}
            @endrole
        @else
            {{ config('app.name', 'Laravel') }}
        @endauth
    </title>

    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <!-- Estilos para logo na navbar -->
    <style>
        /* Reset de margens e padding */
        body {
            margin: 0;
            padding: 0;
        }

        main {
            padding: 0 !important;
            margin: 0 !important;
        }

        /* Navbar - Nova Identidade Visual */
        .navbar {
            background-color: #000000 !important;
            border-bottom: 2px solid #9da1a1;
        }

        .navbar-brand img {
            transition: all 0.3s ease;
        }

        a.navbar-brand:hover img {
            transform: scale(1.05);
        }

        span.navbar-brand:hover img {
            transform: none;
        }

        .navbar .nav-link {
            color: #e8e8e9 !important;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .navbar .nav-link:hover {
            color: #9da1a1 !important;
            transform: translateY(-2px);
        }

        .navbar .navbar-toggler {
            border-color: #9da1a1;
        }

        .navbar .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(232, 232, 233, 0.9)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        .navbar .dropdown-menu {
            background: #000000;
            border: 1px solid #9da1a1;
        }

        .navbar .dropdown-item {
            color: #e8e8e9;
            transition: all 0.3s ease;
        }

        .navbar .dropdown-item:hover {
            background: #9da1a1;
            color: #000000;
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
</head>

<body>
    <div id="app">
        @if (!Route::is('menu.show'))
            <nav class="navbar navbar-expand-md shadow-sm">
                <div class="container">
                    @if (isset($logoClickable) && !$logoClickable)
                        <span class="navbar-brand" style="cursor: default;">
                            @auth
                                @role('store')
                                    <img src="{{ asset('storage/img/logo.png') }}" alt="{{ auth()->user()->store->name }}"
                                        style="height: 36px; width: auto; object-fit: contain;">
                                @else
                                    <img src="{{ asset('storage/img/logo.png') }}" alt="{{ config('app.name', 'Laravel') }}"
                                        style="height: 36px; width: auto; object-fit: contain;">
                                @endrole
                            @else
                                <img src="{{ asset('storage/img/logo.png') }}" alt="{{ config('app.name', 'Laravel') }}"
                                    style="height: 36px; width: auto; object-fit: contain;">
                            @endauth
                        </span>
                    @else
                        <a class="navbar-brand"
                            href="@auth @role('store') {{ route('store.manage') }} @else {{ url('/') }} @endrole @else {{ url('/') }} @endauth">
                            @auth
                                @role('store')
                                    <img src="{{ asset('storage/img/logo.png') }}" alt="{{ auth()->user()->store->name }}"
                                        style="height: 36px; width: auto; object-fit: contain;">
                                @else
                                    <img src="{{ asset('storage/img/logo.png') }}" alt="{{ config('app.name', 'Laravel') }}"
                                        style="height: 36px; width: auto; object-fit: contain;">
                                @endrole
                            @else
                                <img src="{{ asset('storage/img/logo.png') }}" alt="{{ config('app.name', 'Laravel') }}"
                                    style="height: 36px; width: auto; object-fit: contain;">
                            @endauth
                        </a>
                    @endif
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                        aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <!-- Left Side Of Navbar -->
                        <ul class="navbar-nav me-auto">
                            @auth('admin')
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('admin.dashboard') }}">Painel</a>
                                </li>
                            @endauth

                            @auth
                                @role('store')
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('store.manage') }}">Dashboard</a>
                                    </li>
                                @endrole
                            @endauth
                        </ul>

                        <!-- Right Side Of Navbar -->
                        <ul class="navbar-nav ms-auto">
                            <!-- Authentication Links -->
                            @if (!Route::is('admin.login') && !Route::is('admin.first-access'))
                                @if (!auth('admin')->check() && !auth()->check())
                                    @if (Route::has('login'))
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                        </li>
                                    @endif

                                    @if (Route::has('register'))
                                        <li class="nav-item">
                                            <a class="nav-link"
                                                href="{{ route('register') }}">{{ __('Register') }}</a>
                                        </li>
                                    @endif
                                @else
                                    <li class="nav-item dropdown">
                                        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#"
                                            role="button" data-bs-toggle="dropdown" aria-haspopup="true"
                                            aria-expanded="false">
                                            {{ auth('admin')->check() ? auth('admin')->user()->name : auth()->user()->name }}
                                        </a>

                                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                            @if (auth()->check())
                                                @if (auth()->user() && auth()->user()->hasRole('waiter'))
                                                    <a class="dropdown-item d-flex align-items-center"
                                                        href="{{ route('waiter.dashboard') }}">
                                                        <i class="fas fa-house me-2"></i>
                                                        Dashboard
                                                    </a>
                                                    @if (!auth()->user()->is_attending)
                                                        <button type="button" id="openAttendingModalButton"
                                                            class="dropdown-item d-flex align-items-center">
                                                            <i class="fas fa-concierge-bell me-2"></i>
                                                            Iniciar atendimento
                                                        </button>
                                                    @endif
                                                @endif

                                                <a class="dropdown-item d-flex align-items-center"
                                                    href="{{ route('profile.edit') }}">
                                                    <i class="fas fa-user me-2"></i>
                                                    Perfil
                                                </a>
                                                <div class="dropdown-divider"></div>
                                            @endif

                                            @if (auth()->check() && auth()->user()->hasRole('waiter'))
                                                <button type="button" id="requestWaiterLogoutButton"
                                                    class="dropdown-item">
                                                    {{ __('Logout') }}
                                                </button>
                                            @else
                                                <a class="dropdown-item"
                                                    href="{{ auth('admin')->check() ? route('admin.logout') : route('logout') }}"
                                                    onclick="event.preventDefault();
                                                             document.getElementById('logout-form').submit();">
                                                    {{ __('Logout') }}
                                                </a>
                                            @endif

                                            <form id="logout-form"
                                                action="{{ auth('admin')->check() ? route('admin.logout') : route('logout') }}"
                                                method="POST" class="d-none">
                                                @csrf
                                            </form>
                                        </div>
                                    </li>
                                @endif
                            @endif
                        </ul>
                    </div>
                </div>
            </nav>
        @endif

        <main style="padding: 0;">
            @yield('content')
        </main>
    </div>

    @stack('scripts')

    <script>
        // Verifica o tema salvo no localStorage
        document.addEventListener('DOMContentLoaded', function() {
            const theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', theme);
            updateThemeIcon(theme);

            // Debug: Verifica se o Bootstrap está carregado
            console.log('Bootstrap carregado:', typeof window.bootstrap !== 'undefined');

            // Se o Bootstrap estiver disponível, força a inicialização dos dropdowns
            if (typeof window.bootstrap !== 'undefined') {
                const dropdowns = document.querySelectorAll('.dropdown-toggle');
                dropdowns.forEach(dropdown => {
                    if (!window.bootstrap.Dropdown.getInstance(dropdown)) {
                        new window.bootstrap.Dropdown(dropdown);
                    }
                });
                console.log('Dropdowns inicializados:', dropdowns.length);
            }
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
                icon.className = theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
            }
        }
    </script>
</body>

</html>

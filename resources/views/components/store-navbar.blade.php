<nav class="navbar navbar-expand-md shadow-sm store-navbar">
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
                    <a class="nav-link {{ request()->routeIs('store.manage') ? 'active' : '' }}" href="{{ route('store.manage') }}">Restaurante</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('store.dashboard') ? 'active' : '' }}" href="{{ route('store.dashboard') }}">Dashboard</a>
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
                    <a class="nav-link {{ request()->routeIs('store.categories.*') ? 'active' : '' }}" href="{{ route('store.categories.index') }}">Categorias</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('store.products.*') ? 'active' : '' }}" href="{{ route('store.products.index') }}">Produtos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('store.tables.*') ? 'active' : '' }}" href="{{ route('store.tables.index') }}">Mesas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('store.employees.*') ? 'active' : '' }}" href="{{ route('store.employees.index') }}">Funcionários</a>
                </li>
                
                <!-- Dropdown Financeiro -->
                <li class="nav-item dropdown" id="financialDropdown">
                    <a id="financialToggle" class="nav-link dropdown-toggle {{ request()->routeIs('store.withdrawals.*') || request()->routeIs('store.bank-account') ? 'active' : '' }}" href="#" role="button">
                        <i class="fas fa-wallet me-1"></i> Financeiro
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="{{ route('store.bank-account') }}">
                            <i class="fas fa-university me-1"></i> Dados Bancários
                        </a>
                        <a class="dropdown-item" href="{{ route('store.withdrawals.create') }}">
                            <i class="fas fa-hand-holding-usd me-1"></i> Solicitar Saque
                        </a>
                        <a class="dropdown-item" href="{{ route('store.withdrawals.history') }}">
                            <i class="fas fa-history me-1"></i> Histórico de Saques
                        </a>
                    </div>
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
                        <a class="dropdown-item" href="{{ route('store.edit') }}">
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

@push('styles')
<style>
    /* Navbar - Nova Identidade Visual */
    .navbar {
        background: #000000 !important;
        backdrop-filter: blur(10px);
        border: none !important;
        border-bottom: 2px solid #9da1a1;
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        z-index: 1050 !important;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        width: 100%;
    }
    
    .navbar-brand, .nav-link, .navbar-toggler-icon {
        color: #e8e8e9 !important;
    }
    
    .navbar-brand:hover, .nav-link:hover {
        color: #9da1a1 !important;
    }
    
    .nav-link.active {
        color: #9da1a1 !important;
        font-weight: 600;
    }
    
    .dropdown-menu {
        background: #000000;
        backdrop-filter: blur(10px);
        border: 1px solid #9da1a1;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        z-index: 1060 !important;
    }
    
    .dropdown-item {
        color: #e8e8e9;
    }
    
    .dropdown-item:hover {
        background: #9da1a1;
        color: #000000;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dropdown functionality
    const dropdowns = document.querySelectorAll('.dropdown');
    
    dropdowns.forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        const menu = dropdown.querySelector('.dropdown-menu');
        
        if (toggle && menu) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Close other dropdowns
                dropdowns.forEach(otherDropdown => {
                    if (otherDropdown !== dropdown) {
                        otherDropdown.querySelector('.dropdown-menu')?.classList.remove('show');
                    }
                });
                
                // Toggle current dropdown
                menu.classList.toggle('show');
            });
        }
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            dropdowns.forEach(dropdown => {
                dropdown.querySelector('.dropdown-menu')?.classList.remove('show');
            });
        }
    });
    
    // Theme toggle function
    window.toggleTheme = function() {
        const body = document.body;
        const themeIcon = document.getElementById('theme-icon');
        
        if (body.classList.contains('dark-theme')) {
            body.classList.remove('dark-theme');
            themeIcon.className = 'fas fa-moon me-2';
            localStorage.setItem('theme', 'light');
        } else {
            body.classList.add('dark-theme');
            themeIcon.className = 'fas fa-sun me-2';
            localStorage.setItem('theme', 'dark');
        }
    };
    
    // Load saved theme
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-theme');
        document.getElementById('theme-icon').className = 'fas fa-sun me-2';
    }
});
</script>
@endpush

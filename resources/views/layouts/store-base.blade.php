<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    
    @stack('styles')
    
    <style>
        /* Garantir espaçamento adequado do conteúdo */
        body {
            margin: 0;
            padding: 0;
        }
        
        main.py-4 {
            padding-top: 90px !important;
        }
        
        /* Garantir que elementos principais não tenham margin-top negativo */
        .dashboard-container,
        .restaurant-edit-container,
        .edit-container,
        .sidebar-menu,
        .service-container,
        .production-container,
        .history-container {
            margin-top: 0 !important;
        }
        
        /* Resetar margens que podem causar sobreposição */
        .container-fluid,
        .container {
            margin-top: 0 !important;
        }
        
        /* Navbar - Nova Identidade Visual */
        .store-navbar {
            background: #000000 !important;
            backdrop-filter: blur(10px);
            border: none !important;
            border-bottom: 2px solid #9da1a1;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            z-index: 1050 !important;
            position: relative;
        }
        
        .store-navbar .navbar-brand, 
        .store-navbar .nav-link {
            color: #e8e8e9 !important;
            transition: all 0.3s ease;
        }
        
        .store-navbar .navbar-brand:hover, 
        .store-navbar .nav-link:hover {
            color: #9da1a1 !important;
        }
        
        .store-navbar .nav-link.active {
            color: #9da1a1 !important;
            font-weight: 600;
        }
        
        .store-navbar .dropdown-menu {
            background: #000000;
            backdrop-filter: blur(10px);
            border: 1px solid #9da1a1;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .store-navbar .dropdown-item {
            color: #e8e8e9;
            font-weight: 500;
            padding: 12px 20px;
            transition: all 0.3s ease;
        }
        
        .store-navbar .dropdown-item:hover {
            background: #9da1a1;
            color: #000000;
            transform: translateX(5px);
        }
        
        .store-navbar .dropdown-item i {
            width: 20px;
            text-align: center;
            margin-right: 8px;
        }
        
        /* Navbar toggler para mobile */
        .store-navbar .navbar-toggler {
            border: 2px solid #9da1a1;
            border-radius: 8px;
        }
        
        .store-navbar .navbar-toggler:focus {
            box-shadow: 0 0 0 3px rgba(157, 161, 161, 0.3);
        }
        
        .store-navbar .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28232, 232, 233, 0.8%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='m4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }
    </style>
</head>
<body>
    <div id="app">
        <x-store-navbar />

        <main class="py-4">
            @if(session('success'))
                <div class="container">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="container">
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
    
    @stack('scripts')
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $store->name ?? config('app.name', 'Laravel') }}</title>

    <!-- Fontes customizadas já carregadas via app.css -->

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .category-card {
            transition: transform 0.2s;
            cursor: pointer;
        }
        
        .category-card:hover {
            transform: translateY(-5px);
        }

        .product-card {
            transition: all 0.3s ease;
        }

        .product-card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .product-image {
            height: 200px;
            object-fit: cover;
        }

        .category-section {
            scroll-margin-top: 80px;
        }

        .sticky-top {
            top: 1rem;
        }
    </style>
</head>
<body class="font-sans antialiased bg-light dark:bg-dark">
    <!-- Header -->
    <nav class="navbar navbar-expand-md navbar-light bg-white dark:bg-gray-800 shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                @if($store->logo)
                    <img src="{{ asset('storage/' . $store->logo) }}" alt="{{ $store->name }}" class="h-8">
                @else
                    {{ $store->name }}
                @endif
            </a>

            <div class="ms-auto">
                <button class="btn btn-link" id="theme-toggle">
                    <i class="fas fa-moon dark:hidden"></i>
                    <i class="fas fa-sun hidden dark:inline"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="py-4 mt-5">
        <div class="container">
            @yield('content')
        </div>
    </main>

    <script>
        // Theme toggler
        document.getElementById('theme-toggle').addEventListener('click', function() {
            if (document.documentElement.getAttribute('data-bs-theme') === 'dark') {
                document.documentElement.setAttribute('data-bs-theme', 'light');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.setAttribute('data-bs-theme', 'dark');
                localStorage.setItem('theme', 'dark');
            }
        });

        // Check for saved theme preference
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', savedTheme);

        // Smooth scroll para categorias
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html> 
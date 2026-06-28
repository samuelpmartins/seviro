<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'RestSaaS') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Figtree', sans-serif;
            line-height: 1.6;
            color: #333;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .hero-content {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 80px;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 80px 20px 0 20px;
            /* leave space for fixed nav */
            position: relative;
            z-index: 2;
        }

        .hero-left {
            color: white;
        }

        .hero-logo {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }

        .hero-logo img {
            height: 50px;
            width: auto;
            margin-right: 15px;
        }

        .hero-logo h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
        }

        .hero-tag {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: inline-block;
            margin-bottom: 20px;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            line-height: 1.1;
            margin-bottom: 20px;
        }

        .hero-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 40px;
            line-height: 1.6;
        }

        /* Contact Form */
        .contact-form {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 100;
            /* ensure form stays below fixed nav */
        }

        .form-tag {
            background: #f8f9fa;
            color: #6c757d;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: inline-block;
            margin-bottom: 15px;
        }

        .form-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-control {
            width: 100%;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .radio-group {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .radio-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .radio-item input[type="radio"] {
            width: 18px;
            height: 18px;
        }

        .btn-primary {
            background: #2c3e50;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: #34495e;
            transform: translateY(-2px);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
            font-size: 0.9rem;
            color: #6c757d;
        }

        .security-info {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 15px;
            font-size: 0.8rem;
            color: #27ae60;
        }

        /* Sections */
        .section {
            padding: 80px 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
        }

        .section-subtitle {
            font-size: 1.1rem;
            color: #6c757d;
            text-align: center;
            margin-bottom: 50px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Differentials */
        .differentials {
            background: white;
        }

        .differentials-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
            margin-top: 50px;
        }

        .differential-card {
            text-align: center;
            padding: 30px 20px;
        }

        .differential-icon {
            width: 80px;
            height: 80px;
            background: #e3f2fd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
            color: #2196f3;
        }

        .differential-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
        }

        /* CTA Section */
        .cta-section {
            background: white;
        }

        .cta-content {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 40px;
            align-items: center;
            max-width: 800px;
            margin: 0 auto;
        }

        .cta-title {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .cta-subtitle {
            font-size: 1.1rem;
            color: #6c757d;
        }

        .cta-button {
            background: white;
            color: #2c3e50;
            border: 2px solid #e9ecef;
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }

        .cta-button:hover {
            background: #f8f9fa;
            border-color: #dee2e6;
            transform: translateY(-2px);
        }

        /* Stats Section */
        .stats-section {
            background: white;
        }

        .stats-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .stats-image {
            background: #f8f9fa;
            border-radius: 20px;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .stats-image::before {
            content: '';
            position: absolute;
            top: 20px;
            right: 20px;
            width: 30px;
            height: 30px;
            background: #3498db;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stats-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            position: relative;
            top: -20px;
            left: -20px;
        }

        .stats-number {
            font-size: 3rem;
            font-weight: 700;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
        }

        .stats-icon {
            width: 30px;
            height: 30px;
            background: #e3f2fd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2196f3;
        }

        .stats-description {
            font-size: 1.1rem;
            color: #6c757d;
        }

        /* How it Works */
        .how-it-works {
            background: white;
        }

        .how-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: start;
        }

        .steps-list {
            list-style: none;
        }

        .step-item {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .step-item:last-child {
            border-bottom: none;
        }

        .step-icon {
            width: 40px;
            height: 40px;
            background: #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #27ae60;
            font-size: 1.2rem;
        }

        .step-text {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
        }

        /* Footer */
        .footer {
            background: #2c3e50;
            color: white;
            padding: 40px 20px;
            text-align: center;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-text {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-content {
                grid-template-columns: 1fr;
                gap: 40px;
                text-align: center;
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .contact-form {
                padding: 30px 20px;
            }

            .differentials-grid {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .cta-content {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .stats-content {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .how-content {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .footer-content {
                flex-direction: column;
                gap: 20px;
            }
        }

        /* Navigation */
        .nav {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 2000;
            /* ensure nav stays above the contact form and other elements */
            transition: all 0.3s ease;
        }

        .nav a {
            color: white;
            text-decoration: none;
            display: inline-block;
            background-color: rgba(255, 255, 255, 0.1);
            padding: 8px 14px;
            border-radius: 8px;
            margin-left: 8px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .nav a:hover {
            background-color: rgba(255, 255, 255, 0.18);
            transform: translateY(-2px);
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="nav">
        @auth
            @role('admin')
                <a href="{{ route('stores.index') }}">Painel Admin</a>
            @endrole
            @role('store')
                <a href="{{ route('store.dashboard') }}">Meu Painel</a>
            @endrole
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                    Sair
                </a>
            </form>
        @else
            <a href="{{ route('login') }}">Entrar</a>
        @endauth
    </nav>

    <!-- Hero Section -->
    <section id="hero" class="hero">
        <div class="hero-content">
            <div class="hero-left">
                <div class="hero-logo">
                    <img src="{{ asset('storage/img/logo.png') }}" alt="SEVIRÔ">
                </div>

                {{-- <div class="hero-tag">Sistema Completo</div> --}}

                <h2 class="hero-title">Transforme seu restaurante com nossa solução digital</h2>

                <p class="hero-subtitle">
                    Gerencie cardápios, mesas e pedidos de forma simples e eficiente.
                    Aumente suas vendas e melhore a experiência dos seus clientes.
                </p>
            </div>

            <div class="contact-form">
                <div class="form-tag">Solicite uma demonstração</div>
                <h3 class="form-title">Comece sua jornada digital</h3>

                <form method="POST" action="{{ route('demo-requests.create') }}">
                    @csrf

                    <div class="form-group">
                        <label for="name"> Nome do Restaurante/Café</label>
                        <input id="name" name="name" type="text" class="form-control"
                            placeholder="Ex: Restaurante Saboroso" required>
                    </div>

                    <div class="form-group">
                        <label for="document"> CNPJ</label>
                        <input id="document" name="document" type="text" class="form-control"
                            placeholder="00.000.000/0000-00" maxlength="18" required>
                    </div>

                    <div class="form-group">
                        <label for="email"> E-mail</label>
                        <input id="email" name="email" type="email" class="form-control" placeholder="E-mail"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="phone"> Celular</label>
                        <input id="phone" name="phone" type="tel" class="form-control"
                            placeholder="(85) 99999-9999" maxlength="15" required>
                    </div>

                    <button type="submit" class="btn-primary">Solicitar Demonstração</button>

                    <div class="checkbox-group">
                        <input type="checkbox" id="terms" required>
                        <label for="terms">Aceito compartilhar essas informações</label>
                    </div>

                    <div class="security-info">
                        <i class="fas fa-shield-alt"></i>
                        <span>Seus dados estão seguros</span>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Differentials Section -->
    <section class="section differentials">
        <div class="container">
            <div class="differentials-grid">
                <div class="differential-card">
                    <div class="differential-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3 class="differential-title">Cardápio Digital</h3>
                    <p>QR Code para acesso instantâneo ao cardápio no celular do cliente</p>
                </div>

                <div class="differential-card">
                    <div class="differential-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="differential-title">Relatórios Completos</h3>
                    <p>Acompanhe vendas, produtos mais vendidos e performance em tempo real</p>
                </div>

                <div class="differential-card">
                    <div class="differential-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3 class="differential-title">Gestão Simplificada</h3>
                    <p>Interface intuitiva para gerenciar produtos, categorias e mesas facilmente</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="section cta-section">
        <div class="container">
            <div class="cta-content">
                <div>
                    <h3 class="cta-title">Uma solução completa para seu restaurante</h3>
                    <p class="cta-subtitle">Descubra como podemos ajudar seu negócio a crescer</p>
                </div>
                <a href="#hero" class="cta-button">
                    Voltar ao topo <i class="fas fa-arrow-up"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Stats Section -->


    <!-- How it Works Section -->
    <section class="section how-it-works">
        <div class="container">
            <div class="how-content">
                <div>
                    <h3 class="section-title">Como Funciona</h3>
                    <p class="section-subtitle">Implementação simples em apenas 3 passos</p>
                </div>

                <ul class="steps-list">
                    <li class="step-item">
                        <div class="step-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        <span class="step-text">Cadastre seus produtos e categorias</span>
                    </li>
                    <li class="step-item">
                        <div class="step-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        <span class="step-text">Configure suas mesas e QR Codes</span>
                    </li>
                    <li class="step-item">
                        <div class="step-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        <span class="step-text">Comece a receber pedidos digitalmente</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-text">
                © 2025 SEVIRÔ. Todos os direitos reservados.
            </div>

        </div>
    </footer>

    <script>
        const documentInput = document.getElementById('document');
        const phoneInput = document.getElementById('phone');

        documentInput.addEventListener('input', function() {

            let value = this.value.replace(/\D/g, '');

            value = value
                .replace(/^(\d{2})(\d)/, '$1.$2')
                .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
                .replace(/\.(\d{3})(\d)/, '.$1/$2')
                .replace(/(\d{4})(\d)/, '$1-$2');

            this.value = value.substring(0, 18);

        });

        phoneInput.addEventListener('input', function() {

            let value = this.value.replace(/\D/g, '');

            if (value.length <= 10) {
                value = value.replace(
                    /^(\d{2})(\d{0,4})(\d{0,4})$/,
                    (_, ddd, p1, p2) =>
                    p2 ? `(${ddd}) ${p1}-${p2}` : `(${ddd}) ${p1}`
                );
            } else {
                value = value.replace(
                    /^(\d{2})(\d{0,5})(\d{0,4})$/,
                    (_, ddd, p1, p2) =>
                    p2 ? `(${ddd}) ${p1}-${p2}` : `(${ddd}) ${p1}`
                );
            }

            this.value = value;

        });

        function validateCNPJ(cnpj) {

            cnpj = cnpj.replace(/\D/g, '');

            if (cnpj.length !== 14)
                return false;

            if (/^(\d)\1+$/.test(cnpj))
                return false;

            let length = cnpj.length - 2;
            let numbers = cnpj.substring(0, length);
            let digits = cnpj.substring(length);

            let sum = 0;
            let pos = length - 7;

            for (let i = length; i >= 1; i--) {
                sum += numbers.charAt(length - i) * pos--;

                if (pos < 2)
                    pos = 9;
            }

            let result = sum % 11 < 2 ? 0 : 11 - sum % 11;

            if (result != digits.charAt(0))
                return false;

            length += 1;
            numbers = cnpj.substring(0, length);

            sum = 0;
            pos = length - 7;

            for (let i = length; i >= 1; i--) {
                sum += numbers.charAt(length - i) * pos--;

                if (pos < 2)
                    pos = 9;
            }

            result = sum % 11 < 2 ? 0 : 11 - sum % 11;

            return result == digits.charAt(1);

        }

        document.querySelector('form').addEventListener('submit', async function(e) {

            e.preventDefault();

            const cnpj = documentInput.value.replace(/\D/g, '');
            const phone = phoneInput.value.replace(/\D/g, '');

            if (!validateCNPJ(cnpj)) {
                showToast('CNPJ inválido', 'error');
                documentInput.focus();
                return;
            }

            if (phone.length < 10 || phone.length > 11) {
                showToast('Telefone inválido', 'error');
                phoneInput.focus();
                return;
            }

            const form = this;
            const data = new FormData(form);

            data.set('document', cnpj);
            data.set('phone', phone);

            try {

                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector(
                            'input[name="_token"]'
                        ).value
                    },
                    body: data
                });

                const result = await response.json();

                if (result.success) {
                    showToast(result.message, 'success');
                    window.location.reload();
                } else {
                    showToast(result.message, 'error');
                }

            } catch {

                showToast(
                    'Ocorreu um erro ao enviar.',
                    'error'
                );
            }
        });

        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {

                const targetId = this.getAttribute('href');

                if (!targetId || targetId === '#')
                    return;

                const target = document.querySelector(targetId);

                if (!target)
                    return;

                e.preventDefault();

                window.scrollTo({
                    top: target.offsetTop - 20,
                    behavior: 'smooth'
                });

            });
        });
    </script>

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</body>

</html>

@extends('layouts.app')

@section('content')
<style>
    /* Remove margens e padding padrão do body */
    body {
        margin: 0;
        padding: 0;
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    }
    
    /* Remove navbar branca */
    .navbar {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
    }
    
    .navbar-brand, .nav-link {
        color: white !important;
    }
    
    .navbar-brand:hover, .nav-link:hover {
        color: #3498db !important;
    }
    
    /* Remove padding do main */
    main {
        padding: 0 !important;
    }
    
    .login-container {
        min-height: 100vh;
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        margin: 0;
    }
    
    .login-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        padding: 40px;
        width: 100%;
        max-width: 450px;
        position: relative;
    }
    
    .login-tag {
        background: #3498db;
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        display: inline-block;
        margin-bottom: 20px;
    }
    
    .login-title {
        color: #2c3e50;
        font-size: 28px;
        font-weight: bold;
        margin-bottom: 30px;
        line-height: 1.2;
    }
    
    .form-group {
        margin-bottom: 25px;
    }
    
    .form-label {
        color: #2c3e50;
        font-weight: 600;
        margin-bottom: 8px;
        display: block;
    }
    
    .form-control {
        border: 2px solid #ecf0f1;
        border-radius: 10px;
        padding: 15px 20px;
        font-size: 16px;
        transition: all 0.3s ease;
        background: #f8f9fa;
    }
    
    .form-control:focus {
        border-color: #3498db;
        background: white;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        outline: none;
    }
    
    .form-control.is-invalid {
        border-color: #e74c3c;
        background: #fdf2f2;
    }
    
    .invalid-feedback {
        color: #e74c3c;
        font-size: 14px;
        margin-top: 5px;
    }
    
    .remember-section {
        margin: 30px 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .form-check-input {
        width: 20px;
        height: 20px;
        border: 2px solid #bdc3c7;
        border-radius: 4px;
        margin: 0;
    }
    
    .form-check-input:checked {
        background-color: #3498db;
        border-color: #3498db;
    }
    
    .form-check-label {
        color: #2c3e50;
        font-size: 14px;
        margin: 0;
    }
    
    .login-btn {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        color: white;
        border: none;
        border-radius: 10px;
        padding: 15px 30px;
        font-size: 16px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        width: 100%;
        transition: all 0.3s ease;
        margin-bottom: 20px;
    }
    
    .login-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(44, 62, 80, 0.3);
        background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%);
    }
    
    .forgot-link {
        color: #3498db;
        text-decoration: none;
        font-size: 14px;
        text-align: center;
        display: block;
        margin-top: 10px;
    }
    
    .forgot-link:hover {
        color: #2980b9;
        text-decoration: underline;
    }
    
    .register-section {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #ecf0f1;
        text-align: center;
    }
    
    .register-text {
        color: #7f8c8d;
        font-size: 14px;
        margin-bottom: 10px;
    }
    
    .register-link {
        color: #3498db;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
    }
    
    .register-link:hover {
        color: #2980b9;
        text-decoration: underline;
    }
    
    .alert {
        border-radius: 10px;
        border: none;
        margin-bottom: 20px;
    }
    
    .alert-danger {
        background: #fdf2f2;
        color: #e74c3c;
        border-left: 4px solid #e74c3c;
    }
</style>

<div class="login-container">
    <div class="login-card">
        <div class="login-tag">Área Restrita</div>
        
        <h1 class="login-title">Fazer Login</h1>
        
        @if (session('error'))
            <div class="alert alert-danger" role="alert">
                {{ session('error') }}
            </div>
        @endif
        
        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="email" class="form-label">Endereço de Email</label>
                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Senha</label>
                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="remember-section">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label" for="remember">
                    Lembrar de mim
                </label>
            </div>

            <button type="submit" class="login-btn">
                Entrar
            </button>

            @if (Route::has('password.request'))
                <a class="forgot-link" href="{{ route('password.request') }}">
                    Esqueceu sua senha?
                </a>
            @endif
            
            <div class="register-section">
                <p class="register-text">Não tem uma conta?</p>
                <a class="register-link" href="{{ route('register') }}">
                    Cadastre-se
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

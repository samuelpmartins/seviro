@extends('layouts.app')

@section('content')
<div class="container" style="margin-top: 3rem;">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Registrar Nova Loja</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="row g-3">
                            <!-- Informações da Loja -->
                            <div class="col-md-12">
                                <h5 class="border-bottom pb-2">Informações da Loja</h5>
                            </div>

                            <div class="col-md-6">
                                <label for="name" class="form-label">Nome da Loja</label>
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="phone" class="form-label">Telefone</label>
                                <input id="phone" type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}" required>
                                @error('phone')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label for="address" class="form-label">Endereço</label>
                                <input id="address" type="text" class="form-control @error('address') is-invalid @enderror" name="address" value="{{ old('address') }}" required>
                                @error('address')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="document" class="form-label">CNPJ/CPF</label>
                                <input id="document" type="text" class="form-control @error('document') is-invalid @enderror" name="document" value="{{ old('document') }}" required>
                                @error('document')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <!-- Informações do Responsável -->
                            <div class="col-md-12 mt-4">
                                <h5 class="border-bottom pb-2">Informações do Responsável</h5>
                            </div>

                            <div class="col-md-12">
                                <label for="email" class="form-label">E-mail</label>
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="password" class="form-label">Senha</label>
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="password-confirm" class="form-label">Confirmar Senha</label>
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    Registrar Loja
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Máscara para telefone
    const phone = document.getElementById('phone');
    phone.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 11) value = value.slice(0, 11);
        if (value.length > 2) value = value.slice(0, 2) + ' ' + value.slice(2);
        if (value.length > 7) value = value.slice(0, 7) + '-' + value.slice(7);
        e.target.value = value;
    });

    // Máscara para CNPJ/CPF
    const document = document.getElementById('document');
    document.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 11) {
            // CPF
            if (value.length > 9) value = value.slice(0, 9) + '-' + value.slice(9);
            if (value.length > 6) value = value.slice(0, 6) + '.' + value.slice(6);
            if (value.length > 3) value = value.slice(0, 3) + '.' + value.slice(3);
        } else {
            // CNPJ
            if (value.length > 12) value = value.slice(0, 12) + '-' + value.slice(12);
            if (value.length > 8) value = value.slice(0, 8) + '/' + value.slice(8);
            if (value.length > 5) value = value.slice(0, 5) + '.' + value.slice(5);
            if (value.length > 2) value = value.slice(0, 2) + '.' + value.slice(2);
        }
        e.target.value = value;
    });
});
</script>
@endpush
@endsection

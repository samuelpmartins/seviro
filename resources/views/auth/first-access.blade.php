@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-header bg-white text-center">
                        <h3 class="mb-0">Redefinir senha</h3>
                        <p class="text-muted mb-0">Defina uma nova senha para continuar</p>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('first-access.update') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="password" class="form-label">Nova senha</label>
                                <input id="password" type="password"
                                    class="form-control @error('password') is-invalid @enderror" name="password" required
                                    autofocus autocomplete="new-password">
                                @error('password')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirmar nova senha</label>
                                <input id="password_confirmation" type="password" class="form-control"
                                    name="password_confirmation" required autocomplete="new-password">
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Salvar nova senha</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

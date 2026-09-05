@extends('emails.layout')

@section('content')
    <h2 style="margin:0 0 18px; color:#2c3e50;">Olá, {{ $store->name }}!</h2>

    <p>A senha de acesso do seu restaurante foi alterada pelo administrador.</p>

    <p><strong>Dados de acesso:</strong></p>
    <ul>
        <li><strong>E-mail:</strong> {{ $email }}</li>
        <li><strong>Nova senha:</strong> {{ $newPassword }}</li>
    </ul>

    <p>Acesse a plataforma em: <a href="{{ route('login') }}">{{ route('login') }}</a></p>

    <p><strong>Importante:</strong> por segurança, você deverá criar uma nova senha no próximo acesso.</p>

    <p>Atenciosamente,<br>Equipe Sevirô</p>
@endsection

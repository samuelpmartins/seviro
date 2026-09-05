@extends('emails.layout')

@section('content')
    <h2 style="margin:0 0 18px; color:#2c3e50;">Olá {{ $demoRequest->name }}!</h2>

    <p>Sua solicitação de demonstração foi aprovada e sua conta foi criada com sucesso.</p>

    <p><strong>Dados de acesso:</strong></p>
    <ul>
        <li><strong>Email:</strong> {{ $demoRequest->email }}</li>
        <li><strong>Senha temporária:</strong> {{ $tempPassword }}</li>
    </ul>

    <p>Acesse a plataforma em: <a href="{{ route('login') }}">{{ route('login') }}</a></p>

    <p><strong>Importante:</strong> no primeiro acesso, você deverá redefinir sua senha.</p>

    <p>Obrigado e bem-vindo!</p>
@endsection

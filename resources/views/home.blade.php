@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Dados do Usuário -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Meus Dados</span>
                    <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-outline-primary">
                        Editar Perfil
                    </a>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Nome:</strong> {{ $user->name }}
                    </div>
                    <div class="mb-3">
                        <strong>Email:</strong> {{ $user->email }}
                    </div>
                    <div>
                        <strong>Membro desde:</strong> {{ $user->created_at->format('d/m/Y') }}
                    </div>
                </div>
            </div>

            <!-- Mesa Atual -->
            @if($table)
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-utensils me-2"></i>Mesa Atual
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Estabelecimento:</strong> {{ $store->name }}
                        </div>
                        <div class="mb-3">
                            <strong>Mesa:</strong> {{ $table->number }}
                        </div>
                        <div class="text-center mt-4">
                            <a href="{{ route('menu.show', $table->qr_code) }}" class="btn btn-primary">
                                <i class="fas fa-book-open me-2"></i>Ver Cardápio
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="card bg-light">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-qrcode fa-3x mb-3 text-muted"></i>
                        <h5 class="text-muted">Nenhuma mesa ativa no momento</h5>
                        <p class="text-muted mb-0">
                            Escaneie um QR Code de uma mesa para ver o cardápio
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

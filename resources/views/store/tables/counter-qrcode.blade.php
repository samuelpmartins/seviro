@extends('layouts.store-base')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h1 class="h4 mb-0">QR Code - Balcão</h1>
                </div>

                <div class="card-body text-center">
                    <div class="mb-4">
                        {!! $qrCodeWithLabel !!}
                    </div>

                    <p class="mb-4">
                        <strong>URL do Menu:</strong><br>
                        <a href="{{ $url }}" target="_blank">{{ $url }}</a>
                    </p>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Este QR Code permite que clientes façam pedidos de balcão sem precisar estar vinculados a uma mesa.
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('store.tables.index') }}" class="btn btn-secondary">Voltar</a>
                        <button class="btn btn-primary" onclick="window.print()">Imprimir</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Estilo para o SVG do QR Code */
svg {
    max-width: 300px;
    height: auto;
}

@media print {
    .btn {
        display: none;
    }
    .card {
        border: none;
    }
    .card-header {
        background: none;
        border: none;
    }
    .alert {
        display: none;
    }
}
</style>
@endpush
@endsection

@extends('layouts.store-base')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h1 class="h4 mb-0">QR Code - Mesa {{ $table->number }}</h1>
                </div>

                <div class="card-body text-center">
                    <div class="mb-4">
                        {!! $qrCodeWithTableNumber !!}
                    </div>

                    <p class="mb-4">
                        <strong>URL do Menu:</strong><br>
                        <a href="{{ $url }}" target="_blank">{{ $url }}</a>
                    </p>

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
}
</style>
@endpush
@endsection 
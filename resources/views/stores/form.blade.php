@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h1 class="h4 mb-0">
                        {{ isset($store) ? 'Editar Loja' : 'Nova Loja' }}
                    </h1>
                </div>

                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(auth()->user()->hasRole('admin'))
                        <form action="{{ isset($store) ? route('stores.update', $store) : route('stores.store') }}" 
                              method="POST" 
                              enctype="multipart/form-data">
                    @else
                        <form action="{{ route('store.update') }}" 
                              method="POST" 
                              enctype="multipart/form-data">
                    @endif
                        @csrf
                        @if(isset($store))
                            @method('PUT')
                        @endif

                        <div class="row g-3">
                            <!-- Informações da Loja -->
                            <div class="col-md-12">
                                <h5 class="border-bottom pb-2">Informações da Loja</h5>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Nome da Loja</label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $store->name ?? '') }}" 
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">Telefone</label>
                                    <input type="text" 
                                           class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" 
                                           name="phone" 
                                           value="{{ old('phone', $store->phone ?? '') }}" 
                                           required>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="address">Endereço</label>
                                    <input type="text" 
                                           class="form-control @error('address') is-invalid @enderror" 
                                           id="address" 
                                           name="address" 
                                           value="{{ old('address', $store->address ?? '') }}" 
                                           required>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="document">CNPJ/CPF</label>
                                    <input type="text" 
                                           class="form-control @error('document') is-invalid @enderror" 
                                           id="document" 
                                           name="document" 
                                           value="{{ old('document', $store->document ?? '') }}" 
                                           required>
                                    @error('document')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Imagens -->
                            <div class="col-md-12 mt-4">
                                <h5 class="border-bottom pb-2">Imagens</h5>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="logo">Logo</label>
                                    <input type="file" 
                                           class="form-control @error('logo') is-invalid @enderror" 
                                           id="logo" 
                                           name="logo"
                                           accept="image/*">
                                    @error('logo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if(isset($store) && $store->logo)
                                        <div class="mt-2">
                                            <img src="{{ asset('storage/' . $store->logo) }}" 
                                                 alt="Logo atual" 
                                                 class="img-thumbnail"
                                                 style="max-height: 100px;">
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cover_image">Imagem de Capa</label>
                                    <input type="file" 
                                           class="form-control @error('cover_image') is-invalid @enderror" 
                                           id="cover_image" 
                                           name="cover_image"
                                           accept="image/*">
                                    @error('cover_image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if(isset($store) && $store->cover_image)
                                        <div class="mt-2">
                                            <img src="{{ asset('storage/' . $store->cover_image) }}" 
                                                 alt="Capa atual" 
                                                 class="img-thumbnail"
                                                 style="max-height: 100px;">
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @unless(isset($store))
                            <!-- Informações do Responsável -->
                            <div class="col-md-12 mt-4">
                                <h5 class="border-bottom pb-2">Informações do Responsável</h5>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">E-mail</label>
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email') }}" 
                                           required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">Senha</label>
                                    <input type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           id="password" 
                                           name="password" 
                                           required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            @endunless

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ isset($store) ? 'Atualizar' : 'Cadastrar' }}
                                </button>
                                @if(auth()->user()->hasRole('admin'))
                                    <a href="{{ route('stores.index') }}" class="btn btn-secondary">Voltar</a>
                                @else
                                    <a href="{{ route('store.dashboard') }}" class="btn btn-secondary">Voltar</a>
                                @endif
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
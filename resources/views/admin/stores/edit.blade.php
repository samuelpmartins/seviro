@extends('layouts.app')

@section('content')
    <style>
        .admin-store-edit {
            min-height: calc(100vh - 56px);
            padding: 3rem 1rem;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        }

        .admin-store-edit .edit-card {
            max-width: 900px;
            margin: 0 auto;
            padding: 2.5rem;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        }

        .admin-store-edit h1 {
            color: #2c3e50;
            font-size: 2rem;
            font-weight: 700;
        }

        .admin-store-edit .subtitle {
            color: #7f8c8d;
            margin-bottom: 2rem;
        }

        .admin-store-edit .form-label {
            color: #2c3e50;
            font-weight: 600;
        }

        .admin-store-edit .form-control {
            border: 2px solid #ecf0f1;
            border-radius: 10px;
            padding: 0.8rem 1rem;
            background: #f8f9fa;
        }

        .admin-store-edit .form-control:focus {
            border-color: #3498db;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .admin-store-edit .section-title {
            color: #2c3e50;
            border-bottom: 1px solid #ecf0f1;
            padding-bottom: 0.75rem;
            margin: 2rem 0 1.25rem;
        }

        .admin-store-edit .current-image {
            display: block;
            max-width: 180px;
            max-height: 140px;
            margin-top: 0.75rem;
            border: 2px solid #ecf0f1;
            border-radius: 10px;
            object-fit: cover;
        }

        @media (max-width: 576px) {
            .admin-store-edit {
                padding: 1.5rem 0.75rem;
            }

            .admin-store-edit .edit-card {
                padding: 1.5rem;
            }
        }
    </style>

    <div class="admin-store-edit">
        <div class="edit-card">
            <h1>Editar restaurante</h1>
            <p class="subtitle">Atualize os dados do estabelecimento e, se necessário, a senha de acesso.</p>

            <form method="POST" action="{{ route('admin.stores.update', $store) }}" enctype="multipart/form-data">
                @csrf

                <h5 class="section-title">Informações do restaurante</h5>

                <div class="mb-3">
                    <label for="name" class="form-label">Nome do estabelecimento</label>
                    <input type="text" id="name" name="name"
                        class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $store->name) }}"
                        required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Telefone</label>
                        <input type="text" id="phone" name="phone"
                            class="form-control @error('phone') is-invalid @enderror"
                            value="{{ old('phone', $store->phone) }}" required>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="document" class="form-label">CPF/CNPJ</label>
                        <input type="text" id="document" name="document"
                            class="form-control @error('document') is-invalid @enderror"
                            value="{{ old('document', $store->document) }}" required>
                        @error('document')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-3">
                    <label for="address" class="form-label">Endereço</label>
                    <input type="text" id="address" name="address"
                        class="form-control @error('address') is-invalid @enderror"
                        value="{{ old('address', $store->address) }}" required>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-6">
                        <label for="logo" class="form-label">Logo</label>
                        <input type="file" id="logo" name="logo"
                            class="form-control @error('logo') is-invalid @enderror" accept="image/*">
                        @if ($store->logo)
                            <img src="{{ asset('storage/' . $store->logo) }}" alt="Logo atual" class="current-image">
                        @endif
                        @error('logo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="cover_image" class="form-label">Imagem de capa</label>
                        <input type="file" id="cover_image" name="cover_image"
                            class="form-control @error('cover_image') is-invalid @enderror" accept="image/*">
                        @if ($store->cover_image)
                            <img src="{{ asset('storage/' . $store->cover_image) }}" alt="Capa atual"
                                class="current-image">
                        @endif
                        @error('cover_image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <h5 class="section-title">Acesso do restaurante</h5>
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail de acesso</label>
                    <input type="email" id="email" class="form-control"
                        value="{{ $store->user?->email ?? 'Não informado' }}" readonly>
                </div>
                <p class="text-muted small">Preencha os dois campos somente se quiser alterar a senha do usuário
                    responsável. Ao alterar, o usuário deverá criar uma nova senha no próximo acesso e receberá um e-mail
                    com a nova senha.</p>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="password" class="form-label">Nova senha</label>
                        <input type="password" id="password" name="password"
                            class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label">Confirmar nova senha</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
                            autocomplete="new-password">
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-3 mt-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-2"></i>Salvar alterações
                    </button>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary px-4">
                        <i class="fas fa-arrow-left me-2"></i>Voltar ao dashboard
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

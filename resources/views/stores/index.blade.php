@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3">Lojas</h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('stores.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nova Loja
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            @if($stores->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-store fa-3x text-muted mb-3"></i>
                    <p class="h5 text-muted">Nenhuma loja cadastrada</p>
                    <a href="{{ route('stores.create') }}" class="btn btn-primary mt-3">
                        Cadastrar Primeira Loja
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Logo</th>
                                <th>Nome</th>
                                <th>Responsável</th>
                                <th>Contato</th>
                                <th>Cadastro</th>
                                <th width="150">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stores as $store)
                                <tr>
                                    <td>
                                        @if($store->logo)
                                            <img src="{{ asset('storage/' . $store->logo) }}" 
                                                 alt="{{ $store->name }}" 
                                                 class="rounded-circle"
                                                 width="40" 
                                                 height="40"
                                                 style="object-fit: cover;">
                                        @else
                                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white"
                                                 style="width: 40px; height: 40px;">
                                                {{ strtoupper(substr($store->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $store->name }}</td>
                                    <td>{{ $store->user->name }}</td>
                                    <td>{{ $store->phone }}</td>
                                    <td>{{ $store->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('stores.edit', $store) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    onclick="confirmDelete({{ $store->id }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        <form id="delete-form-{{ $store->id }}"
                                              action="{{ route('stores.destroy', $store) }}"
                                              method="POST"
                                              class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $stores->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function confirmDelete(storeId) {
    if (confirm('Tem certeza que deseja excluir esta loja? Esta ação não pode ser desfeita.')) {
        document.getElementById('delete-form-' + storeId).submit();
    }
}
</script>
@endpush
@endsection 
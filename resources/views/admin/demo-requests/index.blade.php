@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Solicitações de Demonstração</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>CNPJ</th>
                                    <th>Email</th>
                                    <th>Telefone</th>
                                    <th>Status</th>
                                    <th>Data</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requests as $request)
                                    <tr>
                                        <td>{{ $request->name }}</td>
                                        <td>{{ $request->document }}</td>
                                        <td>{{ $request->email }}</td>
                                        <td>{{ $request->phone ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $request->isCreated() ? 'warning' : 'success' }}">
                                                @if ($request->isCreated())
                                                    Criado
                                                @elseif($request->isPending())
                                                    Em validação
                                                @else
                                                    Aprovado
                                                @endif
                                            </span>
                                        </td>
                                        <td>{{ $request->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @if ($request->isCreated())
                                                <button class="btn btn-sm btn-warning"
                                                    onclick="setPending({{ $request->id }})">
                                                    Enviar para Em validação
                                                </button>
                                            @elseif($request->isPending())
                                                <button class="btn btn-sm btn-success"
                                                    onclick="createUser({{ $request->id }})">
                                                    Criar Usuário
                                                </button>
                                            @endif

                                            <button class="btn btn-sm btn-danger"
                                                onclick="deleteRequest({{ $request->id }})">
                                                Deletar
                                            </button>

                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Nenhuma solicitação.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        {{ $requests->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function createUser(requestId) {

            fetch(`/admin/demo-requests/${requestId}/create-user`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(r => r.json())
                .then(data => {

                    if (data.success) {
                        showToast(data.message, 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 800);
                    } else {
                        showToast(data.message, 'error');
                    }

                });
        }

        function setPending(requestId) {

            fetch(`/admin/demo-requests/${requestId}/update-pending`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(r => r.json())
                .then(data => {

                    if (data.success) {
                        showToast(data.message, 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 800);
                    } else {
                        showToast(data.message, 'error');
                    }

                });
        }

        function deleteRequest(requestId) {

            fetch(`/admin/demo-requests/${requestId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(r => r.json())
                .then(data => {

                    if (data.success) {
                        location.reload();
                    }

                });
        }

        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.style.cssText = `
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 9999;
            animation: slideDown 0.3s ease;
            font-weight: 500;
        `;
            toast.textContent = message;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.animation = 'slideUp 0.3s ease';
                setTimeout(() => toast.remove(), 3000);
            }, 3000);
        }
    </script>
@endsection

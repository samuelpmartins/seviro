@extends('layouts.store-base')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">Dados Bancários</h2>
                    <p class="text-muted mb-0">Cadastre seus dados para receber os saques</p>
                </div>
                <a href="{{ route('store.dashboard') }}" class="btn btn-outline-secondary">
                    Voltar ao Dashboard
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('store.bank-account.store') }}" method="POST">
                @csrf

                <!-- Dados PIX -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Dados PIX</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="pix_key_type" class="form-label">Tipo de Chave PIX</label>
                                <select class="form-select" id="pix_key_type" name="pix_key_type">
                                    <option value="">Selecione...</option>
                                    <option value="cpf" {{ old('pix_key_type', $bankAccount->pix_key_type ?? '') == 'cpf' ? 'selected' : '' }}>CPF</option>
                                    <option value="cnpj" {{ old('pix_key_type', $bankAccount->pix_key_type ?? '') == 'cnpj' ? 'selected' : '' }}>CNPJ</option>
                                    <option value="email" {{ old('pix_key_type', $bankAccount->pix_key_type ?? '') == 'email' ? 'selected' : '' }}>E-mail</option>
                                    <option value="phone" {{ old('pix_key_type', $bankAccount->pix_key_type ?? '') == 'phone' ? 'selected' : '' }}>Telefone</option>
                                    <option value="random" {{ old('pix_key_type', $bankAccount->pix_key_type ?? '') == 'random' ? 'selected' : '' }}>Chave Aleatória</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="pix_key" class="form-label">Chave PIX</label>
                                <input type="text" class="form-control" id="pix_key" name="pix_key" 
                                       value="{{ old('pix_key', $bankAccount->pix_key ?? '') }}"
                                       placeholder="Digite sua chave PIX">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dados Bancários -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Dados Bancários Completos</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="bank_code" class="form-label">Código do Banco</label>
                                <input type="text" class="form-control" id="bank_code" name="bank_code" 
                                       value="{{ old('bank_code', $bankAccount->bank_code ?? '') }}"
                                       placeholder="Ex: 001">
                            </div>

                            <div class="col-md-9 mb-3">
                                <label for="bank_name" class="form-label">Nome do Banco</label>
                                <input type="text" class="form-control" id="bank_name" name="bank_name" 
                                       value="{{ old('bank_name', $bankAccount->bank_name ?? '') }}"
                                       placeholder="Ex: Banco do Brasil">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="agency" class="form-label">Agência</label>
                                <input type="text" class="form-control" id="agency" name="agency" 
                                       value="{{ old('agency', $bankAccount->agency ?? '') }}"
                                       placeholder="Ex: 1234">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="account_number" class="form-label">Número da Conta</label>
                                <input type="text" class="form-control" id="account_number" name="account_number" 
                                       value="{{ old('account_number', $bankAccount->account_number ?? '') }}"
                                       placeholder="Ex: 12345">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="account_digit" class="form-label">Dígito</label>
                                <input type="text" class="form-control" id="account_digit" name="account_digit" 
                                       value="{{ old('account_digit', $bankAccount->account_digit ?? '') }}"
                                       placeholder="Ex: 6">
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="account_type" class="form-label">Tipo de Conta</label>
                                <select class="form-select" id="account_type" name="account_type">
                                    <option value="">Selecione...</option>
                                    <option value="checking" {{ old('account_type', $bankAccount->account_type ?? '') == 'checking' ? 'selected' : '' }}>Conta Corrente</option>
                                    <option value="savings" {{ old('account_type', $bankAccount->account_type ?? '') == 'savings' ? 'selected' : '' }}>Conta Poupança</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="account_holder_name" class="form-label">Nome do Titular</label>
                                <input type="text" class="form-control" id="account_holder_name" name="account_holder_name" 
                                       value="{{ old('account_holder_name', $bankAccount->account_holder_name ?? '') }}"
                                       placeholder="Nome completo do titular">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="account_holder_document" class="form-label">CPF/CNPJ do Titular</label>
                                <input type="text" class="form-control" id="account_holder_document" name="account_holder_document" 
                                       value="{{ old('account_holder_document', $bankAccount->account_holder_document ?? '') }}"
                                       placeholder="Documento do titular">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info">
                    <strong>Importante:</strong> Você precisa preencher pelo menos os dados PIX <strong>OU</strong> os dados bancários completos. Pode preencher ambos se desejar.
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('store.dashboard') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Salvar Dados Bancários</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection






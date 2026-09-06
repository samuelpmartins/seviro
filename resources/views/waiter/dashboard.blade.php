@extends('layouts.app')

@section('content')
    <style>
        /* Fundo escuro */
        body {
            background: #0f0f23;
            color: #e8e8e9;
            min-height: 100vh;
            margin: 0;
        }

        /* Header do garçom */
        .waiter-header {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            padding: 20px 0;
            margin-bottom: 30px;
        }

        .waiter-header h1 {
            color: white;
            font-weight: 700;
            margin: 0;
        }

        .waiter-header .store-name {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1rem;
        }

        /* Navegação */
        .waiter-nav {
            display: flex;
            gap: 15px;
        }

        .waiter-nav a {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .waiter-nav a:hover,
        .waiter-nav a.active {
            background: white;
            color: #3498db;
        }

        /* Container */
        .waiter-container {
            padding: 0 20px 40px;
        }

        /* Grid de mesas */
        .tables-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }

        .tables-section {
            margin-bottom: 36px;
        }

        .tables-section-title {
            margin: 0 0 16px;
            color: #e8e8e9 !important;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .read-only-card {
            opacity: 0.92;
        }

        /* Card de mesa */
        .table-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }

        .table-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
        }

        .table-card.available {
            border-top: 5px solid #27ae60;
        }

        .table-card.occupied {
            border-top: 5px solid #e74c3c;
        }

        /* Header do card */
        .table-card-header {
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ecf0f1;
        }

        .table-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2c3e50;
        }

        .table-status {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .table-status.available {
            background: #d4edda;
            color: #155724;
        }

        .table-status.occupied {
            background: #f8d7da;
            color: #721c24;
        }

        /* Body do card */
        .table-card-body {
            padding: 20px;
        }

        /* Info da mesa */
        .table-info {
            margin-bottom: 15px;
        }

        .table-info-item {
            display: flex;
            align-items: center;
            padding: 8px 0;
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .table-info-item i {
            width: 24px;
            margin-right: 10px;
            color: #3498db;
        }

        .table-info-item strong {
            color: #2c3e50;
            margin-left: 5px;
        }

        .table-access-pin {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 8px;
            padding: 8px 12px;
            background: #eaf5ff;
            border: 1px solid #b9ddf7;
            border-radius: 8px;
            color: #2471a3;
            font-weight: 700;
            letter-spacing: 1px;
        }

        /* Participantes */
        .participants-list {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .participants-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }

        .participant-item {
            display: flex;
            align-items: center;
            padding: 6px 0;
            font-size: 0.85rem;
            color: #34495e;
        }

        .participant-item i {
            margin-right: 8px;
            color: #3498db;
        }

        /* Total pendente */
        .pending-total {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
        }

        .pending-total .amount {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .pending-total .label {
            font-size: 0.85rem;
            opacity: 0.9;
        }

        /* Footer do card */
        .table-card-footer {
            padding: 15px 20px;
            background: #f8f9fa;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }

        .table-card-footer.has-payment {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .table-card-footer .btn {
            min-width: 0;
            min-height: 84px;
            padding: 10px 6px;
            font-size: 0.82rem;
            font-weight: 600;
            border-radius: 10px;
            border: none;
            transition: all 0.3s ease;
            line-height: 1.25;
            white-space: normal;
            overflow-wrap: normal;
            word-break: normal;
        }

        .table-card-footer .btn i {
            display: block;
            margin: 0 0 6px !important;
        }

        .table-card-footer .btn-primary {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
        }

        .table-card-footer .btn-primary:hover {
            background: linear-gradient(135deg, #2980b9 0%, #2471a3 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
        }

        .table-card-footer .btn-danger {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
        }

        .table-card-footer .btn-danger:hover {
            background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.4);
        }

        .table-card-footer .btn-success {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
            color: white;
        }

        .table-card-footer form {
            display: contents;
        }

        .table-card-footer form .btn {
            width: 100%;
        }

        .transfer-modal {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(15, 15, 35, 0.72);
            z-index: 10002;
        }

        .transfer-modal.show {
            display: flex;
        }

        .transfer-modal-content {
            width: min(680px, 100%);
            max-height: 90vh;
            overflow-y: auto;
            padding: 28px;
            background: #fff;
            border-radius: 16px;
            color: #2c3e50;
            box-shadow: 0 12px 45px rgba(0, 0, 0, 0.45);
        }

        .transfer-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .transfer-modal-header h3 {
            margin: 0;
            font-size: 1.35rem;
        }

        .transfer-close {
            border: 0;
            background: transparent;
            color: #7f8c8d;
            font-size: 1.6rem;
            line-height: 1;
        }

        .transfer-table-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 16px;
        }

        .transfer-table-option {
            min-height: 96px;
            border: 3px solid #f39c12;
            border-radius: 14px;
            background: #fff1dc;
            color: #202a35;
            font-size: 1.15rem;
            font-weight: 700;
            line-height: 1.2;
            overflow-wrap: normal;
            word-break: normal;
            transition: all 0.2s ease;
        }

        .transfer-table-option:hover {
            background: #ffe1b5;
            transform: translateY(-2px);
            box-shadow: 0 5px 14px rgba(243, 156, 18, 0.25);
        }

        .transfer-empty {
            padding: 24px;
            border-radius: 10px;
            background: #f8f9fa;
            color: #7f8c8d;
            text-align: center;
        }

        @media (max-width: 576px) {
            .tables-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .table-card-footer {
                padding: 12px;
                gap: 8px;
            }

            .table-card-footer.has-payment {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .table-card-footer .btn {
                min-height: 78px;
                font-size: 0.8rem;
            }

            .transfer-modal {
                padding: 12px;
            }

            .transfer-modal-content {
                padding: 20px;
            }

            .transfer-table-grid {
                gap: 10px;
            }
        }

        @media (max-width: 360px) {

            .table-card-footer,
            .table-card-footer.has-payment {
                grid-template-columns: 1fr;
            }

            .table-card-footer .btn {
                min-height: 62px;
            }

            .table-card-footer .btn i {
                display: inline-block;
                margin: 0 6px 0 0 !important;
            }

            .transfer-table-grid {
                grid-template-columns: 1fr;
            }
        }

        .table-card-footer .btn-success:hover {
            background: linear-gradient(135deg, #229954 0%, #1e8449 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(39, 174, 96, 0.4);
        }


        /* Resumo dos pedidos */
        .orders-summary {
            margin-top: 15px;
        }

        .orders-summary-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }

        .order-mini {
            background: #ecf0f1;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 8px;
            font-size: 0.85rem;
        }

        .order-mini-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .order-mini-number {
            font-weight: 600;
            color: #2c3e50;
        }

        .order-mini-status {
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .order-mini-status.waiting {
            background: #f39c12;
            color: white;
        }

        .order-mini-status.production {
            background: #3498db;
            color: white;
        }

        .order-mini-status.done {
            background: #27ae60;
            color: white;
        }

        /* Estado vazio */
        .empty-table {
            text-align: center;
            padding: 20px;
            color: #bdc3c7;
        }

        .empty-table i {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        /* Botão de logout */
        .btn-logout {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 8px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-logout:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
        }

        /* Contador */
        .tables-counter {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }

        .counter-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 20px 30px;
            text-align: center;
        }

        .counter-card h3 {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
        }

        .counter-card p {
            margin: 5px 0 0;
            opacity: 0.8;
        }

        .counter-card.available h3 {
            color: #27ae60;
        }

        .counter-card.occupied h3 {
            color: #e74c3c;
        }

        /* Hover do pedido */
        .order-mini:hover {
            transform: translateX(5px);
            background: #dfe4ea !important;
        }

        /* Modal de detalhes do pedido */
        .order-details-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10001;
            animation: fadeIn 0.3s ease;
        }

        .order-details-modal.show {
            display: flex !important;
        }

        .order-details-content {
            background: white;
            border-radius: 20px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 50px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.3s ease;
        }

        .edit-product-modal {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
            background: rgba(15, 15, 35, 0.78);
            z-index: 10003;
        }

        .edit-product-modal.show {
            display: flex;
        }

        .edit-product-content {
            width: min(760px, 100%);
            max-height: 92vh;
            overflow-y: auto;
            background: #fff;
            border-radius: 18px;
            color: #2c3e50;
            box-shadow: 0 12px 50px rgba(0, 0, 0, 0.5);
        }

        .edit-product-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 22px;
            border-bottom: 1px solid #ecf0f1;
        }

        .edit-product-header h3 {
            margin: 0;
            font-size: 1.2rem;
        }

        .edit-product-close {
            border: 0;
            background: transparent;
            color: #7f8c8d;
            font-size: 1.35rem;
        }

        .edit-product-body {
            padding: 20px;
        }

        .edit-product-search {
            margin-bottom: 16px;
        }

        .edit-product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 12px;
        }

        .edit-product-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            min-height: 82px;
            padding: 14px;
            border: 1px solid #e5e9ec;
            border-radius: 12px;
            background: #f8f9fa;
            text-align: left;
            transition: all 0.2s ease;
        }

        .edit-product-card:hover {
            border-color: #3498db;
            background: #eef7fd;
            transform: translateY(-2px);
        }

        .edit-product-card strong {
            display: block;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .edit-product-card small {
            color: #27ae60;
            font-weight: 700;
        }

        .edit-product-card i {
            color: #3498db;
            font-size: 1.1rem;
        }

        .edit-product-customizer {
            display: none;
        }

        .edit-product-customizer.show {
            display: block;
        }

        .edit-product-customizer h4 {
            margin: 0 0 5px;
            color: #2c3e50;
        }

        .edit-product-price {
            margin-bottom: 18px;
            color: #27ae60;
            font-weight: 700;
        }

        .edit-product-ingredients {
            display: grid;
            gap: 9px;
            margin: 14px 0 18px;
        }

        .edit-product-ingredient {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 9px 11px;
            border-radius: 8px;
            background: #f8f9fa;
            font-size: 0.85rem;
        }

        .edit-product-ingredient input {
            width: 64px;
        }

        .edit-product-actions {
            display: flex;
            gap: 10px;
            margin-top: 18px;
        }

        .edit-product-actions .btn {
            flex: 1;
        }

        @media (max-width: 576px) {
            .edit-product-modal {
                padding: 10px;
            }

            .edit-product-body {
                padding: 14px;
            }

            .edit-product-grid {
                grid-template-columns: 1fr;
            }
        }

        .order-details-header {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 25px;
            border-radius: 20px 20px 0 0;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .order-details-header h3 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .order-details-header .order-meta {
            display: flex;
            gap: 15px;
            margin-top: 10px;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .order-details-body {
            padding: 25px;
        }

        .order-info-section {
            margin-bottom: 20px;
        }

        .order-info-section h4 {
            color: #2c3e50;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .order-info-section h4 i {
            color: #3498db;
        }

        .order-item-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: start;
        }

        .order-item-info {
            flex: 1;
        }

        .order-item-name {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .order-item-details {
            font-size: 0.85rem;
            color: #7f8c8d;
        }

        .order-item-price {
            font-weight: 700;
            color: #27ae60;
            font-size: 1.1rem;
        }

        .order-total-box {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            margin-top: 20px;
        }

        .order-total-box .label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 5px;
        }

        .order-total-box .amount {
            font-size: 2rem;
            font-weight: 700;
        }

        .order-details-footer {
            padding: 20px 25px;
            background: #f8f9fa;
            border-radius: 0 0 20px 20px;
            display: flex;
            gap: 10px;
        }

        .order-details-footer button {
            flex: 1;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-close-modal {
            background: #ecf0f1;
            color: #2c3e50;
        }

        .btn-close-modal:hover {
            background: #bdc3c7;
        }

        .attending-modal {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(15, 15, 35, 0.78);
            z-index: 10002;
        }

        .attending-modal.show {
            display: flex;
        }

        .attending-modal-content {
            width: min(100%, 420px);
            overflow: hidden;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 18px 50px rgba(0, 0, 0, 0.45);
            animation: slideUp 0.3s ease;
        }

        .attending-modal-header {
            padding: 24px;
            color: #fff;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        }

        .attending-modal-header h2 {
            margin: 0;
            font-size: 1.35rem;
            font-weight: 700;
        }

        .attending-modal-body {
            padding: 24px;
            color: #34495e;
            font-size: 1rem;
        }

        .attending-modal-actions {
            display: flex;
            gap: 12px;
            padding: 0 24px 24px;
        }

        .attending-modal-actions .btn {
            flex: 1;
            padding: 12px;
            border-radius: 10px;
            font-weight: 700;
        }

        .attending-modal-actions .btn-primary {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            border: none;
        }

        .attending-modal-actions .btn-outline-secondary {
            color: #34495e;
            border-color: #bdc3c7;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>

    <!-- Header -->
    <div class="waiter-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-concierge-bell me-3"></i>Painel do Garçom</h1>
                    <p class="store-name mb-0">{{ $store->name }}</p>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <nav class="waiter-nav">
                        <a href="{{ route('waiter.dashboard') }}" class="active">
                            <i class="fas fa-th-large me-2"></i>Mesas
                        </a>
                        <a href="{{ route('waiter.history') }}">
                            <i class="fas fa-history me-2"></i>Histórico
                        </a>
                    </nav>
                    <form id="waiterLogoutForm" action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-logout">
                            <i class="fas fa-sign-out-alt me-2"></i>Sair
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="container waiter-container">
        <!-- Contadores -->
        <div class="tables-counter">
            <div class="counter-card available">
                <h3>{{ $myTables->where('occupied', false)->count() }}</h3>
                <p>Disponíveis</p>
            </div>
            <div class="counter-card occupied">
                <h3>{{ $myTables->where('occupied', true)->count() }}</h3>
                <p>Ocupadas</p>
            </div>
        </div>

        <section class="tables-section">
            <h2 class="tables-section-title">Minhas Mesas</h2>
            <div class="tables-grid">
                @forelse ($myTables as $table)
                    <div class="table-card {{ $table->occupied ? 'occupied' : 'available' }}">
                        <div class="table-card-header">
                            <span class="table-number">Mesa {{ $table->number }}</span>
                            <span class="table-status {{ $table->occupied ? 'occupied' : 'available' }}">
                                {{ $table->occupied ? 'Ocupada' : 'Disponível' }}
                            </span>
                        </div>

                        <div class="table-card-body">
                            @if ($table->occupied)
                                <div class="table-info">
                                    @if ($table->current_user_name)
                                        <div class="table-info-item">
                                            <i class="fas fa-user"></i>
                                            Responsável: <strong>{{ $table->current_user_name }}</strong>
                                        </div>
                                    @endif
                                    @if ($table->occupied_at)
                                        <div class="table-info-item">
                                            <i class="fas fa-clock"></i>
                                            Ocupada há: <strong>{{ $table->occupied_at->diffForHumans() }}</strong>
                                        </div>
                                    @endif
                                    @if ($table->access_pin)
                                        <div class="table-access-pin">
                                            <i class="fas fa-key"></i>
                                            PIN: {{ $table->access_pin }}
                                        </div>
                                    @endif
                                </div>

                                @if ($table->participants && $table->participants->count() > 0)
                                    <div class="participants-list">
                                        <div class="participants-title">
                                            <i class="fas fa-users me-1"></i> Participantes
                                            ({{ $table->participants->count() }})
                                        </div>
                                        @foreach ($table->participants->take(5) as $participant)
                                            <div class="participant-item">
                                                <i class="fas fa-user-circle"></i>
                                                {{ $participant->name }}
                                            </div>
                                        @endforeach
                                        @if ($table->participants->count() > 5)
                                            <div class="participant-item text-muted">
                                                <i class="fas fa-ellipsis-h"></i>
                                                +{{ $table->participants->count() - 5 }} mais
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                @if ($table->orders && $table->orders->count() > 0)
                                    <div class="orders-summary">
                                        <div class="orders-summary-title">
                                            <i class="fas fa-receipt me-1"></i> Pedidos ({{ $table->orders->count() }})
                                        </div>
                                        @foreach ($table->orders->take(3) as $order)
                                            <div class="order-mini" onclick="showOrderDetails({{ $order->id }})"
                                                style="cursor: pointer; transition: transform 0.2s;">
                                                <div class="order-mini-header">
                                                    <span class="order-mini-number">
                                                        {{ $order->table ? 'Mesa ' . $order->table->number : 'Balcão' }}
                                                        @if ($order->participant)
                                                            - {{ $order->participant->name }}
                                                        @endif
                                                    </span>
                                                    <span
                                                        class="order-mini-status {{ in_array($order->status, ['Aguardando pagamento', 'Aguardando produção']) ? 'waiting' : ($order->status === 'Em produção' ? 'production' : 'done') }}">
                                                        {{ $order->status }}
                                                    </span>
                                                </div>
                                                <div class="text-muted">
                                                    {{ $order->items->sum('quantity') }} itens - R$
                                                    {{ number_format($order->total, 2, ',', '.') }}
                                                </div>
                                            </div>
                                        @endforeach
                                        @if ($table->orders->count() > 3)
                                            <div class="text-center text-muted mt-2" style="font-size: 0.85rem;">
                                                +{{ $table->orders->count() - 3 }} pedidos
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                @if ($table->unpaid_total > 0)
                                    <div class="pending-total mt-3">
                                        <div class="label">Total Pendente</div>
                                        <div class="amount">R$ {{ number_format($table->unpaid_total, 2, ',', '.') }}</div>
                                    </div>
                                @endif
                            @else
                                <div class="empty-table">
                                    <i class="fas fa-chair"></i>
                                    <p>Mesa disponível</p>
                                </div>
                            @endif
                        </div>

                        @if ($table->occupied)
                            <div class="table-card-footer {{ $table->unpaid_total > 0 ? 'has-payment' : '' }}">
                                <a href="{{ route('waiter.table-details', $table) }}" class="btn btn-primary">
                                    <i class="fas fa-eye me-2"></i>Ver Detalhes
                                </a>
                                @if ($table->unpaid_total > 0)
                                    <button type="button" class="btn btn-success"
                                        onclick="markTableAsPaid(this, {{ $table->id }})">
                                        <i class="fas fa-dollar-sign me-1"></i>Marcar Pago
                                    </button>
                                @endif
                                <button type="button" class="btn btn-warning"
                                    onclick="openTransferModal({{ $table->id }}, '{{ addslashes($table->number) }}')">
                                    <i class="fas fa-exchange-alt me-1"></i>Trocar Mesa
                                </button>
                                <form action="{{ route('waiter.table.clear', $table) }}" method="POST"
                                    onsubmit="return confirm('Tem certeza que deseja desocupar a mesa {{ $table->number }}? Isso removerá todos os participantes.');">
                                    @csrf
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-broom me-2"></i>Desocupar
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="empty-table">
                        <i class="fas fa-chair"></i>
                        <p>Nenhuma mesa sob seu atendimento.</p>
                    </div>
                @endforelse
            </div>
        </section>

        @if ($otherTables->isNotEmpty())
            <section class="tables-section">
                <h2 class="tables-section-title">Outras Mesas</h2>
                <div class="tables-grid">
                    @foreach ($otherTables as $table)
                        <div class="table-card read-only-card {{ $table->occupied ? 'occupied' : 'available' }}">
                            <div class="table-card-header">
                                <span class="table-number">Mesa {{ $table->number }}</span>
                                <span class="table-status {{ $table->occupied ? 'occupied' : 'available' }}">
                                    {{ $table->occupied ? 'Ocupada' : 'Disponível' }}
                                </span>
                            </div>
                            <div class="table-card-body">
                                <div class="table-info">
                                    <div class="table-info-item">
                                        <i class="fas fa-concierge-bell"></i>
                                        Garçom responsável:
                                        <strong>{{ $table->activeTableUser?->user?->name ?? 'Sem garçom atribuído' }}</strong>
                                    </div>
                                    @if ($table->occupied_at)
                                        <div class="table-info-item">
                                            <i class="fas fa-clock"></i>
                                            Ocupada há: <strong>{{ $table->occupied_at->diffForHumans() }}</strong>
                                        </div>
                                    @endif
                                    @if ($table->access_pin)
                                        <div class="table-access-pin">
                                            <i class="fas fa-key"></i>
                                            PIN: {{ $table->access_pin }}
                                        </div>
                                    @endif
                                </div>
                                @if ($table->participants->isNotEmpty())
                                    <div class="participants-list mb-0">
                                        <div class="participants-title">
                                            <i class="fas fa-users me-1"></i> Participantes
                                            ({{ $table->participants->count() }})
                                        </div>
                                        @foreach ($table->participants->take(5) as $participant)
                                            <div class="participant-item">
                                                <i class="fas fa-user-circle"></i>{{ $participant->name }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    <!-- Modal de Detalhes do Pedido -->
    <div id="orderDetailsModal" class="order-details-modal" onclick="closeOrderDetails(event)">
        <div class="order-details-content" onclick="event.stopPropagation()">
            <!-- O conteúdo será preenchido dinamicamente -->
        </div>
    </div>

    <div id="editProductModal" class="edit-product-modal" role="dialog" aria-modal="true"
        aria-labelledby="editProductModalTitle" onclick="closeEditProductMenu(event)">
        <div class="edit-product-content" onclick="event.stopPropagation()">
            <div class="edit-product-header">
                <h3 id="editProductModalTitle"><i class="fas fa-utensils me-2"></i>Adicionar item</h3>
                <button type="button" class="edit-product-close" aria-label="Fechar"
                    onclick="closeEditProductMenu()"><i class="fas fa-times"></i></button>
            </div>
            <div class="edit-product-body">
                <div id="editProductCatalogView">
                    <input type="search" id="editProductSearch" class="form-control edit-product-search"
                        placeholder="Buscar item no cardápio" aria-label="Buscar item no cardápio">
                    <div id="editProductGrid" class="edit-product-grid"></div>
                </div>
                <div id="editProductCustomizer" class="edit-product-customizer"></div>
            </div>
        </div>
    </div>

    <div id="transferModal" class="transfer-modal" role="dialog" aria-modal="true"
        aria-labelledby="transferModalTitle" onclick="closeTransferModal(event)">
        <div class="transfer-modal-content" onclick="event.stopPropagation()">
            <div class="transfer-modal-header">
                <h3 id="transferModalTitle"><i class="fas fa-exchange-alt me-2"></i>Trocar Mesa</h3>
                <button type="button" class="transfer-close" aria-label="Fechar" onclick="closeTransferModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <p class="text-muted mb-4">Escolha uma mesa disponível para transferir a ocupação da Mesa <strong
                    id="transferSourceNumber"></strong>.</p>
            @if ($availableTables->isNotEmpty())
                <div class="transfer-table-grid">
                    @foreach ($availableTables as $availableTable)
                        <button type="button" class="transfer-table-option"
                            onclick="confirmTableTransfer({{ $availableTable->id }}, '{{ addslashes($availableTable->number) }}')">
                            Mesa {{ $availableTable->number }}
                        </button>
                    @endforeach
                </div>
            @else
                <div class="transfer-empty">
                    <i class="fas fa-chair mb-2"></i>
                    <div>Nenhuma mesa disponível no momento.</div>
                </div>
            @endif
        </div>
    </div>

    <div id="attendingModal" class="attending-modal" role="dialog" aria-modal="true"
        aria-labelledby="attendingModalTitle">
        <div class="attending-modal-content">
            <div class="attending-modal-header">
                <h2 id="attendingModalTitle"><i class="fas fa-concierge-bell me-2"></i>Iniciar atendimento?</h2>
            </div>
            <div class="attending-modal-body">
                Ao iniciar, novas mesas poderão ser direcionadas para você.
                <div id="attendingModalError" class="text-danger small mt-3 d-none"></div>
            </div>
            <div class="attending-modal-actions">
                <button type="button" id="deferAttendingButton" class="btn btn-outline-secondary">Depois</button>
                <button type="button" id="startAttendingButton" class="btn btn-primary">Sim</button>
            </div>
        </div>
    </div>

    <div id="stopAttendingModal" class="attending-modal" role="dialog" aria-modal="true"
        aria-labelledby="stopAttendingModalTitle">
        <div class="attending-modal-content">
            <div class="attending-modal-header">
                <h2 id="stopAttendingModalTitle"><i class="fas fa-concierge-bell me-2"></i>Encerrar atendimento?</h2>
            </div>
            <div class="attending-modal-body">
                Você deixará de receber novas mesas e será desconectado.
                <div id="stopAttendingModalError" class="text-danger small mt-3 d-none"></div>
            </div>
            <div class="attending-modal-actions">
                <button type="button" id="cancelStopAttendingButton" class="btn btn-outline-secondary">Cancelar</button>
                <button type="button" id="confirmStopAttendingButton" class="btn btn-primary">Sim</button>
            </div>
        </div>
    </div>

    <script>
        const waiterProducts = @json($availableProducts);
        let isAttending = @json(auth()->user()->is_attending);
        const attendingModal = document.getElementById('attendingModal');
        const stopAttendingModal = document.getElementById('stopAttendingModal');
        const transferModal = document.getElementById('transferModal');
        const transferRouteTemplate = @json(route('waiter.table.transfer', ['table' => '__TABLE__']));
        const attendingReminderKey = 'waiter-attending-reminder-{{ auth()->id() }}';
        let attendingReminderTimer;
        let isLoggingOut = false;
        let transferSourceId = null;

        function showAttendingModal() {
            if (!isAttending) {
                attendingModal.classList.add('show');
            }
        }

        document.getElementById('openAttendingModalButton')?.addEventListener('click', function() {
            showAttendingModal();
        });

        async function showStopAttendingModal() {
            const error = document.getElementById('stopAttendingModalError');
            error.classList.add('d-none');

            try {
                const response = await fetch('{{ route('waiter.attendance-status') }}', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Cache-Control': 'no-cache',
                    },
                    cache: 'no-store',
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    throw new Error('Não foi possível validar o atendimento.');
                }

                const result = await response.json();
                if (!result.success) {
                    throw new Error(result.message || 'Não foi possível validar o atendimento.');
                }

                isAttending = result.is_attending === true;
                if (!isAttending) {
                    document.getElementById('logout-form').submit();
                    return;
                }

                stopAttendingModal.classList.add('show');
            } catch (exception) {
                error.textContent = exception.message;
                error.classList.remove('d-none');
                stopAttendingModal.classList.add('show');
            }
        }

        document.getElementById('requestWaiterLogoutButton')?.addEventListener('click', showStopAttendingModal);
        document.getElementById('waiterLogoutForm').addEventListener('submit', function(event) {
            event.preventDefault();
            showStopAttendingModal();
        });

        document.getElementById('cancelStopAttendingButton').addEventListener('click', function() {
            stopAttendingModal.classList.remove('show');
        });

        document.getElementById('confirmStopAttendingButton').addEventListener('click', async function() {
            const button = this;
            const error = document.getElementById('stopAttendingModalError');
            if (isLoggingOut) {
                return;
            }

            isLoggingOut = true;
            button.disabled = true;
            error.classList.add('d-none');

            try {
                const response = await fetch('{{ route('waiter.stop-attending') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        target_table_id: targetTableId,
                    }),
                });

                if (!response.ok) {
                    throw new Error('Não foi possível encerrar o atendimento.');
                }

                const result = await response.json();
                if (!result.success) {
                    throw new Error(result.message || 'Não foi possível encerrar o atendimento.');
                }

                isAttending = false;
                stopAttendingModal.classList.remove('show');
                document.getElementById('logout-form').submit();
            } catch (exception) {
                error.textContent = exception.message;
                error.classList.remove('d-none');
                button.disabled = false;
                isLoggingOut = false;
            }
        });

        function scheduleAttendingReminder() {
            if (isAttending) {
                return;
            }

            const nextReminderAt = Number(sessionStorage.getItem(attendingReminderKey)) || 0;
            const delay = Math.max(0, nextReminderAt - Date.now());

            window.clearTimeout(attendingReminderTimer);
            attendingReminderTimer = window.setTimeout(showAttendingModal, delay);
        }

        document.getElementById('deferAttendingButton').addEventListener('click', function() {
            attendingModal.classList.remove('show');
            sessionStorage.setItem(attendingReminderKey, String(Date.now() + 5 * 60 * 1000));
            scheduleAttendingReminder();
        });

        document.getElementById('startAttendingButton').addEventListener('click', async function() {
            const button = this;
            const error = document.getElementById('attendingModalError');
            button.disabled = true;
            error.classList.add('d-none');

            try {
                const response = await fetch('{{ route('waiter.start-attending') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    throw new Error('Não foi possível iniciar o atendimento.');
                }

                isAttending = true;
                sessionStorage.removeItem(attendingReminderKey);
                attendingModal.classList.remove('show');
            } catch (exception) {
                error.textContent = exception.message;
                error.classList.remove('d-none');
                button.disabled = false;
            }
        });

        scheduleAttendingReminder();

        function openTransferModal(tableId, tableNumber) {
            transferSourceId = tableId;
            document.getElementById('transferSourceNumber').textContent = tableNumber;
            transferModal.classList.add('show');
        }

        function closeTransferModal(event) {
            if (!event || event.target === event.currentTarget) {
                transferModal.classList.remove('show');
                transferSourceId = null;
            }
        }

        async function confirmTableTransfer(targetTableId, targetTableNumber) {
            if (!transferSourceId) {
                return;
            }

            if (!confirm(
                    `Confirma a troca para a Mesa ${targetTableNumber}? Todos os participantes, pedidos e dados da ocupação serão transferidos.`
                )) {
                return;
            }

            const sourceTableId = transferSourceId;
            const transferUrl = transferRouteTemplate.replace('__TABLE__', sourceTableId);

            try {
                const response = await fetch(transferUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        target_table_id: targetTableId,
                    }),
                });

                if (!response.ok) {
                    const result = await response.json().catch(() => ({}));
                    throw new Error(result.message || 'Não foi possível trocar a mesa.');
                }

                location.reload();
            } catch (error) {
                alert(error.message);
            }
        }

        // Atualiza a cada 30 segundos, mas preserva o modal aberto para não interromper a edição.
        function scheduleDashboardRefresh() {
            setTimeout(function() {
                const orderModal = document.getElementById('orderDetailsModal');
                const modalIsOpen = orderModal?.classList.contains('show') || attendingModal.classList.contains(
                        'show') || stopAttendingModal.classList.contains('show') || transferModal.classList
                    .contains('show');

                if (modalIsOpen) {
                    scheduleDashboardRefresh();
                    return;
                }

                location.reload();
            }, 30000);
        }

        scheduleDashboardRefresh();

        // Função para mostrar detalhes do pedido
        async function showOrderDetails(orderId) {
            try {
                const response = await fetch(`/api/orders/${orderId}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error('Erro ao buscar detalhes do pedido');
                }

                const data = await response.json();

                // Aceitar tanto data.order quanto data diretamente sendo o pedido
                const order = data.order || data;

                if (order && order.id) {
                    renderOrderDetails(order);
                    const modal = document.getElementById('orderDetailsModal');
                    if (modal) {
                        modal.classList.add('show');
                    }
                }
            } catch (error) {
                console.error('Erro ao carregar pedido:', error);
                alert('Erro ao carregar detalhes do pedido');
            }
        }

        // Função para renderizar os detalhes do pedido
        function renderOrderDetails(order) {
            const modal = document.querySelector('.order-details-content');

            if (!modal) {
                console.error('Elemento .order-details-content não encontrado!');
                return;
            }

            // Status badge color
            let statusColor = '#f39c12';
            if (order.status === 'Em produção') statusColor = '#3498db';
            if (order.status === 'Finalizado') statusColor = '#27ae60';
            if (order.status === 'Cancelado') statusColor = '#e74c3c';

            // Informações do cliente
            let clientInfo = '';
            if (order.participant) {
                clientInfo = `<i class="fas fa-user"></i> ${order.participant.name}`;
            } else if (order.user) {
                clientInfo = `<i class="fas fa-user"></i> ${order.user.name}`;
            } else {
                clientInfo = `<i class="fas fa-user-slash"></i> Cliente não identificado`;
            }

            // Mesa
            const tableInfo = order.table ? `Mesa ${order.table.number}` : 'Balcão';

            // Itens do pedido
            let itemsHtml = '';
            order.items.forEach(item => {
                const itemTotal = item.quantity * item.price;
                itemsHtml += `
                <div class="order-item-card">
                    <div class="order-item-info">
                        <div class="order-item-name">
                            ${item.quantity}x ${item.product.name}
                            ${item.product.is_quick_item ? '<span style="background: #3498db; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; margin-left: 5px;">RÁPIDO</span>' : ''}
                        </div>
                        <div class="order-item-details">
                            R$ ${parseFloat(item.price).toFixed(2).replace('.', ',')} cada
                            ${item.notes ? `<br><i class="fas fa-sticky-note"></i> ${item.notes}` : ''}
                        </div>
                    </div>
                    <div class="order-item-price">
                        R$ ${itemTotal.toFixed(2).replace('.', ',')}
                    </div>
                </div>
            `;
            });

            // Observações gerais
            let notesHtml = '';
            if (order.notes) {
                notesHtml = `
                <div class="order-info-section">
                    <h4><i class="fas fa-sticky-note"></i> Observações</h4>
                    <div style="background: #fff3cd; padding: 15px; border-radius: 8px; color: #856404;">
                        ${order.notes}
                    </div>
                </div>
            `;
            }

            modal.innerHTML = `
            <div class="order-details-header">
                <h3>Pedido #${order.order_number}</h3>
                <div class="order-meta">
                    <span><i class="fas fa-table"></i> ${tableInfo}</span>
                    <span>${clientInfo}</span>
                    <span style="background: ${statusColor}; padding: 4px 12px; border-radius: 12px; font-weight: 600;">
                        ${order.status}
                    </span>
                </div>
            </div>
            
            <div class="order-details-body">
                <div class="order-info-section">
                    <h4><i class="fas fa-receipt"></i> Itens do Pedido</h4>
                    ${itemsHtml}
                </div>
                
                ${notesHtml}
                
                <div class="order-total-box">
                    <div class="label">Total do Pedido</div>
                    <div class="amount">R$ ${parseFloat(order.total).toFixed(2).replace('.', ',')}</div>
                </div>
                
                <div style="text-align: center; margin-top: 15px; color: #95a5a6; font-size: 0.85rem;">
                    <i class="fas fa-clock"></i> Realizado ${formatDate(order.created_at)}
                </div>
            </div>
            
            <div class="order-details-footer">
                ${order.status === 'Aguardando produção' ? `
                                                                                                                <button class="btn btn-primary" onclick="renderOrderEditForm(${order.id})">
                                                                                                                    <i class="fas fa-edit me-2"></i>Editar Pedido
                                                                                                                </button>
                                                                                                            ` : ''}
                <button class="btn-close-modal" onclick="closeOrderDetails()">
                    <i class="fas fa-times me-2"></i>Fechar
                </button>
            </div>
        `;
        }

        function getEditProductIngredients(product) {
            let ingredients = product.additional_ingredients || product.additionalIngredients || product.ingredients;
            if (typeof ingredients === 'string') {
                try {
                    ingredients = JSON.parse(ingredients);
                } catch (error) {
                    ingredients = ingredients.split(',').map(name => ({
                        name: name.trim(),
                        amount_item: 0,
                        additional_price: 0
                    }));
                }
            }
            if (!Array.isArray(ingredients)) return [];
            return ingredients.map((ingredient, index) => ({
                id: ingredient.id ?? ingredient.name ?? index,
                name: ingredient.name ?? String(ingredient),
                amount_item: Number(ingredient.amount_item) || 0,
                additional_price: Number(ingredient.additional_price) || 0
            })).filter(ingredient => Number.isInteger(Number(ingredient.id)) && Number(ingredient.id) > 0);
        }

        function renderEditIngredientControls(row, selectedValues = []) {
            const product = waiterProducts.find(item => String(item.id) === String(row.querySelector('.edit-item-product')
                .value));
            const container = row.querySelector('.edit-item-ingredients');
            if (!container || !product) return;

            const selectedMap = new Map(selectedValues.map(item => [String(item.id), Number(item.selected_amount)]));
            const ingredients = getEditProductIngredients(product);
            container.innerHTML = ingredients.length ? ingredients.map(ingredient => `
                <div class="edit-ingredient" data-ingredient-id="${ingredient.id}" style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-top:6px;">
                    <span style="font-size:.8rem;color:#666;">${ingredient.name} (+R$ ${ingredient.additional_price.toFixed(2).replace('.', ',')})</span>
                    <input class="form-control form-control-sm edit-ingredient-quantity" type="number" min="0" value="${selectedMap.has(String(ingredient.id)) ? selectedMap.get(String(ingredient.id)) : ingredient.amount_item}" style="width:70px;">
                </div>
            `).join('') : '<small class="text-muted">Sem ingredientes configuráveis.</small>';
        }

        function setupEditItemRow(row, selectedValues = []) {
            renderEditIngredientControls(row, selectedValues);
            row.querySelector('.edit-item-product').addEventListener('change', () => renderEditIngredientControls(row));
        }

        function getEditSelectedIngredients(row) {
            return [...row.querySelectorAll('.edit-ingredient')].map(ingredient => ({
                id: Number(ingredient.dataset.ingredientId),
                selected_amount: Number(ingredient.querySelector('.edit-ingredient-quantity').value) || 0
            })).filter(ingredient => Number.isInteger(ingredient.id) && ingredient.id > 0);
        }

        let editProductModalOrderItems;
        let editProductSelected;

        function escapeEditProductText(value) {
            return String(value ?? '').replace(/[&<>'"]/g, character => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                "'": '&#039;',
                '"': '&quot;'
            } [character]));
        }

        function openEditProductMenu() {
            editProductModalOrderItems = document.getElementById('edit-order-items');
            editProductSelected = null;
            document.getElementById('editProductCatalogView').style.display = 'block';
            document.getElementById('editProductCustomizer').classList.remove('show');
            document.getElementById('editProductSearch').value = '';
            renderEditProductCatalog();
            document.getElementById('editProductModal').classList.add('show');
        }

        function closeEditProductMenu(event) {
            if (!event || event.target === event.currentTarget) {
                document.getElementById('editProductModal').classList.remove('show');
            }
        }

        document.getElementById('editProductSearch')?.addEventListener('input', renderEditProductCatalog);

        function renderEditProductCatalog() {
            const search = document.getElementById('editProductSearch').value.trim().toLowerCase();
            const products = waiterProducts.filter(product => product.name.toLowerCase().includes(search));
            const grid = document.getElementById('editProductGrid');

            grid.innerHTML = products.length ? products.map(product => `
                <button type="button" class="edit-product-card" onclick="openEditProductCustomizer(${product.id})">
                    <span>
                        <strong>${escapeEditProductText(product.name)}</strong>
                        <small>R$ ${Number(product.price).toFixed(2).replace('.', ',')}</small>
                    </span>
                    <i class="fas fa-plus-circle"></i>
                </button>
            `).join('') : '<div class="text-muted text-center py-4">Nenhum produto encontrado.</div>';
        }

        function openEditProductCustomizer(productId) {
            editProductSelected = waiterProducts.find(product => Number(product.id) === Number(productId));
            if (!editProductSelected) return;

            const ingredients = getEditProductIngredients(editProductSelected);
            document.getElementById('editProductCatalogView').style.display = 'none';
            const customizer = document.getElementById('editProductCustomizer');
            customizer.classList.add('show');
            customizer.innerHTML = `
                <button type="button" class="btn btn-link px-0" onclick="backToEditProductCatalog()">
                    <i class="fas fa-arrow-left me-1"></i>Voltar ao cardápio
                </button>
                <h4>${escapeEditProductText(editProductSelected.name)}</h4>
                <div class="edit-product-price">R$ ${Number(editProductSelected.price).toFixed(2).replace('.', ',')} cada</div>
                <label class="form-label">Quantidade</label>
                <input id="editProductQuantity" class="form-control" type="number" min="1" value="1">
                ${ingredients.length ? `
                            <label class="form-label mt-3">Personalize os ingredientes</label>
                            <div class="edit-product-ingredients">
                                ${ingredients.map(ingredient => `
                            <div class="edit-product-ingredient">
                                <span>${escapeEditProductText(ingredient.name)} (+R$ ${ingredient.additional_price.toFixed(2).replace('.', ',')} cada)</span>
                                <input class="form-control form-control-sm edit-product-ingredient-quantity" type="number" min="0" value="${ingredient.amount_item}" data-ingredient-id="${ingredient.id}" data-base-amount="${ingredient.amount_item}">
                            </div>
                        `).join('')}
                            </div>
                        ` : '<p class="text-muted mt-3">Este produto não possui ingredientes personalizáveis.</p>'}
                <label class="form-label mt-3">Observação</label>
                <textarea id="editProductNotes" class="form-control" rows="2" placeholder="Observação do item"></textarea>
                <div class="edit-product-actions">
                    <button type="button" class="btn btn-outline-secondary" onclick="backToEditProductCatalog()">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="addSelectedEditProduct()">
                        <i class="fas fa-plus me-1"></i>Adicionar ao pedido
                    </button>
                </div>
            `;
        }

        function backToEditProductCatalog() {
            editProductSelected = null;
            document.getElementById('editProductCatalogView').style.display = 'block';
            document.getElementById('editProductCustomizer').classList.remove('show');
        }

        function addSelectedEditProduct() {
            if (!editProductSelected || !editProductModalOrderItems) return;

            const quantity = Math.max(1, Number(document.getElementById('editProductQuantity').value) || 1);
            const notes = document.getElementById('editProductNotes').value || '';
            const selectedIngredients = [...document.querySelectorAll('.edit-product-ingredient-quantity')].map(input => ({
                id: Number(input.dataset.ingredientId),
                selected_amount: Math.max(0, Number(input.value) || 0)
            }));
            const wrapper = document.createElement('div');
            wrapper.className = 'edit-order-item';
            wrapper.style.cssText =
                'display:grid;grid-template-columns:1fr 70px 34px;gap:8px;align-items:center;margin-bottom:10px;';
            wrapper.innerHTML =
                `<select class="form-select edit-item-product">${waiterProducts.map(product =>
                `<option value="${product.id}">${escapeEditProductText(product.name)} - R$ ${Number(product.price).toFixed(2).replace('.', ',')}</option>`).join('')}</select><input class="form-control edit-item-quantity" type="number" min="1" value="${quantity}"><button type="button" class="btn btn-outline-danger edit-remove-item" title="Remover item"><i class="fas fa-trash"></i></button><textarea class="form-control edit-item-notes" rows="1" placeholder="Observação do item" style="grid-column:1 / -1;"></textarea><div class="edit-item-ingredients" style="grid-column:1 / -1;"></div>`;
            wrapper.querySelector('.edit-item-product').value = editProductSelected.id;
            wrapper.querySelector('.edit-item-notes').value = notes;
            setupEditItemRow(wrapper, selectedIngredients);
            wrapper.querySelector('.edit-remove-item').addEventListener('click', () => wrapper.remove());
            editProductModalOrderItems.appendChild(wrapper);
            closeEditProductMenu();
        }

        function renderOrderEditForm(orderId) {
            fetch(`/api/orders/${orderId}`, {
                    headers: {
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    const order = data.order || data;
                    const modal = document.querySelector('.order-details-content');
                    const productOptions = waiterProducts.map(product =>
                        `<option value="${product.id}">${product.name} - R$ ${parseFloat(product.price).toFixed(2).replace('.', ',')}</option>`
                    ).join('');
                    const itemRows = order.items.map((item, index) => `
                        <div class="edit-order-item" data-item-index="${index}" style="display:grid;grid-template-columns:1fr 70px 34px;gap:8px;align-items:center;margin-bottom:10px;">
                            <select class="form-select edit-item-product">${productOptions}</select>
                            <input class="form-control edit-item-quantity" type="number" min="1" value="${item.quantity}">
                            <button type="button" class="btn btn-outline-danger edit-remove-item" title="Remover item"><i class="fas fa-trash"></i></button>
                            <textarea class="form-control edit-item-notes" rows="1" placeholder="Observação do item" style="grid-column:1 / -1;">${item.notes || ''}</textarea>
                            <div class="edit-item-ingredients" style="grid-column:1 / -1;"></div>
                        </div>
                    `).join('');

                    modal.innerHTML = `
                        <div class="order-details-header">
                            <h3>Editar Pedido #${order.order_number}</h3>
                            <p style="margin:0;">Disponível somente enquanto estiver aguardando produção.</p>
                        </div>
                        <div class="order-details-body">
                            <div class="order-info-section">
                                <h4><i class="fas fa-receipt"></i> Itens do Pedido</h4>
                                <div id="edit-order-items">${itemRows}</div>
                                <button type="button" id="edit-add-item" class="btn btn-outline-primary w-100 mt-2">
                                    <i class="fas fa-plus me-2"></i>Adicionar item
                                </button>
                            </div>
                            <div class="order-info-section">
                                <h4><i class="fas fa-sticky-note"></i> Observações do Pedido</h4>
                                <textarea id="edit-order-notes" class="form-control" rows="3">${order.notes || ''}</textarea>
                            </div>
                        </div>
                        <div class="order-details-footer">
                            <button class="btn btn-success" onclick="saveOrderEdit(${order.id})">
                                <i class="fas fa-save me-2"></i>Salvar 
                            </button>
                            <button class="btn-close-modal" onclick="showOrderDetails(${order.id})">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </button>
                        </div>
                    `;

                    modal.querySelectorAll('.edit-order-item').forEach((row, index) => {
                        const item = order.items[index];
                        row.querySelector('.edit-item-product').value = item.product_id;
                        setupEditItemRow(row, item.selected_ingredients || []);
                    });
                    modal.querySelectorAll('.edit-remove-item').forEach(button => {
                        button.addEventListener('click', () => button.closest('.edit-order-item').remove());
                    });
                    document.getElementById('edit-add-item').addEventListener('click', openEditProductMenu);
                })
                .catch(() => alert('Erro ao carregar o pedido para edição.'));
        }

        async function saveOrderEdit(orderId) {
            const rows = [...document.querySelectorAll('.edit-order-item')];
            if (!rows.length) {
                alert('O pedido precisa ter ao menos um item.');
                return;
            }

            const items = rows.map(row => ({
                product_id: Number(row.querySelector('.edit-item-product').value),
                quantity: Number(row.querySelector('.edit-item-quantity').value),
                notes: row.querySelector('.edit-item-notes').value || null,
                selected_ingredients: getEditSelectedIngredients(row)
            }));
            const response = await fetch(`/waiter/orders/${orderId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    items,
                    notes: document.getElementById('edit-order-notes').value || null
                })
            });

            const data = await response.json();
            if (!response.ok) {
                alert(data.message || 'Não foi possível atualizar o pedido.');
                return;
            }

            alert(data.message);
            closeOrderDetails();
            location.reload();
        }

        // Função para fechar o modal
        function closeOrderDetails(event) {
            if (!event || event.target === event.currentTarget) {
                document.getElementById('orderDetailsModal').classList.remove('show');
            }
        }

        // Função para formatar data
        function formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);

            if (diffMins < 1) return 'agora mesmo';
            if (diffMins < 60) return `há ${diffMins} minuto${diffMins > 1 ? 's' : ''}`;
            if (diffHours < 24) return `há ${diffHours} hora${diffHours > 1 ? 's' : ''}`;

            return date.toLocaleDateString('pt-BR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Fechar modal com ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeOrderDetails();
                closeTransferModal();
                closeEditProductMenu();
            }
        });

        // Função para marcar mesa como paga
        function markTableAsPaid(button, tableId) {
            if (!confirm('Confirma que todos os pedidos desta mesa foram pagos em DINHEIRO?')) {
                return;
            }

            const originalHtml = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Marcando...';

            // Obter todos os pedidos não pagos da mesa
            fetch(`/api/tables/${tableId}/unpaid-orders`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    const orders = data.orders || [];
                    if (orders.length === 0) {
                        alert('Nenhum pedido pendente de pagamento nesta mesa.');
                        button.disabled = false;
                        button.innerHTML = originalHtml;
                        return;
                    }

                    // Marcar cada pedido como pago
                    const promises = orders.map(order =>
                        fetch(`/waiter/employee/${order.id}/mark-paid-cash`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content'),
                                'Accept': 'application/json'
                            },
                            credentials: 'same-origin'
                        })
                    );

                    Promise.all(promises)
                        .then(() => {
                            window.location.reload();
                        })
                        .catch(error => {
                            console.error('Erro:', error);
                            alert('Erro ao marcar pedidos como pagos. Tente novamente.');
                            button.disabled = false;
                            button.innerHTML = originalHtml;
                        });
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao buscar pedidos. Tente novamente.');
                    button.disabled = false;
                    button.innerHTML = originalHtml;
                });
        }
    </script>
@endsection

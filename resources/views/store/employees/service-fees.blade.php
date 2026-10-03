@extends('layouts.store-base')

@section('content')
    <style>
        .service-fees-container {
            padding: 20px 0;
        }

        .service-fees-title {
            color: #e8e8e9;
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
        }

        [data-bs-theme="light"] .service-fees-title {
            color: #000;
        }

        .service-fees-panel {
            background: #fff;
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .financial-icon {
            display: flex;
            width: 50px;
            height: 50px;
            flex: 0 0 50px;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 1.5rem;
        }

        .service-fee-stat {
            height: 100%;
            padding: 24px;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            background: #f8f9fa;
        }

        .service-fee-stat-label {
            color: #6b7280;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .service-fee-stat-value {
            margin: 8px 0 0;
            font-size: 2rem;
            font-weight: 700;
        }

        @media (max-width: 768px) {
            .financial-icon {
                width: 45px;
                height: 45px;
                flex-basis: 45px;
                font-size: 1.25rem;
            }

            .service-fee-stat-value {
                font-size: 1.5rem;
            }
        }
    </style>

    <div class="container service-fees-container">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <h1 class="service-fees-title">Taxa de serviço · {{ $employee->name }}</h1>
            <a href="{{ route('store.employees.index') }}" class="btn btn-outline-light">
                <i class="fas fa-arrow-left me-1"></i>Funcionários
            </a>
        </div>

        <section class="service-fees-panel">
            <div class="p-4 border-bottom">
                <h2 class="h5 mb-3 text-dark">
                    <i class="fas fa-chart-line me-2"></i>Resumo de atendimento
                </h2>
                <form method="GET" action="{{ route('store.employees.service-fees', $employee) }}"
                    class="d-flex flex-wrap align-items-end gap-2">
                    <div>
                        <label for="start_date" class="form-label text-muted small mb-1">De:</label>
                        <input type="date" id="start_date" name="start_date" value="{{ $startDate }}"
                            class="form-control form-control-sm">
                    </div>
                    <div>
                        <label for="end_date" class="form-label text-muted small mb-1">Até:</label>
                        <input type="date" id="end_date" name="end_date" value="{{ $endDate }}"
                            class="form-control form-control-sm">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-filter me-1"></i>Filtrar
                    </button>
                </form>
            </div>

            <div class="p-4">
                <p class="text-muted small mb-4">
                    Indicadores de {{ $periodLabel }}. Considera pagamentos concluídos com taxa de serviço incluída.
                </p>
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="service-fee-stat">
                            <div class="d-flex align-items-center gap-3">
                                <div class="financial-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="fas fa-receipt"></i>
                                </div>
                                <div>
                                    <div class="service-fee-stat-label">Pedidos atendidos com taxa</div>
                                    <div class="service-fee-stat-value text-primary">{{ $attendedOrderCount }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="service-fee-stat">
                            <div class="d-flex align-items-center gap-3">
                                <div class="financial-icon bg-info bg-opacity-10 text-info">
                                    <i class="fas fa-coins"></i>
                                </div>
                                <div>
                                    <div class="service-fee-stat-label">Total das taxas de serviço</div>
                                    <div class="service-fee-stat-value text-info">
                                        R$ {{ number_format($serviceFeeTotal, 2, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

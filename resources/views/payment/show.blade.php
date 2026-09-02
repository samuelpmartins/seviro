@extends('layouts.app')

@section('content')
    <div class="payment-wrapper" style="min-height: 100vh; background-color: #f8f9fa; padding-bottom: 2rem;">
        <!-- Header -->
        <div class="payment-header"
            style="background: #000; color: white; padding: 1.5rem 0; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <div class="container">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <a href="{{ route('menu.show', $qrCode) }}" class="btn"
                            style="background: rgba(255,255,255,0.1); border: none; color: white; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <div>
                            <h1 class="mb-0" style="font-size: 1.5rem; font-weight: 700;">Pagamento</h1>
                            <small style="opacity: 0.8;">
                                @if (isset($table) && $table)
                                    Mesa {{ $table->number }}
                                @else
                                    Balcão
                                @endif
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conteúdo -->
        <div class="container py-4">
            <!-- Loading State -->
            <div id="loading-state" class="text-center py-5">
                <i class="fas fa-spinner fa-spin fa-3x mb-3" style="color: #000;"></i>
                <p style="color: #666;">Carregando pedidos...</p>
            </div>

            <!-- Pedidos -->
            <div id="orders-container" style="display: none;">
                <!-- Pedidos serão carregados aqui -->
            </div>

            <!-- Métodos de Pagamento -->
            <div id="payment-methods" style="display: none;">
                <!-- Payment Element (Stripe) -->
                <div class="card" style="border-radius: 16px; border: 2px solid #f0f0f0; margin-top: 1.5rem;">
                    <div class="card-body" style="padding: 1.5rem;">
                        <h5 class="mb-4" style="font-weight: 700; color: #000;">
                            <i class="fas fa-credit-card me-2"></i>
                            Pagamento Online
                        </h5>

                        <div id="payment-element-container" style="display: none;">
                            <div id="payment-element" style="margin-bottom: 1rem;"></div>
                            <div id="payment-errors" class="text-danger mt-2 mb-2" style="font-size: 0.875rem;"></div>
                            <button id="submit-payment" class="btn w-100"
                                style="background: #000; color: white; border: none; padding: 1rem; border-radius: 12px; font-weight: 600; font-size: 1rem;">
                                <i class="fas fa-lock me-2"></i>
                                <span id="payment-button-text">Pagar <span id="payment-total">R$ 0,00</span></span>
                            </button>
                        </div>

                        <div id="select-orders-hint" class="text-center py-4" style="color: #666;">
                            <i class="fas fa-hand-pointer fa-2x mb-3" style="color: #999;"></i>
                            <p class="mb-0">Selecione os pedidos acima para pagar</p>
                        </div>
                    </div>
                </div>

                <!-- Dinheiro -->
                <div class="card" style="border-radius: 16px; border: 2px solid #f0f0f0; margin-top: 1rem;">
                    <div class="card-body" style="padding: 1.5rem;">
                        <div id="cash-payment">
                            @if ($hasWaiters)
                                <div class="d-flex align-items-center justify-content-between" id="cash-collapsed"
                                    style="cursor: pointer;">
                                    <h6 class="mb-0" style="font-weight: 700; color: #000;">
                                        <i class="fas fa-money-bill-wave me-2"></i>Pagar em Dinheiro
                                    </h6>
                                    <i class="fas fa-chevron-down" id="cash-chevron"
                                        style="color: #999; transition: transform 0.3s;"></i>
                                </div>
                                <div id="cash-details" style="display: none; margin-top: 1rem;">
                                    <div class="alert"
                                        style="background: #fff3cd; border: 2px solid #ffc107; border-radius: 8px; padding: 1rem;">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Pagamento em Dinheiro</strong>
                                        <p class="mb-0 mt-2">Ao confirmar, você está solicitando pagar em dinheiro. Um
                                            garçom virá até sua mesa para processar o pagamento.</p>
                                    </div>
                                    <button id="submit-cash" class="btn w-100 mt-3"
                                        style="background: #000; color: white; border: none; padding: 1rem; border-radius: 12px; font-weight: 600; font-size: 1rem;">
                                        <i class="fas fa-money-bill-wave me-2"></i>
                                        <span id="cash-button-text">Solicitar Pagamento em Dinheiro - <span
                                                id="cash-total">R$ 0,00</span></span>
                                    </button>
                                </div>
                            @else
                                <div class="alert mb-0"
                                    style="background: #d1ecf1; border: 2px solid #17a2b8; border-radius: 8px; padding: 1.5rem;">
                                    <i class="fas fa-store me-2" style="font-size: 1.5rem;"></i>
                                    <strong style="font-size: 1.1rem;">Pagamento em Dinheiro</strong>
                                    <p class="mb-0 mt-3" style="font-size: 1rem; line-height: 1.6;">
                                        Dirija-se ao balcão para efetuar o pagamento em dinheiro.
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .order-checkbox {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .order-card {
            transition: all 0.3s ease;
        }

        .order-card.selected {
            border-color: #000 !important;
            background: #f8f9fa !important;
        }

        @keyframes slideDown {
            from {
                transform: translate(-50%, -100%);
                opacity: 0;
            }

            to {
                transform: translate(-50%, 0);
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                transform: translate(-50%, 0);
                opacity: 1;
            }

            to {
                transform: translate(-50%, -100%);
                opacity: 0;
            }
        }
    </style>

    <script src="https://js.stripe.com/v3/"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const qrCode = '{{ $qrCode }}';
            let stripe = null;
            let elements = null;
            let paymentElement = null;
            let paymentElementReady = false;

            let selectedOrders = [];
            let totalAmount = 0;
            let allOrders = [];
            let currentParticipantId = null;

            function showToast(message, type = 'info') {
                const toast = document.createElement('div');
                toast.style.cssText = `
            position: fixed; top: 20px; left: 50%; transform: translateX(-50%);
            background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
            color: white; padding: 1rem 1.5rem; border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 9999;
            animation: slideDown 0.3s ease; font-weight: 500; max-width: 90%;
        `;
                toast.textContent = message;
                document.body.appendChild(toast);
                setTimeout(() => {
                    toast.style.animation = 'slideUp 0.3s ease';
                    setTimeout(() => toast.remove(), 300);
                }, 3000);
            }

            function showThankYouModal(message) {
                const modal = document.createElement('div');
                modal.style.cssText = `
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.8); display: flex; align-items: center;
            justify-content: center; z-index: 10000;
        `;
                modal.innerHTML = `
            <div style="background: white; padding: 3rem; border-radius: 20px; text-align: center; max-width: 90%; width: 400px;">
                <div style="width: 80px; height: 80px; background: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <i class="fas fa-check" style="font-size: 2.5rem; color: white;"></i>
                </div>
                <h3 style="color: #000; font-weight: 700; margin-bottom: 1rem;">Pagamento Confirmado!</h3>
                <p style="color: #666; font-size: 1.1rem; margin-bottom: 2rem;">${message}</p>
                <button onclick="window.location.href='/menu/${qrCode}'" class="btn" style="background: #000; color: white; border: none; padding: 1rem 2rem; border-radius: 12px; font-weight: 600; font-size: 1rem; cursor: pointer; width: 100%;">
                    Voltar ao Cardápio
                </button>
            </div>
        `;
                document.body.appendChild(modal);
            }

            // --- Stripe Payment Element ---

            function initializePaymentElement(amountInCents) {
                if (!stripe) {
                    stripe = Stripe('{{ config('services.stripe.key') }}');
                }

                if (elements) {
                    elements.update({
                        amount: amountInCents
                    });
                    return;
                }

                elements = stripe.elements({
                    mode: 'payment',
                    currency: 'brl',
                    amount: amountInCents,
                    locale: 'pt-BR',
                    appearance: {
                        theme: 'stripe',
                        variables: {
                            colorPrimary: '#000000',
                            borderRadius: '8px',
                            fontFamily: 'system-ui, -apple-system, sans-serif',
                        },
                    },
                });

                paymentElement = elements.create('payment', {
                    layout: {
                        type: 'tabs',
                        defaultCollapsed: false,
                    },
                });
                paymentElement.mount('#payment-element');

                paymentElement.on('ready', function() {
                    paymentElementReady = true;
                });
            }

            function showPaymentElement() {
                if (totalAmount > 0) {
                    const amountInCents = Math.round(totalAmount * 100);
                    initializePaymentElement(amountInCents);
                    document.getElementById('payment-element-container').style.display = 'block';
                    document.getElementById('select-orders-hint').style.display = 'none';
                } else {
                    document.getElementById('payment-element-container').style.display = 'none';
                    document.getElementById('select-orders-hint').style.display = 'block';
                }
            }

            // --- Carregar pedidos ---

            loadOrders();

            function loadOrders() {
                fetch(`/api/payment/${qrCode}/orders`)
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            allOrders = data.orders;
                            currentParticipantId = data.current_participant_id || null;
                            renderOrders(data.orders);
                            document.getElementById('loading-state').style.display = 'none';
                            document.getElementById('orders-container').style.display = 'block';
                        } else {
                            document.getElementById('loading-state').innerHTML = `
                        <i class="fas fa-exclamation-circle fa-3x mb-3" style="color: #ef4444;"></i>
                        <p style="color: #666;">${data.message || 'Erro ao carregar pedidos'}</p>
                        <a href="/menu/${qrCode}" class="btn" style="background: #000; color: white; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; display: inline-block;">Voltar ao Cardápio</a>
                    `;
                        }
                    })
                    .catch(error => {
                        document.getElementById('loading-state').innerHTML = `
                    <i class="fas fa-exclamation-circle fa-3x mb-3" style="color: #ef4444;"></i>
                    <p style="color: #666;">Erro ao carregar pedidos: ${error.message}</p>
                    <a href="/menu/${qrCode}" class="btn" style="background: #000; color: white; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; display: inline-block;">Voltar ao Cardápio</a>
                `;
                    });
            }

            function renderOrders(ordersData) {
                const container = document.getElementById('orders-container');

                if (!ordersData || ordersData.length === 0) {
                    container.innerHTML = `
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-3x mb-3" style="color: #10b981;"></i>
                    <h4 style="color: #000;">Nenhum pedido pendente</h4>
                    <p style="color: #666;">Todos os pedidos foram pagos!</p>
                    <a href="/menu/${qrCode}" class="btn mt-3" style="background: #000; color: white; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; display: inline-block;">Voltar ao Cardápio</a>
                </div>
            `;
                    return;
                }

                let html =
                    '<h5 class="mb-3" style="font-weight: 700; color: #000;">Selecione os pedidos para pagar:</h5>';

                if (currentParticipantId) {
                    html += `
                <div class="alert" style="background: #e3f2fd; border: 2px solid #2196f3; border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem;">
                    <i class="fas fa-info-circle me-2" style="color: #2196f3;"></i>
                    <strong style="color: #1976d2;">Você pode pagar pelos pedidos de outras pessoas!</strong>
                    <p class="mb-0 mt-1" style="color: #1565c0; font-size: 0.875rem;">
                        Seus pedidos foram selecionados automaticamente, mas você pode adicionar ou remover qualquer pedido não pago da mesa.
                    </p>
                </div>
            `;
                } else {
                    html += `
                <div class="alert" style="background: #e3f2fd; border: 2px solid #2196f3; border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem;">
                    <i class="fas fa-info-circle me-2" style="color: #2196f3;"></i>
                    <strong style="color: #1976d2;">Selecione qualquer pedido para pagar!</strong>
                    <p class="mb-0 mt-1" style="color: #1565c0; font-size: 0.875rem;">
                        Você pode pagar por um ou mais pedidos da mesa, incluindo pedidos de outras pessoas.
                    </p>
                </div>
            `;
                }

                ordersData.forEach(participantGroup => {
                    html += `
                <div class="mb-4">
                    <h6 style="color: #666; font-weight: 600; margin-bottom: 1rem;">
                        <i class="fas fa-user me-2"></i>${participantGroup.participant_name}
                    </h6>
            `;

                    participantGroup.orders.forEach(order => {
                        const isOwnOrder = currentParticipantId && participantGroup
                            .participant_id == currentParticipantId;
                        const ownOrderBadge = isOwnOrder ?
                            '<span style="background: #10b981; color: white; padding: 0.25rem 0.5rem; border-radius: 6px; font-size: 0.75rem; font-weight: 600; margin-left: 0.5rem;">Seu pedido</span>' :
                            '';

                        html += `
                    <div class="card order-card mb-3" data-order-id="${order.id}" style="border-radius: 12px; border: 2px solid #e0e0e0; cursor: pointer;">
                        <div class="card-body" style="padding: 1rem;">
                            <div class="d-flex align-items-start gap-3">
                                <input type="checkbox" class="order-checkbox" data-order-id="${order.id}" data-amount="${order.total}">
                                <div style="flex: 1;">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1" style="font-weight: 700; color: #000;">
                                                ${order.table_display} - ${participantGroup.participant_name}
                                                ${ownOrderBadge}
                                            </h6>
                                            <small style="color: #666;">${order.created_at}</small>
                                        </div>
                                        <strong style="color: #000; font-size: 1.1rem;">R$ ${parseFloat(order.total).toFixed(2).replace('.', ',')}</strong>
                                    </div>
                                    <div class="order-items">
                                        ${order.items.map(item => `
                                                <div style="font-size: 0.875rem; color: #666;">
                                                    ${item.quantity}x ${item.product_name}
                                                </div>
                                            `).join('')}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                    });

                    html += '</div>';
                });

                container.innerHTML = html;
                document.getElementById('payment-methods').style.display = 'block';

                document.querySelectorAll('.order-checkbox').forEach(checkbox => {
                    checkbox.addEventListener('change', handleOrderSelection);
                });

                document.querySelectorAll('.order-card').forEach(card => {
                    card.addEventListener('click', function(e) {
                        if (e.target.classList.contains('order-checkbox')) return;
                        const checkbox = this.querySelector('.order-checkbox');
                        checkbox.checked = !checkbox.checked;
                        checkbox.dispatchEvent(new Event('change'));
                    });
                });

                if (currentParticipantId) {
                    ordersData.forEach(participantGroup => {
                        if (participantGroup.participant_id == currentParticipantId) {
                            participantGroup.orders.forEach(order => {
                                const checkbox = document.querySelector(
                                    `.order-checkbox[data-order-id="${order.id}"]`);
                                if (checkbox) {
                                    checkbox.checked = true;
                                    checkbox.dispatchEvent(new Event('change'));
                                }
                            });
                        }
                    });
                }
            }

            function handleOrderSelection(e) {
                const orderId = parseInt(e.target.dataset.orderId);
                const amount = parseFloat(e.target.dataset.amount);
                const card = e.target.closest('.order-card');

                if (e.target.checked) {
                    selectedOrders.push(orderId);
                    totalAmount += amount;
                    card.classList.add('selected');
                } else {
                    selectedOrders = selectedOrders.filter(id => id !== orderId);
                    totalAmount -= amount;
                    card.classList.remove('selected');
                }

                updateTotalDisplay();
                showPaymentElement();
            }

            function updateTotalDisplay() {
                const formatted = `R$ ${totalAmount.toFixed(2).replace('.', ',')}`;
                document.getElementById('payment-total').textContent = formatted;
                const cashTotal = document.getElementById('cash-total');
                if (cashTotal) cashTotal.textContent = formatted;

                const hasSelection = selectedOrders.length > 0;
                document.getElementById('submit-payment').disabled = !hasSelection;
                const submitCash = document.getElementById('submit-cash');
                if (submitCash) submitCash.disabled = !hasSelection;
            }

            // --- Cash toggle ---
            const cashCollapsed = document.getElementById('cash-collapsed');
            if (cashCollapsed) {
                cashCollapsed.addEventListener('click', function() {
                    const details = document.getElementById('cash-details');
                    const chevron = document.getElementById('cash-chevron');
                    if (details.style.display === 'none') {
                        details.style.display = 'block';
                        chevron.style.transform = 'rotate(180deg)';
                    } else {
                        details.style.display = 'none';
                        chevron.style.transform = 'rotate(0deg)';
                    }
                });
            }

            // --- Submit Payment (Payment Element) ---

            document.getElementById('submit-payment').addEventListener('click', async function() {
                if (selectedOrders.length === 0) {
                    showToast('Selecione pelo menos um pedido', 'error');
                    return;
                }

                if (!stripe || !elements) {
                    showToast('Stripe não foi inicializado. Tente novamente.', 'error');
                    return;
                }

                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processando...';

                const errorsDiv = document.getElementById('payment-errors');
                errorsDiv.textContent = '';

                try {
                    const {
                        error: submitError
                    } = await elements.submit();
                    if (submitError) {
                        errorsDiv.textContent = submitError.message;
                        this.disabled = false;
                        this.innerHTML =
                            '<i class="fas fa-lock me-2"></i><span id="payment-button-text">Pagar <span id="payment-total">R$ ' +
                            totalAmount.toFixed(2).replace('.', ',') + '</span></span>';
                        return;
                    }

                    const response = await fetch('/api/payment/create-intent', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .content
                        },
                        body: JSON.stringify({
                            qr_code: qrCode,
                            order_ids: selectedOrders,
                        })
                    });

                    const data = await response.json();

                    if (!data.success) {
                        throw new Error(data.message || 'Erro ao criar pagamento');
                    }

                    const {
                        error
                    } = await stripe.confirmPayment({
                        elements,
                        clientSecret: data.client_secret,
                        confirmParams: {
                            return_url: window.location.origin + '/payment/' + qrCode +
                                '/complete',
                        },
                        redirect: 'if_required',
                    });

                    if (error) {
                        throw new Error(error.message);
                    }

                    const confirmResponse = await fetch('/api/payment/confirm', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .content
                        },
                        body: JSON.stringify({
                            payment_intent_id: data.payment_intent_id,
                        })
                    });

                    const confirmData = await confirmResponse.json();

                    if (confirmData.success) {
                        if (confirmData.table_cleared && confirmData.thank_you_message) {
                            showThankYouModal(confirmData.thank_you_message);
                        } else {
                            showToast('Pagamento realizado com sucesso!', 'success');
                            setTimeout(() => location.reload(), 1500);
                        }
                    } else {
                        throw new Error(confirmData.message || 'Erro ao confirmar pagamento');
                    }

                } catch (error) {
                    console.error('Erro:', error);
                    errorsDiv.textContent = error.message;
                    this.disabled = false;
                    this.innerHTML =
                        '<i class="fas fa-lock me-2"></i><span id="payment-button-text">Pagar <span id="payment-total">R$ ' +
                        totalAmount.toFixed(2).replace('.', ',') + '</span></span>';
                }
            });

            // --- Cash payment ---
            const submitCashBtn = document.getElementById('submit-cash');
            if (submitCashBtn) {
                submitCashBtn.addEventListener('click', async function() {
                    if (selectedOrders.length === 0) {
                        showToast('Selecione pelo menos um pedido', 'error');
                        return;
                    }

                    if (!confirm('Confirmar solicitação de pagamento em dinheiro?')) {
                        return;
                    }

                    this.disabled = true;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processando...';

                    try {
                        const response = await fetch('/api/payment/request-cash', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                qr_code: qrCode,
                                order_ids: selectedOrders,
                                amount: totalAmount
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            showToast('Solicitação enviada! Um garçom virá até sua mesa.', 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            throw new Error(data.message || 'Erro ao solicitar pagamento');
                        }

                    } catch (error) {
                        console.error('Erro:', error);
                        showToast(error.message, 'error');
                        this.disabled = false;
                        this.innerHTML =
                            '<i class="fas fa-money-bill-wave me-2"></i>Solicitar Pagamento em Dinheiro - R$ ' +
                            totalAmount.toFixed(2).replace('.', ',');
                    }
                });
            }

            updateTotalDisplay();
        });
    </script>
@endsection

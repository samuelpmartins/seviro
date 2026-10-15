@push('styles')
    <style>
        .pay-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 2000;
            background: rgba(15, 18, 35, 0.75);
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .pay-overlay.open {
            display: flex;
        }

        .pay-modal {
            background: #fff;
            color: #24364b;
            width: 100%;
            max-width: 440px;
            max-height: 94vh;
            overflow-y: auto;
            border-radius: 16px;
            padding: 22px;
        }

        .pay-modal h4 {
            margin: 0;
            font-weight: 800;
            color: #111a33;
        }

        .pay-sub {
            color: #7f8c9d;
            margin-bottom: 14px;
        }

        .pay-close {
            background: none;
            border: 0;
            font-size: 1.4rem;
            color: #5b6b82;
        }

        .pay-box {
            background: #f4f6f9;
            border-radius: 12px;
            padding: 14px;
            margin-bottom: 12px;
        }

        .pay-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .pay-summary strong.big {
            font-size: 1.5rem;
            color: #111a33;
        }

        .pay-seg {
            display: grid;
            grid-template-columns: 1fr 1fr;
            border: 1px solid #dfe4ea;
            border-radius: 10px;
            overflow: hidden;
            margin: 10px 0;
        }

        .pay-seg button {
            border: 0;
            padding: 10px;
            background: #fff;
            font-weight: 700;
        }

        .pay-seg button.active {
            background: linear-gradient(135deg, #f9a11b, #e67e22);
            color: #fff;
        }

        .pay-adjust-row {
            display: flex;
            gap: 10px;
        }

        .pay-adjust-row input {
            flex: 1;
            border: 1px solid #dfe4ea;
            border-radius: 10px;
            padding: 10px;
            font-weight: 700;
        }

        .pay-apply {
            border: 1px solid #f39c12;
            color: #e67e22;
            background: #fff;
            border-radius: 10px;
            padding: 0 18px;
            font-weight: 700;
        }

        .pay-fee-toggle {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .pay-fee-toggle input {
            width: 20px;
            height: 20px;
            accent-color: #e67e22;
        }

        .pay-line {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
        }

        .pay-line.sep {
            border-top: 1px solid #e3e7ec;
            margin-top: 6px;
            padding-top: 10px;
        }

        .pay-total {
            background: linear-gradient(135deg, #f9a11b, #e67e22);
            color: #fff;
            border-radius: 12px;
            text-align: center;
            padding: 14px;
            margin: 12px 0;
        }

        .pay-total strong {
            display: block;
            font-size: 1.9rem;
        }

        .pay-actions {
            display: grid;
            grid-template-columns: 1fr 1.4fr;
            gap: 10px;
        }

        .pay-actions .btn-confirm {
            background: linear-gradient(135deg, #f9a11b, #e67e22);
            color: #fff;
            border: 0;
            border-radius: 10px;
            padding: 12px;
            font-weight: 700;
        }

        .pay-actions .btn-back {
            background: #fff;
            border: 1px solid #c9d1dc;
            border-radius: 10px;
            padding: 12px;
            font-weight: 700;
            color: #111a33;
        }

        .pay-error {
            color: #c0392b;
            font-size: 0.9rem;
            margin-top: 6px;
        }
    </style>
@endpush

<div id="paymentModal" class="pay-overlay" role="dialog" aria-modal="true">
    <div class="pay-modal">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h4>Resumo da mesa</h4>
                <div class="pay-sub" id="payTableLabel"></div>
            </div>
            <button type="button" class="pay-close" onclick="closePaymentModal()" aria-label="Fechar">&times;</button>
        </div>

        <div class="pay-box pay-summary">
            <div>
                <div><strong id="payCounts"></strong></div>
                <small class="text-muted">Consumo total</small>
            </div>
            <strong class="big" id="payConsumptionHead"></strong>
        </div>

        <div class="pay-box">
            <strong>Desconto</strong>
            <div class="pay-sub mb-0">Ajuste o valor do pedido</div>
            <div class="pay-seg">
                <button type="button" class="active" data-type="discount"
                    onclick="setPaymentAdjustType('discount')">Descontar</button>
                <button type="button" data-type="surcharge"
                    onclick="setPaymentAdjustType('surcharge')">Acrescentar</button>
            </div>
            <label class="pay-sub mb-1" for="payAdjustInput">Valor do ajuste</label>
            <div class="pay-adjust-row">
                <input type="text" id="payAdjustInput" inputmode="decimal" placeholder="R$ 0,00">
                <button type="button" class="pay-apply" onclick="applyPaymentAdjust()">Aplicar</button>
            </div>
            <div class="pay-error" id="payError"></div>
        </div>

        <div class="pay-box">
            <label class="pay-fee-toggle">
                <input type="checkbox" id="payFeeCheckbox" onchange="refreshPaymentSummary()">
                <span>
                    <strong>Incluir taxa de serviço (10%)</strong><br>
                    <small class="text-muted">Calculada sobre o valor após o ajuste</small>
                </span>
            </label>
        </div>

        <div>
            <div class="pay-line"><span>Consumo (todos os pedidos)</span><strong id="payConsumption"></strong></div>
            <div class="pay-line"><span id="payAdjustLabel">Ajuste aplicado</span><strong id="payAdjust"></strong></div>
            <div class="pay-line sep"><span>Subtotal</span><strong id="paySubtotal"></strong></div>
            <div class="pay-line"><span>Taxa de serviço (10%)</span><strong id="payFee"></strong></div>
        </div>

        <div class="pay-total">
            <span>Total do pedido</span>
            <strong id="payTotal"></strong>
        </div>

        <div class="pay-actions">
            <button type="button" class="btn-back" onclick="closePaymentModal()">Voltar</button>
            <button type="button" class="btn-confirm" id="payConfirmButton" onclick="confirmPayment()">Confirmar
                pedido</button>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        const paymentState = {
            feeUrl: null,
            payUrl: null,
            type: 'discount',
            amount: 0
        };
        const payCurrency = value => `R$ ${Number(value).toFixed(2).replace('.', ',')}`;
        const payHeaders = () => ({
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        });

        function parsePayAmount(text) {
            let value = String(text).replace(/[^\d,.]/g, '');
            if (value.includes(',')) value = value.replace(/\./g, '').replace(',', '.');
            const number = parseFloat(value);
            return Number.isFinite(number) && number > 0 ? Math.round(number * 100) / 100 : 0;
        }

        function paymentPayload() {
            return {
                include_service_fee: document.getElementById('payFeeCheckbox').checked,
                adjustment_type: paymentState.amount > 0 ? paymentState.type : null,
                adjustment_amount: paymentState.amount > 0 ? paymentState.amount : null
            };
        }

        function openPaymentModal(button) {
            paymentState.feeUrl = button.dataset.serviceFeeUrl;
            paymentState.payUrl = button.dataset.paymentUrl;
            paymentState.type = 'discount';
            paymentState.amount = 0;
            document.getElementById('payAdjustInput').value = '';
            document.getElementById('payFeeCheckbox').checked = false;
            document.getElementById('payError').textContent = '';
            setPaymentAdjustType('discount', false);
            document.getElementById('paymentModal').classList.add('open');
            refreshPaymentSummary();
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').classList.remove('open');
        }

        function setPaymentAdjustType(type, refresh = true) {
            paymentState.type = type;
            document.querySelectorAll('.pay-seg button').forEach(btn =>
                btn.classList.toggle('active', btn.dataset.type === type));
            if (refresh && paymentState.amount > 0) refreshPaymentSummary();
        }

        function applyPaymentAdjust() {
            paymentState.amount = parsePayAmount(document.getElementById('payAdjustInput').value);
            refreshPaymentSummary();
        }

        async function refreshPaymentSummary() {
            const errorBox = document.getElementById('payError');
            errorBox.textContent = '';

            try {
                const response = await fetch(paymentState.feeUrl, {
                    method: 'POST',
                    headers: payHeaders(),
                    credentials: 'same-origin',
                    body: JSON.stringify(paymentPayload())
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Não foi possível calcular o total.');

                const names = (data.participants || []).join(', ');
                document.getElementById('payTableLabel').textContent =
                    `Mesa ${data.table_number}${names ? ' • ' + names : ''}`;
                document.getElementById('payCounts').textContent =
                    `${data.orders_count} pedido${data.orders_count === 1 ? '' : 's'} • ${data.items_count} iten${data.items_count === 1 ? '' : 's'}`;
                document.getElementById('payConsumptionHead').textContent = payCurrency(data.consumption);
                document.getElementById('payConsumption').textContent = payCurrency(data.consumption);
                document.getElementById('payAdjustLabel').textContent =
                    data.adjustment_type === 'surcharge' ? 'Acréscimo aplicado' : 'Desconto aplicado';
                document.getElementById('payAdjust').textContent =
                    `${data.adjustment < 0 ? '− ' : data.adjustment > 0 ? '+ ' : ''}${payCurrency(Math.abs(data.adjustment))}`;
                document.getElementById('paySubtotal').textContent = payCurrency(data.subtotal);
                document.getElementById('payFee').textContent = `+ ${payCurrency(data.service_fee_applied)}`;
                document.getElementById('payTotal').textContent = payCurrency(data.total);
            } catch (error) {
                paymentState.amount = 0;
                errorBox.textContent = error.message;
            }
        }

        function confirmPayment() {
            const button = document.getElementById('payConfirmButton');
            const originalText = button.textContent;
            button.disabled = true;
            button.textContent = 'Registrando...';

            fetch(paymentState.payUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: payHeaders(),
                    body: JSON.stringify(paymentPayload())
                })
                .then(async response => {
                    const data = await response.json();
                    if (!response.ok) throw new Error(data.message || 'Erro ao registrar o pagamento.');
                    window.location.reload();
                })
                .catch(error => {
                    document.getElementById('payError').textContent = error.message;
                    button.disabled = false;
                    button.textContent = originalText;
                });
        }

        document.addEventListener('keydown', event => {
            if (event.key === 'Escape') closePaymentModal();
        });
    </script>
@endpush

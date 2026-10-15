<style>
    .ov-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 2000;
        background: rgba(15, 18, 35, 0.75);
        align-items: center;
        justify-content: center;
        padding: 16px;
    }

    .ov-overlay.open {
        display: flex;
    }

    .ov-modal {
        background: #fff;
        color: #24364b;
        width: 100%;
        max-width: 440px;
        max-height: 94vh;
        overflow-y: auto;
        border-radius: 16px;
        padding: 22px;
    }

    .ov-modal h4 {
        margin: 0;
        font-weight: 800;
        color: #111a33;
    }

    .ov-sub {
        color: #7f8c9d;
        margin-bottom: 14px;
    }

    .ov-close {
        background: none;
        border: 0;
        font-size: 1.4rem;
        color: #5b6b82;
    }

    .ov-box {
        background: #eef2f8;
        border-radius: 12px;
        padding: 14px;
        margin-bottom: 12px;
    }

    .ov-box small.label {
        display: block;
        font-weight: 700;
        color: #4a5b75;
    }

    .ov-box .name {
        font-size: 1.15rem;
        font-weight: 800;
        color: #111a33;
    }

    .ov-warning {
        background: #fff6e0;
        border: 1px solid #f5c76a;
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 0.88rem;
        color: #7a5200;
        margin-bottom: 12px;
    }

    .ov-input {
        width: 100%;
        border: 1px solid #c9d1dc;
        border-radius: 10px;
        padding: 12px;
        font-size: 1.05rem;
        font-weight: 700;
        margin-bottom: 12px;
    }

    .ov-change {
        background: #e3efff;
        border-radius: 12px;
        padding: 14px;
        margin-bottom: 12px;
    }

    .ov-change strong.values {
        display: block;
        font-size: 1.3rem;
        color: #1558d6;
    }

    .ov-error {
        color: #c0392b;
        font-size: 0.9rem;
        margin-bottom: 8px;
    }

    .ov-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .ov-actions button {
        border-radius: 10px;
        padding: 12px;
        font-weight: 700;
    }

    .ov-actions .cancel {
        background: #fff;
        border: 1px solid #c9d1dc;
        color: #111a33;
    }

    .ov-actions .save {
        background: #1558d6;
        border: 0;
        color: #fff;
    }
</style>

<div id="orderValueModal" class="ov-overlay" role="dialog" aria-modal="true">
    <div class="ov-modal">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h4>Editar pedido</h4>
                <div class="ov-sub" id="ovSubtitle"></div>
            </div>
            <button type="button" class="ov-close" onclick="closeOrderValueModal()" aria-label="Fechar">&times;</button>
        </div>

        <div class="ov-box">
            <small class="label">Descrição do pedido</small>
            <div class="name" id="ovName"></div>
            <div id="ovDescription" class="text-muted"></div>
            <div id="ovNotes" class="mt-2"></div>
        </div>

        <div class="ov-warning">
            <strong>Atenção:</strong> informe o valor <strong>integral</strong> do item. Se houver mais de uma
            unidade, lance o valor total de todas elas.
        </div>

        <label for="ovInput" class="ov-sub mb-1">Valor do pedido</label>
        <input type="text" id="ovInput" class="ov-input" inputmode="decimal" placeholder="R$ 0,00"
            oninput="refreshOrderValueChange()">

        <div class="ov-change">
            <small class="label">Alteração no valor</small>
            <strong class="values" id="ovValues"></strong>
            <small id="ovDiff" class="text-muted"></small>
        </div>

        <div class="ov-error" id="ovError"></div>

        <div class="ov-actions">
            <button type="button" class="cancel" onclick="closeOrderValueModal()">Cancelar</button>
            <button type="button" class="save" id="ovSave" onclick="saveOrderValue()">Salvar</button>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        const orderValueState = {
            url: null,
            current: 0
        };
        const ovCurrency = value => `R$ ${Number(value).toFixed(2).replace('.', ',')}`;

        function parseOrderValue(text) {
            let value = String(text).replace(/[^\d,.]/g, '');
            if (value.includes(',')) value = value.replace(/\./g, '').replace(',', '.');
            const number = parseFloat(value);
            return Number.isFinite(number) && number > 0 ? Math.round(number * 100) / 100 : 0;
        }

        function openOrderValueModal(button) {
            const data = button.dataset;
            orderValueState.url = data.url;
            orderValueState.current = parseFloat(data.current) || 0;

            document.getElementById('ovSubtitle').textContent = data.order;
            document.getElementById('ovName').textContent = `${data.quantity} × ${data.name}`;
            document.getElementById('ovDescription').textContent = data.description || '';
            document.getElementById('ovNotes').textContent = data.notes ? `Observação: ${data.notes}` : '';
            document.getElementById('ovInput').value = orderValueState.current > 0 ?
                orderValueState.current.toFixed(2).replace('.', ',') : '';
            document.getElementById('ovError').textContent = '';
            document.getElementById('orderValueModal').classList.add('open');
            refreshOrderValueChange();
            document.getElementById('ovInput').focus();
        }

        function closeOrderValueModal() {
            document.getElementById('orderValueModal').classList.remove('open');
        }

        function refreshOrderValueChange() {
            const next = parseOrderValue(document.getElementById('ovInput').value);
            const diff = next - orderValueState.current;
            document.getElementById('ovValues').textContent =
                `${ovCurrency(orderValueState.current)} → ${ovCurrency(next)}`;
            document.getElementById('ovDiff').textContent =
                `Diferença: ${diff < 0 ? '− ' : '+ '}${ovCurrency(Math.abs(diff))}`;
        }

        async function saveOrderValue() {
            const amount = parseOrderValue(document.getElementById('ovInput').value);
            const errorBox = document.getElementById('ovError');
            const saveButton = document.getElementById('ovSave');
            errorBox.textContent = '';

            if (amount <= 0) {
                errorBox.textContent = 'Informe um valor maior que zero.';
                return;
            }

            saveButton.disabled = true;
            try {
                const response = await fetch(orderValueState.url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        amount
                    })
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Não foi possível salvar o valor.');
                window.location.reload();
            } catch (error) {
                errorBox.textContent = error.message;
                saveButton.disabled = false;
            }
        }

        document.addEventListener('keydown', event => {
            if (event.key === 'Escape') closeOrderValueModal();
        });
    </script>
@endpush

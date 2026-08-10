/**
 * Sistema de Notificações em Tempo Real via Polling
 * 
 * Este script implementa um sistema de notificações que busca
 * por novas notificações a cada 10 segundos.
 */

class NotificationSystem {
    constructor() {
        // Prevenir múltiplas instâncias
        if (window.__notificationSystemInitialized) {
            console.warn('NotificationSystem já foi inicializado. Ignorando...');
            return;
        }

        window.__notificationSystemInitialized = true;

        this.pollingInterval = null;
        this.lastNotificationId = null;
        this.notificationCount = 0;
        this.audioContext = null;
        this.isAuthenticated = null; // Será determinado na primeira requisição

        this.printerStorageKeys = {
            agentUrl: 'kitchenPrinterAgentUrl',
            agentModel: 'kitchenPrinterAgentModel',
            printerAddress: 'kitchenPrinterAddress',
            connected: 'kitchenPrinterConnected'
        };

        // Inicializar ao carregar
        this.init();
    }

    /**
     * Retorna a configuração de impressão armazenada no localStorage
     */
    getPrinterConfig() {
        return {
            agentUrl: localStorage.getItem(this.printerStorageKeys.agentUrl) || '',
            agentModel: localStorage.getItem(this.printerStorageKeys.agentModel) || '',
            printerAddress: localStorage.getItem(this.printerStorageKeys.printerAddress) || '',
            connected: localStorage.getItem(this.printerStorageKeys.connected) === 'true'
        };
    }

    // Acquire a short-lived print lock to avoid concurrent sends for same order
    acquirePrintLock(orderId) {
        const lockKey = `kitchenPrinting:${orderId}`;
        try {
            const now = Date.now();
            const lockRaw = localStorage.getItem(lockKey);
            if (lockRaw) {
                const lockTs = parseInt(lockRaw, 10) || 0;
                if (now - lockTs < 60000) return false; // locked
            }
            localStorage.setItem(lockKey, String(now));
            return true;
        } catch (e) {
            return false;
        }
    }

    // mark that retry button was clicked once to prevent re-enabling after DOM refresh
    markRetryClicked(orderId) {
        try { localStorage.setItem(`kitchenRetryClicked:${orderId}`, 'true'); } catch (e) { /* ignore */ }
    }

    retryClicked(orderId) {
        try { return localStorage.getItem(`kitchenRetryClicked:${orderId}`) === 'true'; } catch (e) { return false; }
    }

    clearRetryClicked(orderId) {
        try { localStorage.removeItem(`kitchenRetryClicked:${orderId}`); } catch (e) { /* ignore */ }
    }

    /**
     * Failed prints queue helpers (localStorage)
     */
    getFailedPrints() {
        try {
            const raw = localStorage.getItem('kitchenFailedPrints') || '[]';
            return JSON.parse(raw);
        } catch (e) {
            return [];
        }
    }

    addFailedPrint(orderId) {
        const list = this.getFailedPrints();
        if (!list.includes(orderId)) {
            list.push(orderId);
            localStorage.setItem('kitchenFailedPrints', JSON.stringify(list));
        }
    }

    removeFailedPrint(orderId) {
        let list = this.getFailedPrints();
        list = list.filter(id => id !== orderId);
        localStorage.setItem('kitchenFailedPrints', JSON.stringify(list));
    }


    async printOrder(orderId) {
        const printerConfig = this.getPrinterConfig();

        try {
            const response = await fetch('/api/notifications/print', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    order_id: orderId,
                    agent_url: printerConfig.agentUrl,
                    agent_model: printerConfig.agentModel,
                    printer_address: printerConfig.printerAddress,
                })
            });

            if (response.status !== 200) {
                const body = await response.text();
                console.error('printOrder non-ok response', { orderId, status: response.status, body });
                throw new Error(`Falha ao enviar impressão: ${response.status} ${body}`);
            }

            return response;
        } catch (error) {
            console.error('Erro ao chamar printOrder no backend:', error);
            throw error;
        }
    }

    async processNewOrderNotifications(notifications) {
        for (const notification of notifications) {
            const data = notification.data;
            if (data.type === 'new_order_kitchen' && data.order_id) {
                try {
                    // Skip if already printed (local) to avoid duplicate prints
                    try {
                        const printedMap = JSON.parse(localStorage.getItem('kitchenPrintedOrders') || '{}');
                        if (printedMap && printedMap[data.order_id]) {
                            console.info(`Pedido ${data.order_id} já marcado como impresso (pulando).`);
                            continue;
                        }
                    } catch (e) { /* ignore */ }

                    // Skip if a recent print attempt is in progress
                    try {
                        const lockKey = `kitchenPrinting:${data.order_id}`;
                        const lockRaw = localStorage.getItem(lockKey);
                        if (lockRaw && (Date.now() - parseInt(lockRaw, 10) < 60000)) {
                            console.info(`Pedido ${data.order_id} está em tentativa de impressão recente (pulando).`);
                            continue;
                        }
                    } catch (e) { /* ignore */ }

                    // Acquire lock to avoid duplicate concurrent prints for same order
                    if (!this.acquirePrintLock(data.order_id)) {
                        console.info(`Pedido ${data.order_id} já está em impressão recente, pulando.`);
                        continue;
                    }

                    // Enviar apenas uma vez ao receber a notificação. Se retornar success=false,
                    // registrar como falha para permitir reenvio manual via botão.
                    try {
                        const res = await this.printOrder(data.order_id);

                        if (res && res.status === 200) {
                            // success on initial notification: do nothing (no UI change, no cache)

                        } else {
                            // backend informou falha
                            this.addFailedPrint(data.order_id);
                            console.info(`Pedido ${data.order_id} adicionado à fila de falhas para reenvio manual.`);
                        }
                    } catch (err) {
                        console.warn('Falha ao imprimir pedido via backend; marcando como falha para reenvio manual:', data.order_id, err);
                        try { this.addFailedPrint(data.order_id); } catch (e) { /* ignore */ }
                    }
                } catch (error) {
                    console.warn('Falha ao imprimir pedido via backend; marcando como falha para reenvio manual:', data.order_id, error);
                    try { this.addFailedPrint(data.order_id); } catch (e) { /* ignore */ }
                }
            }
        }
    }

    /**
     * Inicializa o sistema de notificações
     */
    init() {
        // Criar badge de contador no DOM se não existir
        this.createNotificationBadge();

        // Iniciar polling
        this.startPolling();

        // Nota: não iniciar rotina de reenvio automática — reenvio manual somente via botão

        // Buscar notificações iniciais
        this.fetchNotifications();
    }

    /**
     * Inicia um intervalo que tenta reenviar pedidos que falharam
     */
    startFailedPrintsRetry() {
        // removed: automatic periodic re-send deprecated per spec
    }

    /**
     * Cria o badge de contador de notificações
     */
    createNotificationBadge() {
        // Verificar se já existe e está no DOM
        let badge = document.getElementById('notification-badge');

        // Se existir e estiver no DOM, não fazer nada
        if (badge && badge.parentElement) {
            return badge;
        }

        // Se não existir ou foi removido do DOM, criar um novo
        badge = document.createElement('div');
        badge.id = 'notification-badge';
        badge.className = 'notification-badge-floating';
        badge.setAttribute('data-notification-badge', 'true'); // Marcador para identificar
        badge.style.cssText = `
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #3498db;
            color: white;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
            z-index: 10000;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            cursor: pointer;
            transition: all 0.3s ease;
        `;

        // Criar ícone de sino
        badge.innerHTML = `
            <div style="position: relative;">
                <i class="fas fa-bell" style="font-size: 24px;"></i>
                <span id="notification-count-badge" style="
                    position: absolute;
                    top: -8px;
                    right: -8px;
                    background: #e74c3c;
                    color: white;
                    border-radius: 50%;
                    width: 20px;
                    height: 20px;
                    display: none;
                    align-items: center;
                    justify-content: center;
                    font-size: 11px;
                    font-weight: bold;
                "></span>
            </div>
        `;

        badge.onclick = () => this.showNotificationsList();
        badge.onmouseover = () => {
            badge.style.transform = 'scale(1.1)';
            badge.style.boxShadow = '0 6px 25px rgba(0,0,0,0.4)';
        };
        badge.onmouseout = () => {
            badge.style.transform = 'scale(1)';
            badge.style.boxShadow = '0 4px 20px rgba(0,0,0,0.3)';
        };

        document.body.appendChild(badge);
        return badge;
    }

    /**
     * Inicia o polling de notificações
     */
    startPolling() {
        // Parar qualquer polling existente primeiro
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
        }

        // Buscar a cada 5 segundos
        this.pollingInterval = setInterval(() => {
            this.fetchNotifications();
        }, 5000);
    }

    /**
     * Para o polling
     */
    stopPolling() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
            this.pollingInterval = null;
        }
    }

    /**
     * Busca notificações não lidas
     */
    async fetchNotifications() {
        try {
            let response;

            // Se já sabemos se está autenticado ou não, usar a rota correta
            if (this.isAuthenticated === true) {
                // Usuário autenticado - usar rota autenticada
                response = await fetch('/api/notifications/unread', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin'
                });
            } else if (this.isAuthenticated === false) {
                // Usuário não autenticado - usar rota de cliente
                response = await fetch('/api/notifications/client-unread', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin'
                });
            } else {
                // Primeira vez - tentar rota autenticada para descobrir
                response = await fetch('/api/notifications/unread', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin'
                });

                // Se retornou 401, usuário não está autenticado
                if (response.status === 401) {
                    this.isAuthenticated = false;
                    // Tentar rota de cliente
                    response = await fetch('/api/notifications/client-unread', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin'
                    });
                } else if (response.ok) {
                    // Usuário está autenticado
                    this.isAuthenticated = true;
                }
            }

            if (!response.ok) {
                return;
            }

            const data = await response.json();

            if (data.success && data.notifications && data.notifications.length > 0) {
                // Verificar se há notificações novas
                const newNotifications = this.filterNewNotifications(data.notifications);
                console.debug('[NotificationSystem] notifications received', data.notifications, 'newNotifications', newNotifications);

                if (newNotifications.length > 0) {
                    // Tocar som apenas para notificações novas
                    this.playNotificationSound();

                    // Imprimir pedidos novos via backend
                    this.processNewOrderNotifications(newNotifications);

                    // Mostrar popup da notificação mais recente
                    this.showNotificationPopup(newNotifications[0]);
                }

                // Atualizar contador
                this.updateBadgeCounter(data.count);

                // Atualizar última notificação vista
                if (data.notifications[0]) {
                    this.lastNotificationId = data.notifications[0].id;
                }
            } else {
                this.updateBadgeCounter(0);
            }
        } catch (error) {
            console.error('Erro ao buscar notificações:', error);
        }
    }

    /**
     * Filtra notificações novas desde a última verificação
     */
    filterNewNotifications(notifications) {
        if (!this.lastNotificationId) {
            // Primeira vez, considerar apenas a mais recente
            return notifications.slice(0, 1);
        }

        const newOnes = [];
        for (const notif of notifications) {
            if (notif.id === this.lastNotificationId) {
                break;
            }
            newOnes.push(notif);
        }

        return newOnes;
    }

    /**
     * Atualiza o contador de notificações
     */
    updateBadgeCounter(count) {
        this.notificationCount = count;
        let badge = document.getElementById('notification-badge');

        // Se o badge não existir ou não estiver no DOM, recriá-lo
        if (!badge || !badge.parentElement) {
            badge = this.createNotificationBadge();
        }

        if (badge && badge.parentElement) {
            // Badge principal sempre visível
            badge.style.display = 'flex';

            const countBadge = document.getElementById('notification-count-badge');

            if (count > 0) {
                // Mudar cor quando houver notificações
                badge.style.background = '#e74c3c';

                // Mostrar contador
                if (countBadge) {
                    countBadge.textContent = count > 99 ? '99+' : count;
                    countBadge.style.display = 'flex';
                }
            } else {
                // Cor padrão quando não houver notificações
                badge.style.background = '#3498db';

                // Esconder contador
                if (countBadge) {
                    countBadge.style.display = 'none';
                }
            }
        }
    }

    /**
     * Toca o som de notificação
     */
    playNotificationSound() {
        // Tentar tocar o arquivo MP3 primeiro
        const audio = new Audio('/sounds/notification.mp3');
        audio.volume = 0.5;

        audio.play().catch(() => {
            // Se falhar, criar um beep sintético
            this.playBeep();
        });
    }

    /**
     * Cria um beep sintético usando Web Audio API
     */
    playBeep() {
        try {
            if (!this.audioContext) {
                this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
            }

            const oscillator = this.audioContext.createOscillator();
            const gainNode = this.audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(this.audioContext.destination);

            oscillator.frequency.value = 800;
            oscillator.type = 'sine';

            gainNode.gain.setValueAtTime(0.3, this.audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, this.audioContext.currentTime + 0.5);

            oscillator.start(this.audioContext.currentTime);
            oscillator.stop(this.audioContext.currentTime + 0.5);
        } catch (error) {
            console.error('Erro ao tocar beep:', error);
        }
    }

    /**
     * Mostra popup de notificação
     */
    showNotificationPopup(notification) {
        const data = notification.data;
        const type = data.type;

        // Verificar se é notificação para cliente (precisa de popup destacado)
        if (type === 'order_ready_client') {
            this.showClientReadyModal(data, notification.id);
            return;
        }

        // Para outros tipos, mostrar toast simples
        this.showToast(data.message, this.getNotificationColor(type), notification.id);
    }

    /**
     * Mostra modal grande para cliente quando pedido estiver pronto
     */
    showClientReadyModal(data, notificationId) {
        // Remover modal anterior se existir
        const existingModal = document.getElementById('client-ready-modal');
        if (existingModal) {
            existingModal.remove();
        }

        // Criar modal
        const modal = document.createElement('div');
        modal.id = 'client-ready-modal';
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            animation: fadeIn 0.3s ease;
        `;

        modal.innerHTML = `
            <div style="
                background: white;
                border-radius: 20px;
                padding: 40px;
                max-width: 500px;
                width: 90%;
                text-align: center;
                box-shadow: 0 10px 50px rgba(0,0,0,0.3);
                animation: slideUp 0.3s ease;
            ">
                <div style="
                    background: #27ae60;
                    width: 80px;
                    height: 80px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0 auto 20px;
                ">
                    <i class="fas fa-check" style="font-size: 40px; color: white;"></i>
                </div>
                <h2 style="color: #2c3e50; margin-bottom: 15px; font-size: 28px;">
                    Pedido Pronto!
                </h2>
                <p style="color: #7f8c8d; font-size: 18px; margin-bottom: 10px;">
                    Pedido <strong>#${data.order_number}</strong>
                </p>
                <p style="color: #2c3e50; font-size: 20px; margin-bottom: 30px; font-weight: 600;">
                    ${data.message}
                </p>
                <button onclick="document.getElementById('client-ready-modal').remove(); notificationSystem.markAsRead('${notificationId}');" style="
                    background: #27ae60;
                    color: white;
                    border: none;
                    padding: 15px 40px;
                    border-radius: 10px;
                    font-size: 18px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.3s ease;
                " onmouseover="this.style.background='#229954'" onmouseout="this.style.background='#27ae60'">
                    Entendido!
                </button>
            </div>
        `;

        document.body.appendChild(modal);

        // Adicionar estilos de animação
        if (!document.getElementById('notification-animations')) {
            const style = document.createElement('style');
            style.id = 'notification-animations';
            style.textContent = `
                @keyframes fadeIn {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }
                @keyframes slideUp {
                    from { transform: translateY(50px); opacity: 0; }
                    to { transform: translateY(0); opacity: 1; }
                }
            `;
            document.head.appendChild(style);
        }
    }

    /**
     * Mostra toast de notificação
     */
    showToast(message, color = '#3498db', notificationId = null) {
        const toast = document.createElement('div');
        toast.className = 'notification-toast';
        toast.style.cssText = `
            position: fixed;
            top: 16px;
            right: 16px;
            left: auto;
            background: ${color};
            color: white;
            padding: 15px 50px 15px 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            z-index: 10001;
            max-width: min(90vw, 350px);
            width: auto;
            animation: slideIn 0.3s ease;
            font-size: 14px;
            line-height: 1.5;
            position: relative;
        `;

        const messageDiv = document.createElement('div');
        messageDiv.textContent = message;
        toast.appendChild(messageDiv);

        // Adicionar botão de fechar
        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = '×';
        closeBtn.style.cssText = `
            position: absolute;
            top: 5px;
            right: 10px;
            background: transparent;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            line-height: 1;
            opacity: 0.7;
            transition: opacity 0.2s;
        `;
        closeBtn.onmouseover = () => closeBtn.style.opacity = '1';
        closeBtn.onmouseout = () => closeBtn.style.opacity = '0.7';
        closeBtn.onclick = () => {
            if (notificationId) {
                this.markAsRead(notificationId);
            }
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        };
        toast.appendChild(closeBtn);

        // Adicionar animação de slide
        if (!document.getElementById('toast-animations')) {
            const style = document.createElement('style');
            style.id = 'toast-animations';
            style.textContent = `
                @keyframes slideIn {
                    from { transform: translateX(400px); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                @keyframes slideOut {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(400px); opacity: 0; }
                }
            `;
            document.head.appendChild(style);
        }

        document.body.appendChild(toast);

        // Auto-dismiss após 5 segundos
        const autoDismissTimer = setTimeout(() => {
            if (notificationId) {
                this.markAsRead(notificationId);
            }
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 5000);

        // Cancelar auto-dismiss se o usuário interagir
        toast.onmouseenter = () => clearTimeout(autoDismissTimer);
    }

    /**
     * Retorna a cor baseada no tipo de notificação
     */
    getNotificationColor(type) {
        const colors = {
            'new_order_kitchen': '#e74c3c',
            'quick_item_waiter': '#f39c12',
            'order_ready_waiter': '#3498db',
            'order_ready_client': '#27ae60',
        };

        return colors[type] || '#3498db';
    }

    /**
     * Marca notificação como lida
     */
    async markAsRead(notificationId) {
        try {
            let response;

            // Usar a rota correta baseado no status de autenticação
            if (this.isAuthenticated === false) {
                // Cliente não autenticado
                response = await fetch(`/api/notifications/client/${notificationId}/read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    credentials: 'same-origin'
                });
            } else {
                // Usuário autenticado ou ainda não sabemos
                response = await fetch(`/api/notifications/${notificationId}/read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    credentials: 'same-origin'
                });

                // Se retornou 401, tentar rota de cliente
                if (response.status === 401) {
                    this.isAuthenticated = false;
                    response = await fetch(`/api/notifications/client/${notificationId}/read`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        credentials: 'same-origin'
                    });
                }
            }

            if (response.ok) {
                // Atualizar lista de notificações
                this.fetchNotifications();
            }
        } catch (error) {
            console.error('Erro ao marcar notificação como lida:', error);
        }
    }

    /**
     * Mostra lista de todas as notificações
     */
    async showNotificationsList() {
        try {
            let response;

            // Usar a rota correta baseado no status de autenticação
            if (this.isAuthenticated === false) {
                // Cliente não autenticado
                response = await fetch('/api/notifications/client-all', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin'
                });
            } else {
                // Usuário autenticado ou ainda não sabemos
                response = await fetch('/api/notifications/all', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin'
                });

                // Se retornou 401, tentar rota de cliente
                if (response.status === 401) {
                    this.isAuthenticated = false;
                    response = await fetch('/api/notifications/client-all', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin'
                    });
                }
            }

            if (!response.ok) {
                console.error('Erro ao buscar notificações');
                return;
            }

            const data = await response.json();
            this.renderNotificationsModal(data.notifications || []);
        } catch (error) {
            console.error('Erro ao buscar lista de notificações:', error);
        }
    }

    /**
     * Renderiza o modal com lista de notificações
     */
    renderNotificationsModal(notifications) {
        // Remover modal anterior se existir
        const existingModal = document.getElementById('notifications-list-modal');
        if (existingModal) {
            existingModal.remove();
        }

        // Criar modal
        const modal = document.createElement('div');
        modal.id = 'notifications-list-modal';
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 99998;
            animation: fadeIn 0.3s ease;
        `;

        // Criar conteúdo do modal
        const modalContent = document.createElement('div');
        modalContent.style.cssText = `
            background: white;
            border-radius: 15px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 10px 50px rgba(0,0,0,0.3);
            animation: slideUp 0.3s ease;
        `;

        // Cabeçalho
        const header = document.createElement('div');
        header.style.cssText = `
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            border-radius: 15px 15px 0 0;
        `;
        header.innerHTML = `
            <h3 style="margin: 0; color: white; font-size: 20px;">
                <i class="fas fa-bell" style="margin-right: 10px;"></i>
                Notificações (${notifications.length})
            </h3>
            <button onclick="document.getElementById('notifications-list-modal').remove();" style="
                background: rgba(255,255,255,0.2);
                border: none;
                font-size: 24px;
                cursor: pointer;
                color: white;
                padding: 5px 10px;
                line-height: 1;
                border-radius: 5px;
                transition: background 0.2s;
            " onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">×</button>
        `;
        modalContent.appendChild(header);

        // Lista de notificações
        const listContainer = document.createElement('div');
        listContainer.style.cssText = `
            flex: 1;
            overflow-y: auto;
            padding: 10px;
        `;

        if (notifications.length === 0) {
            listContainer.innerHTML = `
                <div style="
                    text-align: center;
                    padding: 40px 20px;
                    color: #7f8c8d;
                ">
                    <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3;"></i>
                    <p style="margin: 0;">Nenhuma notificação</p>
                </div>
            `;
        } else {
            notifications.forEach(notification => {
                const notifItem = this.createNotificationItem(notification);
                listContainer.appendChild(notifItem);
            });
        }

        modalContent.appendChild(listContainer);

        // Rodapé com ação de marcar todas como lidas
        if (notifications.some(n => !n.read_at)) {
            const footer = document.createElement('div');
            footer.style.cssText = `
                padding: 15px 20px;
                border-top: 1px solid #e0e0e0;
                text-align: center;
            `;
            footer.innerHTML = `
                <button onclick="notificationSystem.markAllAsRead();" style="
                    background: #3498db;
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 8px;
                    cursor: pointer;
                    font-size: 14px;
                    font-weight: 600;
                    transition: background 0.2s;
                " onmouseover="this.style.background='#2980b9'" onmouseout="this.style.background='#3498db'">
                    Marcar todas como lidas
                </button>
            `;
            modalContent.appendChild(footer);
        }

        modal.appendChild(modalContent);
        document.body.appendChild(modal);

        // Fechar ao clicar fora
        modal.onclick = (e) => {
            if (e.target === modal) {
                modal.remove();
            }
        };
    }

    /**
     * Cria um item de notificação
     */
    createNotificationItem(notification) {
        const item = document.createElement('div');
        const isUnread = !notification.read_at;
        const data = notification.data;
        const color = this.getNotificationColor(data.type);

        item.style.cssText = `
            background: ${isUnread ? '#fff3cd' : 'white'};
            border-left: 4px solid ${color};
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: start;
            transition: all 0.2s;
            position: relative;
            ${isUnread ? 'box-shadow: 0 2px 8px rgba(0,0,0,0.1);' : ''}
        `;

        item.onmouseover = () => item.style.transform = 'translateX(5px)';
        item.onmouseout = () => item.style.transform = 'translateX(0)';

        // Badge de "Nova" para não lidas
        if (isUnread) {
            const newBadge = document.createElement('div');
            newBadge.textContent = 'NOVA';
            newBadge.style.cssText = `
                position: absolute;
                top: 10px;
                right: 10px;
                background: ${color};
                color: white;
                padding: 2px 8px;
                border-radius: 12px;
                font-size: 10px;
                font-weight: bold;
                letter-spacing: 0.5px;
            `;
            item.appendChild(newBadge);
        } else {
            // Ícone de "lida"
            const readIcon = document.createElement('div');
            readIcon.innerHTML = '<i class="fas fa-check-double"></i>';
            readIcon.style.cssText = `
                position: absolute;
                top: 10px;
                right: 10px;
                color: #27ae60;
                font-size: 14px;
            `;
            readIcon.title = 'Lida';
            item.appendChild(readIcon);
        }

        // Conteúdo
        const content = document.createElement('div');
        content.style.cssText = 'flex: 1; padding-right: 50px;';

        const message = document.createElement('div');
        message.textContent = data.message;
        message.style.cssText = `
            color: #2c3e50;
            margin-bottom: 5px;
            font-size: 14px;
            ${isUnread ? 'font-weight: 600;' : ''}
        `;
        content.appendChild(message);

        const time = document.createElement('div');
        time.textContent = this.formatDate(notification.created_at);
        time.style.cssText = `
            color: #95a5a6;
            font-size: 12px;
            font-weight: normal;
        `;
        content.appendChild(time);

        item.appendChild(content);

        // Botão de marcar como lida (apenas para não lidas)
        if (isUnread) {
            const dismissBtn = document.createElement('button');
            dismissBtn.innerHTML = '<i class="fas fa-check"></i>';
            dismissBtn.title = 'Marcar como lida';
            dismissBtn.style.cssText = `
                position: absolute;
                bottom: 10px;
                right: 10px;
                background: ${color};
                color: white;
                border: none;
                width: 32px;
                height: 32px;
                border-radius: 50%;
                cursor: pointer;
                font-size: 12px;
                transition: all 0.2s;
                flex-shrink: 0;
            `;
            dismissBtn.onmouseover = () => {
                dismissBtn.style.transform = 'scale(1.1)';
                dismissBtn.style.boxShadow = '0 2px 8px rgba(0,0,0,0.2)';
            };
            dismissBtn.onmouseout = () => {
                dismissBtn.style.transform = 'scale(1)';
                dismissBtn.style.boxShadow = 'none';
            };
            dismissBtn.onclick = (e) => {
                e.stopPropagation();
                this.markAsRead(notification.id);
                // Animar remoção
                item.style.opacity = '0';
                item.style.transform = 'translateX(-20px)';
                setTimeout(() => {
                    item.remove();
                    // Reabrir modal para atualizar
                    this.showNotificationsList();
                }, 300);
            };
            item.appendChild(dismissBtn);
        }

        return item;
    }

    /**
     * Formata a data da notificação
     */
    formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);

        if (diffMins < 1) return 'Agora';
        if (diffMins < 60) return `Há ${diffMins} min`;
        if (diffHours < 24) return `Há ${diffHours}h`;
        if (diffDays < 7) return `Há ${diffDays}d`;

        return date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
    }

    /**
     * Marca todas as notificações como lidas
     */
    async markAllAsRead() {
        try {
            let response;

            // Usar a rota correta baseado no status de autenticação
            if (this.isAuthenticated === false) {
                // Cliente não autenticado
                response = await fetch('/api/notifications/client-mark-all-read', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    credentials: 'same-origin'
                });
            } else {
                // Usuário autenticado ou ainda não sabemos
                response = await fetch('/api/notifications/mark-all-read', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    credentials: 'same-origin'
                });

                // Se retornou 401, tentar rota de cliente
                if (response.status === 401) {
                    this.isAuthenticated = false;
                    response = await fetch('/api/notifications/client-mark-all-read', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        credentials: 'same-origin'
                    });
                }
            }

            if (response.ok) {
                // Fechar modal e atualizar
                document.getElementById('notifications-list-modal')?.remove();
                this.fetchNotifications();
            }
        } catch (error) {
            console.error('Erro ao marcar todas como lidas:', error);
        }
    }

    /**
     * Destruir o sistema
     */
    destroy() {
        this.stopPolling();
        if (this._failedPrintsRetryInterval) {
            clearInterval(this._failedPrintsRetryInterval);
            this._failedPrintsRetryInterval = null;
        }
        window.__notificationSystemInitialized = false;
        // NÃO remover o badge do DOM para evitar piscar
        // const badge = document.getElementById('notification-badge');
        // if (badge) {
        //     badge.remove();
        // }
    }
}

// Prevenir múltiplas instâncias
if (!window.notificationSystem) {
    // Inicializar quando o DOM estiver pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            window.notificationSystem = new NotificationSystem();
        });
    } else {
        window.notificationSystem = new NotificationSystem();
    }
}

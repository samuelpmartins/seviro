/**
 * Sistema de Notificações em Tempo Real via Polling
 * 
 * Este script implementa um sistema de notificações que busca
 * por novas notificações a cada 10 segundos.
 */

class NotificationSystem {
    constructor() {
        this.pollingInterval = null;
        this.lastNotificationId = null;
        this.notificationCount = 0;
        this.audioContext = null;
        
        // Inicializar ao carregar
        this.init();
    }
    
    /**
     * Inicializa o sistema de notificações
     */
    init() {
        // Criar badge de contador no DOM se não existir
        this.createNotificationBadge();
        
        // Iniciar polling
        this.startPolling();
        
        // Buscar notificações iniciais
        this.fetchNotifications();
    }
    
    /**
     * Cria o badge de contador de notificações
     */
    createNotificationBadge() {
        // Procurar por um elemento com id 'notification-badge' ou criar um
        let badge = document.getElementById('notification-badge');
        if (!badge) {
            // Se não existir, criar um badge flutuante
            badge = document.createElement('div');
            badge.id = 'notification-badge';
            badge.className = 'notification-badge-floating';
            badge.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #e74c3c;
                color: white;
                border-radius: 50%;
                width: 30px;
                height: 30px;
                display: none;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                font-size: 14px;
                z-index: 10000;
                box-shadow: 0 2px 10px rgba(0,0,0,0.3);
                cursor: pointer;
            `;
            badge.onclick = () => this.showNotificationsList();
            document.body.appendChild(badge);
        }
    }
    
    /**
     * Inicia o polling de notificações
     */
    startPolling() {
        // Buscar a cada 2 segundos
        this.pollingInterval = setInterval(() => {
            this.fetchNotifications();
        }, 2000);
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
            // Tentar rota autenticada primeiro
            let response = await fetch('/api/notifications/unread', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin'
            });
            
            // Se não estiver autenticado (401), tentar rota para cliente não autenticado
            if (response.status === 401) {
                response = await fetch('/api/notifications/client-unread', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin'
                });
            }
            
            if (!response.ok) {
                return;
            }
            
            const data = await response.json();
            
            if (data.success && data.notifications && data.notifications.length > 0) {
                // Verificar se há notificações novas
                const newNotifications = this.filterNewNotifications(data.notifications);
                
                if (newNotifications.length > 0) {
                    // Tocar som apenas para notificações novas
                    this.playNotificationSound();
                    
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
        const badge = document.getElementById('notification-badge');
        
        if (badge) {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
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
            this.showClientReadyModal(data);
            return;
        }
        
        // Para outros tipos, mostrar toast simples
        this.showToast(data.message, this.getNotificationColor(type));
    }
    
    /**
     * Mostra modal grande para cliente quando pedido estiver pronto
     */
    showClientReadyModal(data) {
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
                <button onclick="document.getElementById('client-ready-modal').remove(); notificationSystem.markAsRead('${notification.id}');" style="
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
    showToast(message, color = '#3498db') {
        const toast = document.createElement('div');
        toast.className = 'notification-toast';
        toast.style.cssText = `
            position: fixed;
            top: 80px;
            right: 20px;
            background: ${color};
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            z-index: 10001;
            max-width: 350px;
            animation: slideIn 0.3s ease;
            font-size: 14px;
            line-height: 1.5;
        `;
        toast.textContent = message;
        
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
        
        // Remover após 5 segundos
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 5000);
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
            // Tentar rota autenticada primeiro
            let response = await fetch(`/api/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                credentials: 'same-origin'
            });
            
            // Se não estiver autenticado (401), tentar rota para cliente
            if (response.status === 401) {
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
    showNotificationsList() {
        // Implementar modal com lista de notificações (opcional)
        console.log('Abrir lista de notificações');
    }
    
    /**
     * Destruir o sistema
     */
    destroy() {
        this.stopPolling();
        const badge = document.getElementById('notification-badge');
        if (badge) {
            badge.remove();
        }
    }
}

// Inicializar globalmente
let notificationSystem = null;

// Inicializar quando o DOM estiver pronto
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        notificationSystem = new NotificationSystem();
    });
} else {
    notificationSystem = new NotificationSystem();
}

// Exportar para uso global
window.notificationSystem = notificationSystem;

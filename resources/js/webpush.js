/**
 * Web Push (VAPID) — inscreve o navegador para receber notificações
 * mesmo com a aba fechada, em Windows/desktop e mobile.
 */

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; i++) {
        outputArray[i] = rawData.charCodeAt(i);
    }

    return outputArray;
}

async function sendSubscriptionToServer(url, subscription) {
    await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        credentials: 'same-origin',
        body: JSON.stringify(subscription),
    });
}

async function unsubscribeFromWebPush() {
    if (!('serviceWorker' in navigator)) {
        return;
    }

    const registration = await navigator.serviceWorker.getRegistration('/sw.js');
    if (!registration) {
        return;
    }

    const subscription = await registration.pushManager.getSubscription();
    if (!subscription) {
        return;
    }

    const endpoint = subscription.endpoint;
    await subscription.unsubscribe();

    await sendSubscriptionToServer('/api/push/webpush-unsubscribe', { endpoint });
}

async function subscribeToWebPush() {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        console.warn('Este navegador não suporta Web Push.');
        return;
    }

    const vapidMeta = document.querySelector('meta[name="vapid-public-key"]');
    if (!vapidMeta || !vapidMeta.content) {
        return;
    }

    const registration = await navigator.serviceWorker.register('/sw.js');

    const permission = await Notification.requestPermission();
    if (permission !== 'granted') {
        return;
    }

    let subscription = await registration.pushManager.getSubscription();
    if (!subscription) {
        subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(vapidMeta.content),
        });
    }

    await sendSubscriptionToServer('/api/push/webpush-subscribe', {
        ...subscription.toJSON(),
        contentEncoding: 'aes128gcm',
    });
}

document.addEventListener('DOMContentLoaded', function () {
    subscribeToWebPush().catch(function (error) {
        console.error('Falha ao inscrever para Web Push:', error);
    });

    document.querySelectorAll('form').forEach(function (form) {
        if (form.id === 'waiterLogoutForm') {
            return;
        }

        if (new URL(form.action, window.location.origin).pathname !== '/logout') {
            return;
        }

        form.addEventListener('submit', async function (event) {
            if (form.dataset.pushLogoutHandled === 'true') {
                return;
            }

            event.preventDefault();
            form.dataset.pushLogoutHandled = 'true';

            try {
                await unsubscribeFromWebPush();
            } catch (error) {
                console.error('Falha ao remover inscrição Web Push:', error);
            } finally {
                form.submit();
            }
        });
    });
});

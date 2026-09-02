// Service Worker do SeviRo — Web Push
// Roda fora da página, em background. Não tem acesso ao DOM.

self.addEventListener('push', function (event) {
    if (!event.data) {
        return;
    }

    const payload = event.data.json();
    const title = payload.title || 'SeviRo';
    const options = {
        body: payload.body || '',
        icon: payload.icon || '/storage/img/logo.png',
        badge: payload.badge || '/storage/img/logo.png',
        data: payload.data || {},
        // Mantém visível até o usuário interagir e vibra no mobile, salvo a notificação dizer o contrário.
        requireInteraction: payload.requireInteraction ?? true,
        vibrate: payload.vibrate || [200, 100, 200],
        tag: payload.tag || title,
        renotify: payload.renotify ?? true,
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    const url = event.notification.data && event.notification.data.url ? event.notification.data.url : '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (windowClients) {
            for (const client of windowClients) {
                if (client.url === url && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});

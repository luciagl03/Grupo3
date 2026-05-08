// sw.js — Service Worker para notificaciones push
// Ubicación: backend/sw.js (raíz del backend para máximo alcance)

self.addEventListener('push', function(event) {
    if (!event.data) return;

    var data = {};
    try { data = event.data.json(); } catch(e) { data = { title: 'Zpot', body: event.data.text() }; }

    var options = {
        body:    data.body    || '',
        icon:    data.icon    || '/frontend/assets/images/Icono.png',
        badge:   data.badge   || '/frontend/assets/images/Icono.png',
        data:    data.url     || '/',
        vibrate: [100, 50, 100],
        actions: [{ action: 'ver', title: 'Ver' }]
    };

    event.waitUntil(
        self.registration.showNotification(data.title || 'Zpot', options)
    );
});

self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    var url = event.notification.data || '/';
    event.waitUntil(clients.openWindow(url));
});

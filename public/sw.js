/**
 * SISTA Service Worker — Progressive Web App
 * SMA Islam Sultan Agung 1 Semarang
 */

const CACHE_NAME = 'sista-v2.4.0';
const STATIC_ASSETS = [
    '/',
    '/manifest.json',
    '/assets/icons/icon-192x192.png'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS).catch(() => {});
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
            );
        })
    );
    return self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;

    event.respondWith(
        caches.match(event.request).then((cachedResponse) => {
            if (cachedResponse) {
                return cachedResponse;
            }
            return fetch(event.request).catch(() => {
                // Offline fallback
                if (event.request.mode === 'navigate') {
                    return caches.match('/');
                }
            });
        })
    );
});

self.addEventListener('push', (event) => {
    const data = event.data ? event.data.json() : {};
    const title = data.title || 'SISTA Notifikasi';
    const options = {
        body: data.body || 'Pembaruan baru dari SMA Islam Sultan Agung 1 Semarang.',
        icon: '/assets/icons/icon-192x192.png',
        badge: '/assets/icons/badge-72x72.png',
        data: data.url || '/'
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    event.waitUntil(clients.openWindow(event.notification.data || '/'));
});

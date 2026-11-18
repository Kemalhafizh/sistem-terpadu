/**
 * Sulaone Service Worker — Progressive Web App (SISTA)
 * Never caches /api/*, authenticated routes, or HTML responses.
 */

const CACHE_NAME = 'sulaone-v1.0.0';
const OFFLINE_URL = '/offline.html';
const PRECACHE_ASSETS = [
    OFFLINE_URL,
    '/images/logo-kotak.png',
    '/favicon.ico',
    '/assets/icons/icon-192x192.png',
    '/assets/icons/badge-72x72.png',
    '/offline-queue.js',
];

const STATIC_PREFIXES = ['/build/', '/assets/', '/images/'];
const STATIC_FILES = [OFFLINE_URL, '/offline-queue.js', '/favicon.ico'];

function isStaticAsset(pathname) {
    return STATIC_PREFIXES.some((prefix) => pathname.startsWith(prefix))
        || STATIC_FILES.includes(pathname);
}

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => Promise.allSettled(
            PRECACHE_ASSETS.map((asset) => cache.add(asset))
        ))
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(
            keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
        ))
    );
    return self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);
    if (url.origin !== self.location.origin) return;

    // Never cache API, Sanctum, Storage, auth endpoints, Livewire, or protected portals
    if (
        url.pathname.startsWith('/api/')
        || url.pathname.startsWith('/sanctum/')
        || url.pathname.startsWith('/storage/')
        || url.pathname.startsWith('/livewire/')
        || url.pathname.startsWith('/admin')
        || url.pathname.startsWith('/finance')
        || url.pathname === '/login'
        || url.pathname === '/logout'
    ) {
        return;
    }

    // Navigation / HTML requests: network-only with offline fallback page
    const acceptHeader = event.request.headers.get('accept') || '';
    if (event.request.mode === 'navigate' || acceptHeader.includes('text/html')) {
        event.respondWith(
            fetch(event.request).catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    // Static build/media assets only: cache-first with safe re-population
    if (isStaticAsset(url.pathname)) {
        event.respondWith(
            caches.match(event.request).then((cachedResponse) => {
                if (cachedResponse) {
                    return cachedResponse;
                }
                return fetch(event.request).then((networkResponse) => {
                    if (networkResponse && networkResponse.ok && networkResponse.type === 'basic') {
                        const responseToCache = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(event.request, responseToCache);
                        });
                    }
                    return networkResponse;
                });
            })
        );
    }
});

self.addEventListener('push', (event) => {
    let data = {};
    try {
        data = event.data ? event.data.json() : {};
    } catch (e) {
        data = {};
    }
    const title = data.title || 'Sulaone — Hanyakra';
    const options = {
        body: data.body || 'Pembaruan informasi terbaru dari portal sekolah.',
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

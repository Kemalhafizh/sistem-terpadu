const CACHE_VERSION = 'sista-v1';
const STATIC_CACHE = `sista-static-${CACHE_VERSION}`;
const DYNAMIC_CACHE = `sista-dynamic-${CACHE_VERSION}`;
const OFFLINE_URL = '/offline.html';

// Static resources to cache immediately on installation
const STATIC_ASSETS = [
    OFFLINE_URL,
    '/images/logo-kotak.png',
    '/favicon.ico',
    '/offline-queue.js',
];

// 1. INSTALL EVENT: Pre-cache core offline assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE).then((cache) => {
            console.log('[Service Worker] Pre-caching offline assets');
            return cache.addAll(STATIC_ASSETS);
        }).then(() => self.skipWaiting())
    );
});

// 2. ACTIVATE EVENT: Clean up stale caches and claim clients
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.map((key) => {
                    if (key !== STATIC_CACHE && key !== DYNAMIC_CACHE) {
                        console.log('[Service Worker] Deleting old cache:', key);
                        return caches.delete(key);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// 3. FETCH EVENT: Intelligent Caching Strategy
self.addEventListener('fetch', (event) => {
    // Only handle GET requests and same-origin requests
    if (event.request.method !== 'GET' || !event.request.url.startsWith(self.location.origin)) {
        return;
    }

    const url = new URL(event.request.url);

    // Skip caching for sensitive routes (e.g. login, logout, impersonation, administrative/finance callbacks)
    if (
        url.pathname.startsWith('/login') ||
        url.pathname.startsWith('/logout') ||
        url.pathname.startsWith('/admin') ||
        url.pathname.startsWith('/finance/payments') ||
        url.pathname.startsWith('/gateway')
    ) {
        event.respondWith(fetch(event.request));
        return;
    }

    // A. API & Dynamic Data: Stale-While-Revalidate
    if (url.pathname.startsWith('/api/') || url.pathname.includes('/schedules/') || url.pathname.includes('/calendar')) {
        event.respondWith(
            caches.open(DYNAMIC_CACHE).then((cache) => {
                return cache.match(event.request).then((cachedResponse) => {
                    const fetchPromise = fetch(event.request).then((networkResponse) => {
                        if (networkResponse.status === 200) {
                            cache.put(event.request, networkResponse.clone());
                        }
                        return networkResponse;
                    }).catch(() => {
                        // Fallback to cache if network fails completely (offline)
                        return cachedResponse;
                    });
                    
                    return cachedResponse || fetchPromise;
                });
            })
        );
        return;
    }

    // B. Static Assets (CSS, JS, Images, Webfonts): Cache-First with Network Fallback & dynamic caching
    const isStaticAsset = 
        url.pathname.match(/\.(js|css|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot)$/) ||
        url.href.includes('fonts.googleapis.com') ||
        url.href.includes('fonts.gstatic.com');

    if (isStaticAsset) {
        event.respondWith(
            caches.match(event.request).then((cachedResponse) => {
                if (cachedResponse) {
                    return cachedResponse;
                }
                
                return fetch(event.request).then((networkResponse) => {
                    // Only cache successful requests
                    if (networkResponse && networkResponse.status === 200) {
                        const responseClone = networkResponse.clone();
                        caches.open(STATIC_CACHE).then((cache) => {
                            cache.put(event.request, responseClone);
                        });
                    }
                    return networkResponse;
                }).catch(() => {
                    // If image request fails offline, try to serve default placeholder logo if available in static cache
                    if (url.pathname.match(/\.(png|jpg|jpeg|svg)$/)) {
                        return caches.match('/images/logo-kotak.png');
                    }
                });
            })
        );
        return;
    }

    // C. HTML Pages: Network-First with Cache Fallback & Offline Fallback Page
    event.respondWith(
        fetch(event.request)
            .then((networkResponse) => {
                // If it's a valid HTML page, save to dynamic cache
                if (networkResponse && networkResponse.status === 200 && networkResponse.headers.get('content-type').includes('text/html')) {
                    const responseClone = networkResponse.clone();
                    caches.open(DYNAMIC_CACHE).then((cache) => {
                        cache.put(event.request, responseClone);
                    });
                }
                return networkResponse;
            })
            .catch(() => {
                // Network failed (offline), try to load page from caches
                return caches.match(event.request).then((cachedResponse) => {
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    // If page is not in cache, return the premium offline HTML page
                    return caches.match(OFFLINE_URL);
                });
            })
    );
});

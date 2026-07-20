const CACHE_NAME = 'absen-djj-v5';

// Only cache truly static assets — never cache HTML pages
const STATIC_ASSETS = [
    '/manifest.json',
    '/images/Logo/Logo_PU.png',
    '/images/Logo/favicon.ico'
];

self.addEventListener('install', (event) => {
    // Skip waiting so the new SW activates immediately
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS);
        })
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        Promise.all([
            // Claim all clients immediately
            self.clients.claim(),
            // Delete all old caches
            caches.keys().then((cacheNames) => {
                return Promise.all(
                    cacheNames.map((cache) => {
                        if (cache !== CACHE_NAME) {
                            console.log('Service Worker: Clearing Old Cache', cache);
                            return caches.delete(cache);
                        }
                    })
                );
            })
        ])
    );
});

self.addEventListener('fetch', (event) => {
    // Only handle GET requests. Bypass POST, PUT, DELETE, etc. (such as submitting /login form)
    if (event.request.method !== 'GET') {
        return;
    }

    const url = new URL(event.request.url);

    // Bypass Service Worker completely for dynamic API and polling routes
    const dynamicRoutes = [
        '/poll-check',
        '/today_holiday.json',
        '/notifications',
        '/check-holiday',
        '/cookie-consent',
        '/dev-reload-check'
    ];
    if (dynamicRoutes.some(path => url.pathname.includes(path))) {
        return;
    }

    // For navigation requests (HTML pages) — ALWAYS use network-first
    // This ensures users always get the latest server-rendered HTML with up-to-date JS
    if (event.request.mode === 'navigate' || event.request.headers.get('Accept')?.includes('text/html')) {
        event.respondWith(
            fetch(event.request).catch(async () => {
                // Offline fallback: return cached version if network fails
                const cachedResponse = await caches.match(event.request);
                if (cachedResponse) {
                    return cachedResponse;
                }
                // If not in cache, return a friendly offline HTML response
                return new Response(
                    '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Koneksi Terputus</title><meta name="viewport" content="width=device-width,initial-scale=1"><style>body{font-family:sans-serif;text-align:center;padding:50px;background:#f8f9fa;color:#495057}h1{color:#dc3545}p{font-size:1.1rem}</style></head><body><h1>Koneksi Terputus</h1><p>Anda sedang offline. Silakan periksa koneksi internet Anda dan coba kembali.</p></body></html>',
                    {
                        status: 503,
                        statusText: 'Service Unavailable',
                        headers: { 'Content-Type': 'text/html; charset=utf-8' }
                    }
                );
            })
        );
        return;
    }

    // For static assets — use cache-first
    event.respondWith(
        caches.match(event.request).then((response) => {
            return response || fetch(event.request).catch((err) => {
                // Silently catch network failures (e.g. offline or server restarted)
                console.warn('[SW] Network request failed:', event.request.url);
                return new Response('Network error', { status: 480, statusText: 'Network Error' });
            });
        })
    );
});
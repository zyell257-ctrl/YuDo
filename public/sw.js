/**
 * Ludo Tracker - Service Worker (PWA)
 * Cache ringan untuk performa mobile
 */

const CACHE_NAME    = 'ludo-tracker-v1';
const CACHE_STATIC  = [
    '/',
    '/css/app.css',
    '/js/app.js',
    '/manifest.json',
    'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js',
];

// ---- Install: cache static assets ----
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(CACHE_STATIC))
            .then(() => self.skipWaiting())
    );
});

// ---- Activate: hapus cache lama ----
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))
            )
        ).then(() => self.clients.claim())
    );
});

// ---- Fetch: strategi cache-first untuk aset, network-first untuk API ----
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    // Skip non-GET request
    if (event.request.method !== 'GET') return;

    // Skip API calls & admin routes (selalu ambil dari network)
    if (url.pathname.startsWith('/api/') || url.pathname.startsWith('/admin/')) {
        event.respondWith(
            fetch(event.request).catch(() => caches.match(event.request))
        );
        return;
    }

    // Cache-first untuk aset statis (CSS, JS, font, gambar)
    if (
        url.pathname.match(/\.(css|js|woff2?|ttf|png|jpg|jpeg|webp|svg|ico)$/) ||
        url.hostname !== location.hostname
    ) {
        event.respondWith(
            caches.match(event.request).then(cached =>
                cached || fetch(event.request).then(response => {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                    return response;
                })
            )
        );
        return;
    }

    // Network-first untuk halaman HTML
    event.respondWith(
        fetch(event.request)
            .then(response => {
                const clone = response.clone();
                caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                return response;
            })
            .catch(() => caches.match(event.request))
    );
});

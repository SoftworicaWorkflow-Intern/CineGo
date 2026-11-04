/*
 * CineGo service worker.
 *
 * Strategy
 *  - Static assets (css/js/fonts/images): cache-first, refreshed in the background.
 *  - Public page navigations: network-first, cached copy only as an offline fallback.
 *  - Booking, payment, account and admin requests are NEVER cached — they always
 *    go straight to the network so seats, prices and personal data stay fresh.
 */

const STATIC_CACHE = 'cinego-static-v1';
const PAGE_CACHE = 'cinego-pages-v1';
const MAX_PAGE_ENTRIES = 25;

const NEVER_CACHE = ['/account', '/booking', '/payment', '/admin', '/checkout', '/ticket'];

const PRECACHE_ASSETS = ['/', '/manifest.webmanifest', '/img/icons/icon.svg'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches
            .open(STATIC_CACHE)
            .then((cache) => cache.addAll(PRECACHE_ASSETS))
            .then(() => self.skipWaiting()),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((keys) =>
                Promise.all(
                    keys
                        .filter((key) => key !== STATIC_CACHE && key !== PAGE_CACHE)
                        .map((key) => caches.delete(key)),
                ),
            )
            .then(() => self.clients.claim()),
    );
});

function isSensitive(url) {
    return NEVER_CACHE.some((prefix) => url.pathname.startsWith(prefix));
}

async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) {
        return cached;
    }

    const response = await fetch(request);
    if (response && response.ok) {
        const cache = await caches.open(STATIC_CACHE);
        cache.put(request, response.clone());
    }

    return response;
}

async function networkFirst(request) {
    try {
        const response = await fetch(request);
        if (response && response.ok) {
            const cache = await caches.open(PAGE_CACHE);
            cache.put(request, response.clone());

            const keys = await cache.keys();
            if (keys.length > MAX_PAGE_ENTRIES) {
                await cache.delete(keys[0]);
            }
        }
        return response;
    } catch (error) {
        const cached = await caches.match(request);
        if (cached) {
            return cached;
        }

        const offline = await caches.match('/');
        if (offline) {
            return offline;
        }

        return new Response('You are offline.', {
            status: 503,
            headers: { 'Content-Type': 'text/plain; charset=utf-8' },
        });
    }
}

self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    if (request.method !== 'GET' || url.origin !== self.location.origin) {
        return;
    }

    if (isSensitive(url)) {
        return;
    }

    const destination = request.destination;

    if (destination === 'style' || destination === 'script' || destination === 'image' || destination === 'font') {
        event.respondWith(cacheFirst(request));
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(networkFirst(request));
    }
});

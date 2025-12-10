// Versioned cache
const CACHE = "admin-pwa-v1";

self.addEventListener("install", event => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE).then(cache =>
            cache.addAll([
                '/offline-admin',
                '/images/icons/icon-192.png',
                '/images/icons/icon-512.png',
            ])
        )
    );
});

self.addEventListener("activate", event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
        )
    );
    self.clients.claim();
});

self.addEventListener("fetch", event => {
    if (event.request.method !== "GET") return;

    event.respondWith(
        caches.match(event.request).then(cached =>
            cached || fetch(event.request).catch(() => caches.match('/offline-admin'))
        )
    );
});

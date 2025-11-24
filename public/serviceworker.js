// Versioned cache name
const staticCacheName = "pwa-v" + new Date().getTime();

// Only cache resources that are static and do NOT change between builds.
const filesToCache = [
    '/offline',
    '/images/icons/icon-72x72.png',
    '/images/icons/icon-96x96.png',
    '/images/icons/icon-128x128.png',
    '/images/icons/icon-144x144.png',
    '/images/icons/icon-152x152.png',
    '/images/icons/icon-192x192.png',
    '/images/icons/icon-384x384.png',
    '/images/icons/icon-512x512.png',
];

// Cache on install
self.addEventListener("install", event => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(staticCacheName).then(cache => cache.addAll(filesToCache))
    );
});

// Clear old caches
self.addEventListener("activate", event => {
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys
                    .filter(name => name.startsWith("pwa-"))
                    .filter(name => name !== staticCacheName)
                    .map(name => caches.delete(name))
            );
        })
    );
    self.clients.claim();
});

// Serve from cache, fallback to offline
self.addEventListener("fetch", event => {

    const url = new URL(event.request.url);

    if (url.pathname.startsWith('/api/')) return;
    if (event.request.method !== 'GET') return;

    event.respondWith(
        caches.match(event.request).then(res => 
            res || fetch(event.request).catch(() => caches.match('/offline'))
        )
    );
});

// Push notifications
self.addEventListener("push", event => {
    let data = {};

    try { data = event.data.json(); }
    catch { data = { title: event.data.text() || "Fleet Manager", body: "" }; }

    event.waitUntil(
        self.registration.showNotification(
            data.title,
            {
                body: data.body || "",
                icon: "/images/icons/icon-192x192.png",
                badge: "/images/icons/icon-72x72.png",
                data: data.data || {}
            }
        )
    );
});

self.addEventListener("notificationclick", event => {
    event.notification.close();
    const url = event.notification.data.url || "/";
    event.waitUntil(clients.openWindow(url));
});

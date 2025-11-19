// Versioned cache name
const staticCacheName = "pwa-v" + new Date().getTime();

// Only cache resources that are static and do NOT change between builds.
// Vite assets НЕ добавляем, их имена хэшируются и часто меняются.
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
        caches.open(staticCacheName).then(cache => {
            return cache.addAll(filesToCache);
        })
    );
});

// Clear old caches
self.addEventListener("activate", event => {
    event.waitUntil(
        caches.keys().then(names => {
            return Promise.all(
                names
                    .filter(name => name.startsWith("pwa-"))
                    .filter(name => name !== staticCacheName)
                    .map(name => caches.delete(name))
            );
        })
    );
    self.clients.claim();
});

// Serve from cache, fallback to offline page
self.addEventListener("fetch", event => {

    const url = new URL(event.request.url);

    // 🚫 1) НЕ перехватываем API-запросы
    if (url.pathname.startsWith('/api/')) {
        return; // запрос пойдет напрямую в интернет → Laravel получит POST
    }

    // 🚫 2) НЕ перехватываем POST-запросы вообще
    if (event.request.method !== 'GET') {
        return;
    }

    event.respondWith(
        caches.match(event.request).then(response => {
            return response || fetch(event.request).catch(() => {
                return caches.match('/offline');
            });
        })
    );
});

self.addEventListener("push", event => {
    if (!event.data) return;

    const data = event.data.json();

    event.waitUntil(
        self.registration.showNotification(
            data.title || "Fleet Manager",
            {
                body: data.body || "",
                icon: "/icons/icon-192x192.png",
                badge: "/icons/badge-72x72.png",
                data: data.data || {},
            }
        )
    );
});

self.addEventListener("notificationclick", event => {
    event.notification.close();
    const url = event.notification.data.url || "/";
    
    event.waitUntil(
        clients.openWindow(url)
    );
});

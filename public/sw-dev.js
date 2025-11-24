console.log("SW-DEV active");

self.addEventListener("install", () => self.skipWaiting());
self.addEventListener("activate", () => {
    caches.keys().then(keys => keys.map(k => caches.delete(k)));
    self.clients.claim();
});

// No caching, no intercepting
self.addEventListener("fetch", () => {});

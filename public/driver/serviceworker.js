// Driver PWA SW (safe for Livewire/Vite)
const CACHE = "driver-pwa-v2";

const OFFLINE_URL = "/driver/offline";
const PRECACHE = [
  OFFLINE_URL,
  "/images/icons/icon-192.png",
  "/images/icons/icon-512.png",
];

self.addEventListener("install", (event) => {
  self.skipWaiting();
  event.waitUntil(caches.open(CACHE).then((cache) => cache.addAll(PRECACHE)));
});

self.addEventListener("activate", (event) => {
  event.waitUntil((async () => {
    const keys = await caches.keys();
    await Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k)));
    await self.clients.claim();
  })());
});

self.addEventListener("fetch", (event) => {
  const req = event.request;

  if (req.method !== "GET") return;

  const url = new URL(req.url);
  if (url.origin !== self.location.origin) return;

  // ❌ Never cache Livewire/Vite/API
  if (
    url.pathname.startsWith("/livewire/") ||
    url.pathname.startsWith("/build/") ||
    url.pathname.startsWith("/__vite") ||
    url.pathname.includes("livewire.js") ||
    url.pathname.startsWith("/api/")
  ) {
    return;
  }

  // ✅ HTML navigation
  if (req.mode === "navigate") {
    event.respondWith(
      fetch(req).catch(() => caches.match(OFFLINE_URL))
    );
    return;
  }

  // ✅ Assets cache-first
  event.respondWith(
    caches.match(req).then((cached) => cached || fetch(req).then((res) => {
      const copy = res.clone();
      caches.open(CACHE).then((cache) => cache.put(req, copy));
      return res;
    }).catch(() => cached))
  );
});

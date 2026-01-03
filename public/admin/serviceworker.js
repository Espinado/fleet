const CACHE = "admin-pwa-v2";
const OFFLINE_PAGE = "/offline-admin";

const ASSETS = [
  OFFLINE_PAGE,
  "/images/icons/icon-192.png",
  "/images/icons/icon-512.png"
];

// ===============================
// INSTALL
// ===============================
self.addEventListener("install", event => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE).then(c => c.addAll(ASSETS))
  );
});

// ===============================
// ACTIVATE
// ===============================
self.addEventListener("activate", event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
    )
  );
  self.clients.claim();
});

// ===============================
// FETCH
// ===============================
self.addEventListener("fetch", event => {
  if (event.request.method !== "GET") return;

  const url = new URL(event.request.url);

  // ❌ НИКОГДА не трогаем driver
  if (url.pathname.startsWith("/driver")) return;

  // ❌ auth
  if (url.pathname === "/login" || url.pathname === "/logout") return;

  // ❌ API / Livewire
  if (
    url.pathname.startsWith("/api") ||
    url.pathname.includes("/livewire")
  ) return;

  // ❌ HTML — всегда из сети
  if (event.request.headers.get("accept")?.includes("text/html")) {
    return;
  }

  event.respondWith(
    caches.match(event.request)
      .then(r => r || fetch(event.request))
      .catch(() => caches.match(OFFLINE_PAGE))
  );
});

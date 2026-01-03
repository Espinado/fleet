const CACHE = "driver-pwa-v5";
const OFFLINE_PAGE = "/offline";

const ASSETS = [
  OFFLINE_PAGE,
  "/images/icons/icon-192.png",
  "/images/icons/icon-512.png"
];

// ----------------------------
// Install
// ----------------------------
self.addEventListener("install", event => {
  self.skipWaiting();
  event.waitUntil(caches.open(CACHE).then(cache => cache.addAll(ASSETS)));
});

// ----------------------------
// Activate
// ----------------------------
self.addEventListener("activate", event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
    )
  );
  self.clients.claim();
});

// ----------------------------
// Helpers
// ----------------------------
function isHtmlNavigation(request) {
  return request.mode === "navigate" ||
    (request.headers.get("accept") || "").includes("text/html");
}

function shouldBypass(url) {
  // Важные страницы/файлы, которые нельзя кэшировать
  if (url.pathname === "/login") return true;
  if (url.pathname === "/logout") return true;
  if (url.pathname === "/serviceworker.js") return true;
  if (url.pathname === "/manifest.webmanifest") return true;

  // Livewire / API / Vite — напрямую
  if (url.pathname.includes("/livewire/")) return true;
  if (url.pathname.startsWith("/api/")) return true;
  if (url.pathname.startsWith("/@vite")) return true;

  return false;
}

async function fetchNoCache(request) {
  // Всегда просим свежие данные
  return fetch(request, { cache: "no-store" });
}

// ----------------------------
// Fetch
// ----------------------------
self.addEventListener("fetch", event => {
  const req = event.request;

  // ✅ все POST (Livewire / forms) — всегда в сеть
  if (req.method === "POST") {
    event.respondWith(fetch(req));
    return;
  }

  if (req.method !== "GET") return;

  const url = new URL(req.url);

  // ✅ Bypass критичных путей
  if (shouldBypass(url)) {
    event.respondWith(fetchNoCache(req));
    return;
  }

  // ✅ HTML навигация: NETWORK-FIRST (важно для CSRF / session)
  if (isHtmlNavigation(req)) {
    event.respondWith((async () => {
      try {
        const res = await fetchNoCache(req);

        // Никогда не кэшируем ответы, которые меняют cookies
        if (res && res.headers && res.headers.get("set-cookie")) {
          return res;
        }

        return res;
      } catch (e) {
        const cached = await caches.match(req);
        return cached || caches.match(OFFLINE_PAGE);
      }
    })());
    return;
  }

  // ✅ Остальное (картинки/стили/скрипты): CACHE-FIRST
  event.respondWith((async () => {
    const cached = await caches.match(req);
    if (cached) return cached;

    try {
      const res = await fetch(req);

      // Не кэшируем если сервер сетит cookies
      if (res && res.headers && res.headers.get("set-cookie")) {
        return res;
      }

      // Кэшируем только успешные ответы
      if (res && res.ok) {
        const cache = await caches.open(CACHE);
        cache.put(req, res.clone());
      }

      return res;
    } catch (e) {
      return caches.match(OFFLINE_PAGE);
    }
  })());
});

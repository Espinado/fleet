// Root SW (scope /) — только для Web Push. Контролирует /dashboard и все страницы админки.
self.addEventListener("push", function (event) {
  const data = event.data ? event.data.json() : {};
  const title = data.title || "Fleet";
  const options = {
    body: data.body || "",
    icon: data.icon || "/images/icons/icon-192.png",
    badge: data.badge || "/images/icons/icon-72.png",
    tag: data.tag || "fleet-push",
    data: { url: data.url || "/" },
  };
  event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener("notificationclick", function (event) {
  event.notification.close();
  const url = event.notification.data && event.notification.data.url;
  if (url) {
    event.waitUntil(
      clients.matchAll({ type: "window", includeUncontrolled: true }).then(function (list) {
        if (list.length) list[0].focus();
        else if (clients.openWindow) return clients.openWindow(url);
      })
    );
  }
});

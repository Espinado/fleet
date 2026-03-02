function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const raw = atob(base64);
    const output = new Uint8Array(raw.length);
    for (let i = 0; i < raw.length; ++i) output[i] = raw.charCodeAt(i);
    return output;
}

async function subscribeForPush() {
    if (!("serviceWorker" in navigator && "PushManager" in window)) {
        if (typeof window.fleetPushMessage === "function") window.fleetPushMessage("Браузер не поддерживает push.");
        return false;
    }
    var key = typeof VAPID_PUBLIC_KEY !== "undefined" ? VAPID_PUBLIC_KEY : (window.VAPID_PUBLIC_KEY || null);
    if (!key) {
        if (typeof window.fleetPushMessage === "function") window.fleetPushMessage("VAPID ключ не задан. Настройте .env.");
        return false;
    }
    var permission = await Notification.requestPermission();
    if (permission !== "granted") {
        if (typeof window.fleetPushMessage === "function") window.fleetPushMessage("Уведомления запрещены.");
        return false;
    }
    var reg = await navigator.serviceWorker.ready;
    var sub = await reg.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(key),
    });
    var json = sub.toJSON();
    var csrf = document.querySelector('meta[name="csrf-token"]');
    var headers = { "Content-Type": "application/json", "X-Requested-With": "XMLHttpRequest", "Accept": "application/json" };
    if (csrf && csrf.content) headers["X-CSRF-TOKEN"] = csrf.content;
    var res = await fetch("/push/subscribe", {
        method: "POST",
        headers: headers,
        credentials: "include",
        body: JSON.stringify({
            endpoint: json.endpoint,
            public_key: json.keys.p256dh,
            auth_token: json.keys.auth,
            content_encoding: (PushManager.supportedContentEncodings && PushManager.supportedContentEncodings[0]) || "aesgcm",
        }),
    });
    if (!res.ok) {
        if (typeof window.fleetPushMessage === "function") window.fleetPushMessage("Ошибка подписки. Проверьте вход в админку.");
        return false;
    }
    if (typeof window.fleetPushMessage === "function") window.fleetPushMessage("Уведомления включены.");
    return true;
}

window.subscribeForPush = subscribeForPush;

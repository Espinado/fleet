function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const raw = atob(base64);
    const output = new Uint8Array(raw.length);
    for (let i = 0; i < raw.length; ++i) output[i] = raw.charCodeAt(i);
    return output;
}

async function subscribeForPush() {
    var msg = function (text) {
        if (typeof window.fleetPushMessage === "function") window.fleetPushMessage(text);
        if (typeof window.fleetPushStatus === "function") window.fleetPushStatus(text);
    };
    try {
        if (!("serviceWorker" in navigator && "PushManager" in window)) {
            msg("Браузер не поддерживает push.");
            return false;
        }
        var key = (typeof window !== "undefined" && window.VAPID_PUBLIC_KEY) || (typeof VAPID_PUBLIC_KEY !== "undefined" ? VAPID_PUBLIC_KEY : null);
        if (!key) {
            msg("VAPID ключ не задан. Настройте .env и config:clear.");
            return false;
        }
        var permission = Notification.permission;
        if (permission === "denied") {
            msg("Уведомления заблокированы. Разрешите в настройках: иконка замка в адресной строке → Настройки сайта → Уведомления → Разрешить.");
            return false;
        }
        if (permission !== "granted") {
            msg("Запрос разрешения…");
            permission = await Notification.requestPermission();
            if (permission !== "granted") {
                var hintFull = "Включите уведомления вручную:\n\n1. Нажмите значок замка (или «i») слева от адреса в адресной строке.\n2. Найдите «Уведомления» и выберите «Разрешить».\n3. Обновите страницу (F5) и снова нажмите «Включить уведомления».";
                if (permission === "denied") {
                    msg("Запрещено. Замок → Уведомления → Разрешить.");
                } else {
                    msg("Включите в настройках сайта (замок → Уведомления).");
                }
                alert(hintFull);
                return false;
            }
        }
        msg("Ожидание Service Worker…");
        var reg = await navigator.serviceWorker.ready;
        msg("Подписка…");
        var sub = await reg.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(key),
        });
        var json = sub.toJSON();
        if (!json.keys || !json.endpoint) {
            msg("Ошибка: нет ключей подписки.");
            return false;
        }
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
        var text = await res.text();
        if (!res.ok) {
            var err = "Ошибка " + res.status;
            try { var j = JSON.parse(text); if (j.message) err = j.message; } catch (e) {}
            if (res.status === 419) err = "Сессия истекла. Обновите страницу (F5) и нажмите снова.";
            if (res.status === 401) err = "Войдите в админку заново.";
            msg(err);
            return false;
        }
        msg("Уведомления включены.");
        if (typeof window.fleetPushSuccess === "function") window.fleetPushSuccess();
        return true;
    } catch (e) {
        msg("Ошибка: " + (e.message || String(e)));
        return false;
    }
}

window.subscribeForPush = subscribeForPush;

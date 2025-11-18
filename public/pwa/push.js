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
        console.log("❌ Push not supported");
        return;
    }

    const permission = await Notification.requestPermission();
    if (permission !== "granted") {
        console.log("❌ Notification permission denied");
        return;
    }

    const registration = await navigator.serviceWorker.ready;

    const sub = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY),
    });

    const json = sub.toJSON();

    await fetch("/api/push/subscribe", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        credentials: "include",
        body: JSON.stringify({
            endpoint: json.endpoint,
            public_key: json.keys.p256dh,
            auth_token: json.keys.auth,
            content_encoding: (PushManager.supportedContentEncodings || ["aesgcm"])[0],
        }),
    });

    console.log("✅ PUSH SUBSCRIBED");
}

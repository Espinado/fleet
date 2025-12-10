<!DOCTYPE html>
<html lang="ru" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

    <title>{{ $title ?? 'Driver App' }}</title>

    {{-- Favicons / PWA icons --}}
    <link rel="icon" href="/images/icons/icon-192.png">
    <link rel="apple-touch-icon" href="/images/icons/icon-512.png">

    <!-- DRIVER PWA -->
    <link rel="manifest" href="/driver/manifest.webmanifest">

    <!-- iOS fullscreen -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <!-- Android theme color -->
    <meta name="theme-color" content="#0066ff">

    {{-- Driver App assets (полностью независимые от админки) --}}
    @vite([
        'resources/driver/css/app.css',
        'resources/driver/js/app.js'
    ])

    @livewireStyles
    <script>
if ('serviceWorker' in navigator) {
    if (!location.hostname.includes('localhost')) {
        navigator.serviceWorker.register('/driver/serviceworker.js');
    }
}
</script>
</head>

<body class="bg-gray-100 h-full">

    <!-- Driver Content -->
    <main class="min-h-screen pb-20">
        {{ $slot }}
    </main>

    {{-- Bottom navigation --}}
    @php
        $currentTrip = auth()->user()?->driver?->activeTrip()->first();
    @endphp

    @include('driver-app.components.bottombar', [
        'currentTripId' => $currentTrip?->id
    ])

    @livewireScripts

    <!-- REGISTER DRIVER SERVICE WORKER -->
    <script>
        if ("serviceWorker" in navigator) {
            window.addEventListener("load", () => {
                const isLocal =
                    location.hostname === "localhost" ||
                    location.hostname === "127.0.0.1" ||
                    location.hostname.endsWith(".test");

                if (isLocal) {
                    console.log("Driver SW disabled on local");
                    return;
                }

                navigator.serviceWorker.register("/driver/serviceworker.js", {
                    scope: "/driver/"
                })
                .then(() => console.log("Driver SW loaded"))
                .catch(err => console.warn("Driver SW error:", err));
            });
        }
    </script>

</body>
</html>

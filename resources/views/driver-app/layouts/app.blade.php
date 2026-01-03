<!DOCTYPE html>
<html lang="ru" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="viewport"
          content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

    <title>{{ $title ?? 'Fleet Driver' }}</title>

    {{-- =======================
         PWA / Icons
    ======================== --}}
    <link rel="icon" href="/images/icons/icon-192.png">
    <link rel="apple-touch-icon" href="/images/icons/icon-512.png">
    <link rel="manifest" href="/manifest.webmanifest">

    {{-- iOS PWA --}}
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Fleet Driver">

    {{-- Android --}}
    <meta name="theme-color" content="#0066ff">
    <meta name="application-name" content="Fleet Driver">

    {{-- =======================
         Assets (driver only)
    ======================== --}}
    @vite([
        'resources/driver/css/app.css',
        'resources/driver/js/app.js'
    ])

    @livewireStyles
</head>

<body class="bg-gray-100 h-full">

    {{-- =======================
         Main content
    ======================== --}}
    <main class="min-h-screen {{ auth('driver')->check() ? 'pb-20' : '' }}">
        {{ $slot }}
    </main>

    {{-- =======================
         Bottom navigation (ONLY when logged in)
    ======================== --}}
    @auth('driver')
        @php
            $currentTrip = auth('driver')->user()?->driver?->activeTrip()->first();
        @endphp

        @include('driver-app.components.bottombar', [
            'currentTripId' => $currentTrip?->id
        ])
    @endauth

    @livewireScripts

    {{-- =======================
         Service Worker (driver only)
    ======================== --}}
    <script>
        if ("serviceWorker" in navigator) {
            window.addEventListener("load", () => {
                navigator.serviceWorker.register("/serviceworker.js", { scope: "/" })
                    .then(() => console.log("✅ Driver SW registered"))
                    .catch(err => console.warn("❌ Driver SW error:", err));
            });
        }
    </script>

</body>
</html>

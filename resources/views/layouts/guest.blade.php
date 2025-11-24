<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Fleet Manager') }}</title>

    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-gray-900 antialiased bg-gray-100">

    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
        <div>{{-- logo here if needed --}}</div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md sm:rounded-lg">
            {{ $slot }}
        </div>
    </div>

    @livewireScripts
    <script>
if ('serviceWorker' in navigator) {
    window.addEventListener("load", () => {
        const isLocal =
            location.hostname === "localhost" ||
            location.hostname === "127.0.0.1" ||
            location.hostname.endsWith(".test");

        // локалка → sw-dev.js, прод → sw.js
        const swFile = isLocal ? "/sw-dev.js" : "/serviceworker.js";

        navigator.serviceWorker.register(swFile)
            .then(reg => console.log("Service Worker registered:", swFile))
            .catch(err => console.warn("SW registration failed:", err));
    });
}
</script>

</body>
</html>

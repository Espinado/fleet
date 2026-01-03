<!DOCTYPE html>
<html lang="ru" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title ?? 'Driver Login' }}</title>

    @vite(['resources/driver/css/app.css', 'resources/driver/js/app.js'])

    @livewireStyles
</head>

<body class="bg-gray-100 text-gray-900 h-full">

    <div class="min-h-screen flex items-center justify-center p-4">
        {{ $slot }}
    </div>

    @livewireScripts
</body>
</html>

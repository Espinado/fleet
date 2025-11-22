<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @vite(['resources/driver/css/app.css', 'resources/driver/js/app.js'])
    <title>{{ $title ?? 'Driver Login' }}</title>
</head>

<body class="bg-gray-100 text-gray-900">

    <div class="min-h-screen flex items-center justify-center p-4">
        {{ $slot }}
    </div>

</body>
</html>

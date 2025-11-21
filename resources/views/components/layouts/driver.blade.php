<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1, maximum-scale=1">

    <title>Driver App</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="bg-gray-100 min-h-screen pb-16">

<main class="p-4">
    {{ $slot }}
</main>

@auth
<nav class="fixed bottom-0 left-0 right-0 bg-white border-t shadow-lg py-2 flex justify-around text-center">
    <a href="{{ route('driver.dashboard') }}" class="flex flex-col items-center text-gray-700">
        <span class="text-xl">🏠</span>
        <span class="text-xs">Главная</span>
    </a>

    <a href="#"
       onclick="document.getElementById('logoutDriver').submit()"
       class="flex flex-col items-center text-red-600">
        <span class="text-xl">🚪</span>
        <span class="text-xs">Выход</span>
    </a>

    <form id="logoutDriver" method="POST" action="{{ route('driver.logout') }}">
        @csrf
    </form>
</nav>
@endauth

@livewireScripts
</body>
</html>

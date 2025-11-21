<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <title>Driver App</title>

    @vite(['resources/css/app.css','resources/js/app.js'])
    @livewireStyles
</head>

<body class="bg-gray-100 min-h-screen flex flex-col">

    {{-- Контент Livewire-компонентов --}}
    <main class="flex-1 p-4">
        {{ $slot }}
    </main>

    {{-- Нижнее меню --}}
    <nav class="bg-white border-t h-16 flex justify-around items-center shadow-xl">
        <a href="{{ route('driver.dashboard') }}"
           class="text-center text-sm flex flex-col items-center">
            🏠
            <span>Главная</span>
        </a>

        <form method="POST" action="{{ route('driver.logout') }}" class="text-center text-sm flex flex-col items-center">
            @csrf
            <button type="submit" class="text-red-600">
                🚪
                <span>Выход</span>
            </button>
        </form>
    </nav>

    @livewireScripts
</body>
</html>

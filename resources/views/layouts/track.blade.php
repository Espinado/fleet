<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('app.track.title')) — {{ config('app.name', 'Fleet Manager') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-gray-900 antialiased bg-gray-100 min-h-screen">
    <div class="min-h-screen py-6 px-4 sm:px-6 lg:px-8">
        @yield('content')
    </div>
</body>
</html>

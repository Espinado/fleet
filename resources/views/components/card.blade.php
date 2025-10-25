@props([
    'title' => null,
])

<div class="bg-white shadow rounded-lg p-6 relative">
    @if($title)
        <h2 class="text-xl font-semibold mb-4">{{ $title }}</h2>
    @endif

    {{ $slot }}
</div>


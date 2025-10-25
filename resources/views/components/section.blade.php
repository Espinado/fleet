@props([
    'title' => null,
    'icon' => null,
])

<div class="mt-8">
    <div class="flex items-center gap-2 mb-3 border-b pb-1">
        @if($icon)
            <span class="text-blue-600">{{ $icon }}</span>
        @endif
        @if($title)
            <h3 class="text-lg font-semibold text-gray-800">{{ $title }}</h3>
        @endif
    </div>

    <div class="space-y-4">
        {{ $slot }}
    </div>
</div>

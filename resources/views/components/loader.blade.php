@props([
    'target' => null,
    'size' => '8',
])

<div wire:loading.flex @if($target) wire:target="{{ $target }}" @endif
     class="absolute inset-0 bg-white/60 backdrop-blur-sm flex items-center justify-center z-10 rounded">
    <div class="animate-spin rounded-full border-4 border-blue-500 border-t-transparent"
         style="width: {{ $size }}rem; height: {{ $size }}rem;">
    </div>
</div>

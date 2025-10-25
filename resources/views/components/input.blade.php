@props([
    'label' => null,
    'type' => 'text',
    'model' => null,
    'step' => null,
    'placeholder' => null,
])

<div>
    @if($label)
        <label class="block text-sm font-medium mb-1">{{ $label }}</label>
    @endif

    <input
        type="{{ $type }}"
        wire:model="{{ $model }}"
        @if($step) step="{{ $step }}" @endif
        placeholder="{{ $placeholder }}"
        class="w-full border rounded p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
    />

    @error($model)
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

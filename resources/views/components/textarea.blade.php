@props([
    'label' => null,
    'model' => null,
    'placeholder' => null,
    'rows' => 3,
])

<div>
    @if($label)
        <label class="block text-sm font-medium mb-1">{{ $label }}</label>
    @endif

    <textarea
        wire:model="{{ $model }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        class="w-full border rounded p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
    ></textarea>

    @error($model)
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

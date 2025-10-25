@props([
    'label' => null,
    'model' => null,        // имя свойства Livewire (например expeditorId)
    'options' => [],        // ['value' => 'Label']
    'live' => false,        // булев флаг для wire:model.live
    'placeholder' => 'Select option',
])

@php
    $isLive = filter_var($live, FILTER_VALIDATE_BOOLEAN);
    $inputId = $attributes->get('id') ?? 'select_' . str_replace(['.', '[', ']'], '_', $model);
@endphp

<div class="flex flex-col">
    @if($label)
        <label for="{{ $inputId }}" class="text-sm font-medium mb-1">{{ $label }}</label>
    @endif

    <select
        id="{{ $inputId }}"
        name="{{ $model }}"
        @if($isLive)
            wire:model.live="{{ $model }}"
        @else
            wire:model="{{ $model }}"
        @endif
        {{ $attributes->class('w-full border rounded px-3 py-2 focus:ring focus:ring-blue-200') }}
    >
        <option value="">{{ $placeholder }}</option>
        @foreach($options as $key => $value)
            <option value="{{ $key }}">{{ $value }}</option>
        @endforeach
    </select>

    @error($model)
        <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
    @enderror
</div>

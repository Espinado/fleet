{{-- Полноэкранный оверлей загрузки (документы/расходы), в т.ч. для PWA. Использование: @include('components.upload-loading-overlay', ['targets' => 'documentFile,saveDocument']) --}}
@props(['targets' => 'file,saveDocument'])

<div wire:loading.flex
     wire:target="{{ $targets }}"
     class="fixed inset-0 z-[200] flex items-center justify-center bg-white/90 dark:bg-gray-900/90 backdrop-blur-sm"
     aria-live="polite"
     aria-label="{{ __('app.please_wait') }}">
    @include('components.upload-loading-spinner-box')
</div>

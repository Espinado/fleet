{{-- Спиннер + текст «Please wait» (общий фрагмент для оверлея) --}}
<div class="flex flex-col items-center gap-4 p-8 rounded-2xl bg-white dark:bg-gray-800 shadow-xl border border-gray-200 dark:border-gray-700">
    <svg class="animate-spin h-12 w-12 text-indigo-600 dark:text-indigo-400 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    <span class="font-semibold text-gray-800 dark:text-gray-200 text-lg">{{ __('app.please_wait') }}</span>
</div>

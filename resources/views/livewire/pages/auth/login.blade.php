<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        $this->validate();
        $this->form->authenticate();
        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
};
?>

<div class="min-h-screen flex flex-col justify-center items-center bg-gradient-to-br from-gray-100 to-gray-200 p-4">

    <div class="w-full max-w-md space-y-8">
        {{-- Логотип --}}
        <div class="flex flex-col items-center animate-fade-in">
            <img src="{{ asset('images/icons/fleet.png') }}" alt="Fleet Manager" class="w-16 h-16 rounded-2xl shadow-md mb-3">
            <h1 class="text-2xl font-semibold text-gray-800 tracking-tight">Fleet Manager</h1>
            <!-- <p class="text-gray-500 text-sm">Sign in to continue</p> -->
        </div>

        {{-- Карточка входа --}}
        <div class="bg-white rounded-2xl shadow-lg p-6 space-y-6 animate-slide-up">
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form wire:submit="login" class="space-y-4">
                {{-- Email --}}
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input
                        wire:model="form.email"
                        id="email"
                        type="email"
                        required
                        autofocus
                        autocomplete="username"
                        class="block w-full mt-1 rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                        placeholder="you@example.com"
                    />
                    <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
                </div>

                {{-- Password --}}
                <div>
                    <x-input-label for="password" :value="__('Password')" />
                    <x-text-input
                        wire:model="form.password"
                        id="password"
                        type="password"
                        required
                        autocomplete="current-password"
                        class="block w-full mt-1 rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                        placeholder="••••••••"
                    />
                    <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
                </div>

                {{-- Submit --}}
                <div class="flex items-center justify-end">
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="relative inline-flex items-center justify-center px-6 py-2 text-white font-semibold rounded-lg bg-blue-600 hover:bg-blue-700 transition disabled:opacity-70 disabled:cursor-not-allowed"
                    >
                        {{-- Spinner при загрузке --}}
                        <svg wire:loading class="animate-spin h-5 w-5 mr-2 text-white absolute left-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>

                        {{-- Текст — меняется при загрузке --}}
                        <span wire:loading.remove>{{ __('Log in') }}</span>
                        <span wire:loading>{{ __('Signing in...') }}</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- Footer --}}
        <p class="text-xs text-gray-500 text-center mt-6">
            © {{ now()->year }} Fleet Manager 
        </p>
    </div>

    {{-- Анимации --}}
    <style>
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slide-up {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in { animation: fade-in 0.6s ease-out both; }
        .animate-slide-up { animation: slide-up 0.6s ease-out both; }
    </style>
</div>

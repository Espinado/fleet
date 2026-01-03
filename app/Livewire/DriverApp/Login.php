<?php

namespace App\Livewire\DriverApp;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Driver;

class Login extends Component
{
    public string $pin = '';

    protected function rules(): array
    {
        return [
            'pin' => ['required', 'digits_between:4,6'],
        ];
    }

    public function login()
    {
        $this->validate();

        $driver = Driver::where('login_pin', $this->pin)->first();

        if (!$driver || !$driver->user) {
            Log::warning('Driver login failed: invalid PIN', [
                'pin' => $this->pin,
                'host' => request()->getHost(),
            ]);

            $this->addError('pin', 'Неверный PIN');
            return;
        }

        // ✅ логиним в driver guard + "remember" чтобы надёжнее держалось
        Auth::guard('driver')->login($driver->user, true);

        Log::info('Driver login success', [
            'driver_id' => $driver->id,
            'user_id' => $driver->user->id,
            'driver_guard_check' => Auth::guard('driver')->check(),
            'driver_guard_id' => Auth::guard('driver')->id(),
            'default_guard_check' => Auth::check(),
            'session_id' => session()->getId(),
            'host' => request()->getHost(),
        ]);

       return redirect('/dashboard');
    }

    public function render()
    {
        return view('driver-app.pages.login')
            ->layout('driver-app.layouts.auth', [
                'title' => 'Авторизация водителя',
            ]);
    }
}

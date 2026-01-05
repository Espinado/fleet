<?php

namespace App\Livewire\DriverApp;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Driver;

class Login extends Component
{
    public $pin = '';

    public function login()
    {
        $this->validate([
            'pin' => 'required|digits_between:4,6',
        ], [
            'pin.required' => 'Введите PIN',
            'pin.digits_between' => 'PIN должен быть от 4 до 6 цифр',
        ]);

        $driver = Driver::whereNotNull('login_pin')
            ->where('login_pin', $this->pin)
            ->first();

        if (!$driver || !$driver->user) {
            $this->addError('pin', 'Неверный PIN');
            return;
        }
Auth::guard('driver')->login($driver->user);
request()->session()->regenerate();

return redirect('/driver/dashboard');
    }

    public function render()
    {
         return view('driver-app.pages.login')
        ->layout('driver-app.layouts.auth', [
            'title' => 'Авторизация водителя'
        ]);
    }
}

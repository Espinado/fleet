<?php

namespace App\Livewire\DriverApp;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class Profile extends Component
{
    use WithFileUploads;

    public $driver;
    public $user;

    public $photo;
    public $phone;
    public $email;

    public function mount()
    {
        $this->user = Auth::user();
        $this->driver = $this->user->driver;

        $this->phone = $this->driver->phone;
        $this->email = $this->driver->email;
    }

    public function save()
    {
        $this->validate([
            'phone' => 'required|string|max:32',
            'email' => 'nullable|email|max:255',
            'photo' => 'nullable|image|max:20480'
        ]);

        if ($this->photo) {
            $path = $this->photo->store('driver-photos', 'public');
            $this->driver->photo = $path;
        }

        $this->driver->phone = $this->phone;
        $this->driver->email = $this->email;
        $this->driver->save();

        session()->flash('success', 'Профиль обновлён');
    }

    public function render()
    {
        return view('driver-app.pages.profile')
            ->layout('driver-app.layouts.app', [
                'title' => 'Профиль'
            ]);
    }
}

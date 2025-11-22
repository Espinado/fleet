<?php

namespace App\Livewire\DriverApp;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\TripDocument;

class ViewDocument extends Component
{
    public TripDocument $document;

    public function mount($document)
    {
        $user = Auth::user();
        if (!$user || !$user->driver) {
            return redirect()->route('driver.login');
        }

        $this->document = TripDocument::findOrFail($document);
    }

    public function render()
    {
        return view('driver-app.pages.view-document', [
            'title' => $this->document->name,
        ])->layout('driver-app.layouts.app', [
            'title' => 'Документ',
            'back' => true
        ]);
    }
}

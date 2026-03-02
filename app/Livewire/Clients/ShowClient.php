<?php

namespace App\Livewire\Clients;

use Livewire\Component;
use App\Models\Client;

class ShowClient extends Component
{
    public Client $client;
    public bool $confirmingDelete = false;

    public function delete()
    {
        $this->client->delete();

        session()->flash('success', __('app.client.show.deleted_success', [], 'lv'));
        return redirect()->route('clients.index');
    }

    public function render()
    {
        return view('livewire.clients.show-client')
            ->layout('layouts.app', [
                'title' => __('app.clients.title'),
            ]);
    }
}

<?php

namespace App\Livewire\Clients;

use Livewire\Component;
use App\Models\Client;

class EditClient extends Component
{
    public $client;
    public $form = [];

    public function mount($client)
    {
        $this->client = Client::findOrFail($client);

        // Копируем все поля из модели в массив формы
        $this->form = $this->client->only([
            'company_name',
            'reg_nr',
            'jur_country',
            'jur_city',
            'jur_address',
            'jur_post_code',
            'fiz_country',
            'fiz_city',
            'fiz_address',
            'fiz_post_code',
            'bank_name',
            'swift',
            'email',
            'phone',
            'representative',
        ]);
    }

    protected $rules = [
        'form.company_name'   => 'required|string|max:255',
        'form.reg_nr'         => 'nullable|string|max:50',
        'form.jur_country'    => 'nullable|string|max:100',
        'form.jur_city'       => 'nullable|string|max:100',
        'form.jur_address'    => 'nullable|string|max:255',
        'form.jur_post_code'  => 'nullable|string|max:20',
        'form.fiz_country'    => 'nullable|string|max:100',
        'form.fiz_city'       => 'nullable|string|max:100',
        'form.fiz_address'    => 'nullable|string|max:255',
        'form.fiz_post_code'  => 'nullable|string|max:20',
        'form.bank_name'      => 'nullable|string|max:255',
        'form.swift'          => 'nullable|string|max:50',
        'form.email'          => 'nullable|email|max:255',
        'form.phone'          => 'nullable|string|max:50',
        'form.representative' => 'nullable|string|max:255',
    ];

    public function save()
    {
        $this->validate();

        // Обновляем модель
        $this->client->update($this->form);

        session()->flash('success', 'Client updated successfully!');
        return redirect()->route('clients.show', $this->client->id);
    }

    public function render()
    {
        return view('livewire.clients.edit-client')
            ->layout('layouts.app')
            ->title('Edit Client');
    }
}

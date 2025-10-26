<?php

namespace App\Livewire\Clients;

use Livewire\Component;
use App\Models\Client;

class EditClient extends Component
{
    public Client $client;

    // Основные данные
    public $company_name;
    public $reg_nr;
    public $representative;
    public $email;
    public $phone;
    public $bank_name;
    public $swift;

    // Адреса
    public ?int $jur_country_id = null;
    public ?int $jur_city_id = null;
    public $jur_address;
    public $jur_post_code;

    public ?int $fiz_country_id = null;
    public ?int $fiz_city_id = null;
    public $fiz_address;
    public $fiz_post_code;

    public function mount(Client $client)
    {
        $this->client = $client;

        // Загружаем данные модели в публичные свойства
        $this->fill([
            'company_name' => $client->company_name,
            'reg_nr' => $client->reg_nr,
            'representative' => $client->representative,
            'email' => $client->email,
            'phone' => $client->phone,
            'bank_name' => $client->bank_name,
            'swift' => $client->swift,

            'jur_country_id' => $client->jur_country_id,
            'jur_city_id' => $client->jur_city_id,
            'jur_address' => $client->jur_address,
            'jur_post_code' => $client->jur_post_code,

            'fiz_country_id' => $client->fiz_country_id,
            'fiz_city_id' => $client->fiz_city_id,
            'fiz_address' => $client->fiz_address,
            'fiz_post_code' => $client->fiz_post_code,
        ]);
    }

    protected $rules = [
        'company_name' => 'required|string|max:255',
        'reg_nr' => 'nullable|string|max:50',
        'representative' => 'nullable|string|max:255',
        'email' => 'nullable|email|max:255',
        'phone' => 'nullable|string|max:50',
        'bank_name' => 'nullable|string|max:255',
        'swift' => 'nullable|string|max:50',

        'jur_country_id' => 'required|integer',
        'jur_city_id' => 'required|integer',
        'jur_address' => 'nullable|string|max:255',
        'jur_post_code' => 'nullable|string|max:20',

        'fiz_country_id' => 'nullable|integer',
        'fiz_city_id' => 'nullable|integer',
        'fiz_address' => 'nullable|string|max:255',
        'fiz_post_code' => 'nullable|string|max:20',
    ];

    public function updatedJurCountryId($value)
    {
        $this->jur_city_id = null;
    }

    public function updatedFizCountryId($value)
    {
        $this->fiz_city_id = null;
    }

    public function save()
    {
        $validated = $this->validate();

        $this->client->update($validated);

        session()->flash('success', 'Client updated successfully!');
        return redirect()->route('clients.index');
    }

    public function render()
    {
        $countries = config('countries');
        $jurCities = $this->jur_country_id ? getCitiesByCountryId($this->jur_country_id) : [];
        $fizCities = $this->fiz_country_id ? getCitiesByCountryId($this->fiz_country_id) : [];

        return view('livewire.clients.edit-client', [
            'countries' => $countries,
            'jurCities' => $jurCities,
            'fizCities' => $fizCities,
        ])->layout('layouts.app')->title('Edit Client');
    }
}

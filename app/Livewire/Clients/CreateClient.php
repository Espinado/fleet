<?php

namespace App\Livewire\Clients;

use Livewire\Component;
use App\Models\Client;

class CreateClient extends Component
{
    // 🏢 Основные данные
    public $company_name;
    public $reg_nr;
    public $representative;
    public $email;
    public $phone;
    public $bank_name;
    public $swift;

    // 📍 Юридический адрес
    public ?int $jur_country_id = null;
    public ?int $jur_city_id = null;
    public $jur_address;
    public $jur_post_code;

    // 🏠 Фактический адрес
    public ?int $fiz_country_id = null;
    public ?int $fiz_city_id = null;
    public $fiz_address;
    public $fiz_post_code;

    protected $rules = [
        // Основное
        'company_name' => 'required|string|max:255',
        'reg_nr' => 'nullable|string|max:50',
        'representative' => 'nullable|string|max:255',
        'email' => 'nullable|email|max:255',
        'phone' => 'nullable|string|max:50',
        'bank_name' => 'nullable|string|max:255',
        'swift' => 'nullable|string|max:50',

        // Юридический адрес
        'jur_country_id' => 'required|integer',
        'jur_city_id' => 'required|integer',
        'jur_address' => 'nullable|string|max:255',
        'jur_post_code' => 'nullable|string|max:20',

        // Фактический адрес
        'fiz_country_id' => 'nullable|integer',
        'fiz_city_id' => 'nullable|integer',
        'fiz_address' => 'nullable|string|max:255',
        'fiz_post_code' => 'nullable|string|max:20',
    ];

    // 🔄 Сброс города при смене страны
    public function updatedJurCountryId($value)
    {
        $this->jur_city_id = null;
    }

    public function updatedFizCountryId($value)
    {
        $this->fiz_city_id = null;
    }

    // 💾 Сохранение
    public function save()
    {
        $validated = $this->validate();

        Client::create($validated);

        session()->flash('success', 'Client created successfully!');
        return redirect()->route('clients.index');
    }

    // 🎨 Рендер
    public function render()
    {
        $countries = config('countries');

        $jurCities = $this->jur_country_id
            ? getCitiesByCountryId($this->jur_country_id)
            : [];

        $fizCities = $this->fiz_country_id
            ? getCitiesByCountryId($this->fiz_country_id)
            : [];

        return view('livewire.clients.create-client', [
            'countries' => $countries,
            'jurCities' => $jurCities,
            'fizCities' => $fizCities,
        ])->layout('layouts.app', [
        'title' => 'New client'
    ]);
    }
}

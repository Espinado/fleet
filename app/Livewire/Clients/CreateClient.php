<?php

namespace App\Livewire\Clients;

use Livewire\Component;
use App\Models\Client;

class CreateClient extends Component
{
    public $company_name;
    public $reg_nr;
    public $jur_country;
    public $jur_city;
    public $jur_address;
    public $jur_post_code;
    public $fiz_country;
    public $fiz_city;
    public $fiz_address;
    public $fiz_post_code;
    public $bank_name;
    public $swift;
    public $email;
    public $phone;
    public $representative;

    protected $rules = [
        'company_name'   => 'required|string|max:255',
        'reg_nr'         => 'nullable|string|max:50',
        'jur_country'    => 'nullable|string|max:100',
        'jur_city'       => 'nullable|string|max:100',
        'jur_address'    => 'nullable|string|max:255',
        'jur_post_code'  => 'nullable|string|max:20',
        'fiz_country'    => 'nullable|string|max:100',
        'fiz_city'       => 'nullable|string|max:100',
        'fiz_address'    => 'nullable|string|max:255',
        'fiz_post_code'  => 'nullable|string|max:20',
        'bank_name'      => 'nullable|string|max:255',
        'swift'          => 'nullable|string|max:50',
        'email'          => 'nullable|email|max:255',
        'phone'          => 'nullable|string|max:50',
        'representative' => 'nullable|string|max:255',
    ];

    public function save()
    {
        $validated = $this->validate();

        Client::create($validated);

        session()->flash('success', 'Client created successfully!');
        return redirect()->route('clients.index');
    }

    public function render()
    {
        return view('livewire.clients.create-client')
            ->layout('layouts.app')
            ->title('Add Client');
    }
}

<?php

namespace App\Livewire\Carriers;

use App\Models\Company;
use Livewire\Component;

class EditCarrier extends Component
{
    public Company $carrier;

    public string $name = '';
    public ?string $reg_nr = null;
    public ?string $country = null;
    public ?string $city = null;
    public ?string $address = null;
    public ?string $post_code = null;
    public ?string $email = null;
    public ?string $phone = null;
    public ?string $contact_person = null;
    public ?int $rating = null;
    public bool $is_active = true;

    public function mount(Company $carrier): void
    {
        $this->carrier = $carrier;
        if ($this->carrier->type !== 'carrier' || !$this->carrier->is_third_party) {
            abort(404);
        }
        $this->name = $this->carrier->name ?? '';
        $this->reg_nr = $this->carrier->reg_nr;
        $this->country = $this->carrier->country;
        $this->city = $this->carrier->city;
        $this->address = $this->carrier->address;
        $this->post_code = $this->carrier->post_code;
        $this->email = $this->carrier->email;
        $this->phone = $this->carrier->phone;
        $this->contact_person = $this->carrier->contact_person;
        $this->rating = $this->carrier->rating;
        $this->is_active = (bool) $this->carrier->is_active;
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'reg_nr' => 'nullable|string|max:191',
            'country' => 'nullable|string|max:191',
            'city' => 'nullable|string|max:191',
            'address' => 'nullable|string',
            'post_code' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:191',
            'phone' => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'rating' => 'nullable|integer|min:1|max:5',
            'is_active' => 'boolean',
        ];
    }

    public function save(): \Illuminate\Http\RedirectResponse
    {
        $this->validate();

        $this->carrier->update([
            'name' => trim($this->name),
            'reg_nr' => $this->reg_nr ? trim($this->reg_nr) : null,
            'country' => $this->country ? trim($this->country) : null,
            'city' => $this->city ? trim($this->city) : null,
            'address' => $this->address ? trim($this->address) : null,
            'post_code' => $this->post_code ? trim($this->post_code) : null,
            'email' => $this->email ? trim($this->email) : null,
            'phone' => $this->phone ? trim($this->phone) : null,
            'contact_person' => $this->contact_person ? trim($this->contact_person) : null,
            'rating' => $this->rating,
            'is_active' => $this->is_active,
        ]);

        session()->flash('success', __('app.carriers.updated'));

        return $this->redirect(route('carriers.show', $this->carrier), navigate: true);
    }

    public function render()
    {
        return view('livewire.carriers.edit-carrier')->layout('layouts.app', [
            'title' => __('app.carriers.edit_title') . ' — ' . $this->carrier->name,
        ]);
    }
}

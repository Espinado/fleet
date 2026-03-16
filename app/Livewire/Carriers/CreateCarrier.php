<?php

namespace App\Livewire\Carriers;

use App\Models\Company;
use Illuminate\Support\Str;
use Livewire\Component;

class CreateCarrier extends Component
{
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
        ];
    }

    public function save(): \Illuminate\Http\RedirectResponse
    {
        $this->validate();

        $slug = Str::slug($this->name) ?: 'carrier';
        $base = $slug;
        $i = 2;
        while (Company::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }

        Company::create([
            'slug' => $slug,
            'name' => trim($this->name),
            'type' => 'carrier',
            'is_third_party' => true,
            'reg_nr' => $this->reg_nr ? trim($this->reg_nr) : null,
            'country' => $this->country ? trim($this->country) : null,
            'city' => $this->city ? trim($this->city) : null,
            'address' => $this->address ? trim($this->address) : null,
            'post_code' => $this->post_code ? trim($this->post_code) : null,
            'email' => $this->email ? trim($this->email) : null,
            'phone' => $this->phone ? trim($this->phone) : null,
            'contact_person' => $this->contact_person ? trim($this->contact_person) : null,
            'rating' => $this->rating,
            'is_system' => false,
            'is_active' => true,
        ]);

        session()->flash('success', __('app.carriers.created'));

        return $this->redirect(route('carriers.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.carriers.create-carrier')->layout('layouts.app', [
            'title' => __('app.carriers.create_title'),
        ]);
    }
}

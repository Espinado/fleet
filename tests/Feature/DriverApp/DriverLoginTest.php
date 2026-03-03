<?php

namespace Tests\Feature\DriverApp;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverLoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function driver_login_page_renders(): void
    {
        $response = $this->get(route('driver.login'));

        $response->assertOk();
        $response->assertSeeLivewire(\App\Livewire\DriverApp\Login::class);
    }

    /** @test */
    public function driver_can_login_with_valid_pin(): void
    {
        $user = User::factory()->create();
        $user->update(['role' => 'driver']);
        Driver::factory()->create([
            'user_id' => $user->id,
            'login_pin' => '1234',
        ]);

        $response = $this->livewire(\App\Livewire\DriverApp\Login::class)
            ->set('pin', '1234')
            ->call('login');

        $response->assertRedirect(route('driver.dashboard'));
        $this->assertAuthenticated('driver');
    }

    /** @test */
    public function driver_cannot_login_with_invalid_pin(): void
    {
        $user = User::factory()->create();
        $user->update(['role' => 'driver']);
        Driver::factory()->create([
            'user_id' => $user->id,
            'login_pin' => '1234',
        ]);

        $response = $this->livewire(\App\Livewire\DriverApp\Login::class)
            ->set('pin', '9999')
            ->call('login');

        $response->assertHasErrors('pin');
        $this->assertGuest('driver');
    }

    /** @test */
    public function driver_cannot_login_with_empty_pin(): void
    {
        $response = $this->livewire(\App\Livewire\DriverApp\Login::class)
            ->set('pin', '')
            ->call('login');

        $response->assertHasErrors('pin');
    }

    /** @test */
    public function driver_without_user_cannot_login(): void
    {
        $driver = Driver::factory()->create([
            'user_id' => null,
            'login_pin' => '1234',
        ]);

        $response = $this->livewire(\App\Livewire\DriverApp\Login::class)
            ->set('pin', '1234')
            ->call('login');

        $response->assertHasErrors('pin');
        $this->assertGuest('driver');
    }
}

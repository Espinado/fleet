<?php

namespace Tests\Feature\DriverApp;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverDashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guest_cannot_access_driver_dashboard(): void
    {
        $response = $this->get(route('driver.dashboard'));

        $response->assertRedirect(route('driver.login'));
    }

    /** @test */
    public function authenticated_driver_can_access_dashboard(): void
    {
        $user = User::factory()->create();
        $user->update(['role' => 'driver']);
        $driver = Driver::factory()->create(['user_id' => $user->id, 'login_pin' => '1234']);

        $response = $this->actingAs($user, 'driver')
            ->get(route('driver.dashboard'));

        $response->assertOk();
        $response->assertSeeLivewire(\App\Livewire\DriverApp\Dashboard::class);
    }
}

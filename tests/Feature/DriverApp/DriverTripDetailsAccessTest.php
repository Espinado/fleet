<?php

namespace Tests\Feature\DriverApp;

use App\Models\Driver;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverTripDetailsAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guest_cannot_access_driver_trip_details(): void
    {
        $trip = Trip::factory()->create();

        $response = $this->get(route('driver.trip', ['trip' => $trip->id]));

        $response->assertRedirect(route('driver.login'));
    }

    /** @test */
    public function driver_can_access_own_trip_details(): void
    {
        $user = User::factory()->create();
        $user->update(['role' => 'driver']);
        $driver = Driver::factory()->create(['user_id' => $user->id]);
        $trip = Trip::factory()->create(['driver_id' => $driver->id]);

        $response = $this->actingAs($user, 'driver')
            ->get(route('driver.trip', ['trip' => $trip->id]));

        $response->assertOk();
        $response->assertSeeLivewire(\App\Livewire\DriverApp\TripDetails::class);
    }
}

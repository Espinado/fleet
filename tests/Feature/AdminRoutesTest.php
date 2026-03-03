<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected function adminUser(): User
    {
        $user = User::factory()->create();
        $user->update(['role' => 'admin']);
        return $user;
    }

    /** @test */
    public function guest_redirected_from_dashboard(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect('/login');
    }

    /** @test */
    public function authenticated_admin_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->adminUser())
            ->get(route('dashboard'));

        $response->assertOk();
    }

    /** @test */
    public function authenticated_admin_can_access_drivers_index(): void
    {
        $response = $this->actingAs($this->adminUser())
            ->get(route('drivers.index'));

        $response->assertOk();
    }

    /** @test */
    public function authenticated_admin_can_access_trucks_index(): void
    {
        $response = $this->actingAs($this->adminUser())
            ->get(route('trucks.index'));

        $response->assertOk();
    }

    /** @test */
    public function authenticated_admin_can_access_trailers_index(): void
    {
        $response = $this->actingAs($this->adminUser())
            ->get(route('trailers.index'));

        $response->assertOk();
    }

    /** @test */
    public function authenticated_admin_can_access_clients_index(): void
    {
        $response = $this->actingAs($this->adminUser())
            ->get(route('clients.index'));

        $response->assertOk();
    }

    /** @test */
    public function authenticated_admin_can_access_trips_index(): void
    {
        $response = $this->actingAs($this->adminUser())
            ->get(route('trips.index'));

        $response->assertOk();
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Trip;
use App\Models\TripCargo;
use App\Models\TripStep;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateTripTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function trip_with_cargos_and_steps_is_created_correctly()
    {
        $step1 = TripStep::factory()->create(['order' => 1, 'type' => 'loading']);
        $step2 = TripStep::factory()->create(['order' => 2, 'type' => 'unloading']);

        $response = $this->post('/trips/create', [
            'expeditor_id' => 1,
            'driver_id' => 1,
            'truck_id' => 1,
            'currency' => 'EUR',
            'cargos' => [
                [
                    'price' => 100,
                    'tax_percent' => 21,
                    'loading_step_id' => $step1->id,
                    'unloading_step_id' => $step2->id,
                ]
            ]
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseCount('trips', 1);
        $this->assertDatabaseCount('trip_cargos', 1);
    }
}

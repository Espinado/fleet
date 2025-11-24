<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TripStep;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateTripFailsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function cannot_create_trip_if_unloading_is_before_loading()
    {
        $loading = TripStep::factory()->create(['order' => 2, 'type' => 'loading']);
        $unloading = TripStep::factory()->create(['order' => 1, 'type' => 'unloading']);

        $response = $this->post('/trips/create', [
            'expeditor_id' => 1,
            'driver_id'    => 1,
            'truck_id'     => 1,
            'currency'     => 'EUR',
            'cargos' => [
                [
                    'price' => 100,
                    'tax_percent' => 21,
                    'loading_step_id' => $loading->id,
                    'unloading_step_id' => $unloading->id,
                ]
            ]
        ]);

        $response->assertSessionHasErrors();
    }
}

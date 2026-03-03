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
    public function trip_can_be_created_with_steps_and_cargos()
    {
        $trip = Trip::factory()->create();
        $step1 = TripStep::factory()->create(['trip_id' => $trip->id, 'order' => 1, 'type' => 'loading']);
        $step2 = TripStep::factory()->create(['trip_id' => $trip->id, 'order' => 2, 'type' => 'unloading']);

        $cargo1 = TripCargo::factory()->create(['trip_id' => $trip->id]);
        $step1->cargos()->attach($cargo1->id, ['role' => 'loading']);
        $step2->cargos()->attach($cargo1->id, ['role' => 'unloading']);

        $this->assertDatabaseCount('trips', 1);
        $this->assertDatabaseCount('trip_steps', 2);
        $this->assertDatabaseCount('trip_cargos', 1);
        $this->assertCount(2, $trip->fresh()->steps);
        $this->assertCount(1, $trip->fresh()->cargos);
    }
}

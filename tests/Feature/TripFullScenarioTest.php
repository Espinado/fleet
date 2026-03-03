<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Trip;
use App\Models\TripStep;
use App\Models\TripCargo;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TripFullScenarioTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function full_trip_scenario_works()
    {
        $trip = \App\Models\Trip::factory()->create();
        $s1 = TripStep::factory()->create(['trip_id' => $trip->id, 'order' => 1, 'type' => 'loading']);
        $s2 = TripStep::factory()->create(['trip_id' => $trip->id, 'order' => 2, 'type' => 'loading']);
        $s3 = TripStep::factory()->create(['trip_id' => $trip->id, 'order' => 3, 'type' => 'unloading']);

        $cargo1 = TripCargo::factory()->create(['trip_id' => $trip->id]);
        $cargo2 = TripCargo::factory()->create(['trip_id' => $trip->id]);

        $s1->cargos()->attach($cargo1->id, ['role' => 'loading']);
        $s2->cargos()->attach($cargo2->id, ['role' => 'loading']);
        $s3->cargos()->attach($cargo1->id, ['role' => 'unloading']);
        $s3->cargos()->attach($cargo2->id, ['role' => 'unloading']);

        $this->assertCount(1, $s1->cargosLoadedHere->get());
        $this->assertCount(1, $s2->cargosLoadedHere->get());
        $this->assertCount(2, $s3->cargosUnloadedHere->get());
    }
}

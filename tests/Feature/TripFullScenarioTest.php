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
        $s1 = TripStep::factory()->create(['order' => 1, 'type' => 'loading']);
        $s2 = TripStep::factory()->create(['order' => 2, 'type' => 'loading']);
        $s3 = TripStep::factory()->create(['order' => 3, 'type' => 'unloading']);

        $cargo1 = TripCargo::factory()->create([
            'loading_step_id' => $s1->id,
            'unloading_step_id' => $s3->id,
        ]);

        $cargo2 = TripCargo::factory()->create([
            'loading_step_id' => $s2->id,
            'unloading_step_id' => $s3->id,
        ]);

        $this->assertCount(1, $s1->cargosLoadedHere);
        $this->assertCount(1, $s2->cargosLoadedHere);
        $this->assertCount(2, $s3->cargosUnloadedHere);
    }
}

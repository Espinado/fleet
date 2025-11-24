<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Trip;
use App\Models\TripCargo;
use App\Models\TripStep;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TripStepTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function step_relations_work()
    {
        $step = TripStep::factory()->create(['type' => 'loading']);

        $cargo = TripCargo::factory()->create([
            'loading_step_id' => $step->id
        ]);

        $this->assertCount(1, $step->cargosLoadedHere);
        $this->assertEquals($cargo->id, $step->cargosLoadedHere->first()->id);
    }

    /** @test */
    public function order_field_is_respected()
    {
        $step1 = TripStep::factory()->create(['order' => 1]);
        $step2 = TripStep::factory()->create(['order' => 2]);

        $this->assertEquals(1, $step1->order);
        $this->assertEquals(2, $step2->order);
    }
}

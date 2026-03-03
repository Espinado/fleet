<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\TripStep;
use App\Models\TripCargo;
use App\Models\Trip;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TripCargoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function cargo_belongs_to_trip()
    {
        $trip = Trip::factory()->create();
        $cargo = TripCargo::factory()->create(['trip_id' => $trip->id]);

        $this->assertSame((int) $trip->id, (int) $cargo->trip_id);
        $this->assertTrue($cargo->trip->is($trip));
    }

    /** @test */
    public function cargo_can_have_no_steps_attached()
    {
        $cargo = TripCargo::factory()->create();

        $this->assertCount(0, $cargo->steps);
    }

    /** @test */
    public function cargo_can_be_attached_to_steps_via_pivot()
    {
        $trip = Trip::factory()->create();
        $step1 = TripStep::factory()->create(['trip_id' => $trip->id, 'type' => 'loading']);
        $step2 = TripStep::factory()->create(['trip_id' => $trip->id, 'type' => 'unloading']);
        $cargo = TripCargo::factory()->create(['trip_id' => $trip->id]);

        $cargo->steps()->attach($step1->id, ['role' => 'loading']);
        $cargo->steps()->attach($step2->id, ['role' => 'unloading']);

        $this->assertCount(2, $cargo->steps);
        $this->assertCount(1, $cargo->steps()->wherePivot('role', 'loading')->get());
        $this->assertCount(1, $cargo->steps()->wherePivot('role', 'unloading')->get());
    }
}

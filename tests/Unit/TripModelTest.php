<?php

namespace Tests\Unit;

use App\Models\Driver;
use App\Models\Trip;
use App\Models\TripStep;
use App\Models\User;
use App\Enums\TripStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function trip_has_driver_truck_trailer_relations(): void
    {
        $trip = Trip::factory()->create();

        $this->assertInstanceOf(Driver::class, $trip->driver);
        $this->assertNotNull($trip->truck_id);
        $this->assertNotNull($trip->trailer_id);
    }

    /** @test */
    public function trip_has_steps_ordered_by_order(): void
    {
        $trip = Trip::factory()->create();
        TripStep::factory()->create(['trip_id' => $trip->id, 'order' => 2]);
        TripStep::factory()->create(['trip_id' => $trip->id, 'order' => 1]);

        $steps = $trip->steps;

        $this->assertCount(2, $steps);
        $this->assertEquals(1, $steps[0]->order);
        $this->assertEquals(2, $steps[1]->order);
    }

    /** @test */
    public function trip_status_label_attribute_returns_string(): void
    {
        $trip = Trip::factory()->create(['status' => TripStatus::PLANNED]);

        $this->assertNotEmpty($trip->status_label);
        $this->assertIsString($trip->status_label);
    }
}

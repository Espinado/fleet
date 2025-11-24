<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\TripStep;
use App\Models\TripCargo;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TripCargoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function cargo_cannot_have_unloading_before_loading()
    {
        $l = TripStep::factory()->create(['order' => 2, 'type' => 'loading']);
        $u = TripStep::factory()->create(['order' => 1, 'type' => 'unloading']);

        $this->expectException(\Exception::class);

        TripCargo::create([
            'loading_step_id' => $l->id,
            'unloading_step_id' => $u->id
        ]);
    }

    /** @test */
    public function cargo_can_have_no_steps()
    {
        $cargo = TripCargo::create([]);

        $this->assertNull($cargo->loading_step_id);
        $this->assertNull($cargo->unloading_step_id);
    }

    /** @test */
    public function cargo_tax_calculation_is_correct()
    {
        $cargo = TripCargo::factory()->make([
            'price' => 100,
            'tax_percent' => 21,
        ]);

        $cargo->calculateTotals();

        $this->assertEquals(21.00, $cargo->total_tax_amount);
        $this->assertEquals(121.00, $cargo->price_with_tax);
    }
}

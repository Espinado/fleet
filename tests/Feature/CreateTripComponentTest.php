<?php

namespace Tests\Feature;

use Tests\TestCase;
use Livewire\Livewire;
use App\Livewire\Trips\CreateTrip;
use App\Models\TripStep;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateTripComponentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function livewire_recalculates_tax_correctly()
    {
        Livewire::test(CreateTrip::class)
            ->set('cargos.0.price', 100)
            ->set('cargos.0.tax_percent', 21)
            ->assertSet('cargos.0.total_tax_amount', 21.00)
            ->assertSet('cargos.0.price_with_tax', 121.00);
    }

    /** @test */
    public function livewire_detects_wrong_step_order()
    {
        $l = TripStep::factory()->create(['order' => 2, 'type' => 'loading']);
        $u = TripStep::factory()->create(['order' => 1, 'type' => 'unloading']);

        Livewire::test(CreateTrip::class)
            ->set('cargos.0.loading_step_id', $l->id)
            ->set('cargos.0.unloading_step_id', $u->id)
            ->assertHasErrors();
    }
}

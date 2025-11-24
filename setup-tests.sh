#!/bin/bash

echo "=== Создание тестов Laravel Fleet Manager ==="

# -------------------------
# Unit Tests
# -------------------------

echo "Создаём Unit тесты..."

php artisan make:test TripCargoTest --unit
php artisan make:test TripStepTest --unit

# TripCargoTest
cat << 'EOF' > tests/Unit/TripCargoTest.php
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
EOF

# TripStepTest
cat << 'EOF' > tests/Unit/TripStepTest.php
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
EOF

# -------------------------
# Feature Tests
# -------------------------

echo "Создаём Feature тесты..."

php artisan make:test CreateTripTest
php artisan make:test CreateTripFailsTest
php artisan make:test TripFullScenarioTest

# CreateTripTest
cat << 'EOF' > tests/Feature/CreateTripTest.php
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
EOF

# CreateTripFailsTest
cat << 'EOF' > tests/Feature/CreateTripFailsTest.php
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
EOF

# TripFullScenarioTest
cat << 'EOF' > tests/Feature/TripFullScenarioTest.php
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
EOF

# -------------------------
# Livewire Test
# -------------------------

echo "Создаём Livewire тест..."

php artisan make:test CreateTripComponentTest

cat << 'EOF' > tests/Feature/CreateTripComponentTest.php
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
EOF

echo "=== Тесты успешно созданы и заполнены ==="

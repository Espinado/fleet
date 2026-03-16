<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Client;
use App\Models\Company;
use App\Models\Trip;
use App\Models\TripCargo;
use App\Models\TripStep;
use App\Models\TransportOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\Trips\ViewTrip;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Углублённое тестирование сценариев рейсов и заказов:
 * - просмотр рейса с маршрутом и грузами;
 * - добавление заказов к рейсу и обновление маршрута/грузов;
 * - удаление заказа из рейса.
 */
class TripAndOrdersScenarioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedMinimalCompaniesAndClients();
    }

    /** Создаём минимум компанию и клиента для рейсов и заказов. */
    protected function seedMinimalCompaniesAndClients(): void
    {
        if (Company::query()->exists()) {
            return;
        }
        Company::create([
            'slug' => 'test-expeditor',
            'name' => 'Test Expeditor',
            'type' => 'expeditor',
            'is_active' => true,
        ]);
        Company::create([
            'slug' => 'test-carrier',
            'name' => 'Test Carrier',
            'type' => 'carrier',
            'is_active' => true,
        ]);
        if (!Client::query()->exists()) {
            Client::factory()->create(['company_name' => 'Test Client']);
        }
    }

    protected function adminUser(): User
    {
        $user = User::factory()->create();
        $user->update(['role' => 'admin']);
        return $user;
    }

    #[Test]
    public function trip_show_page_loads_with_steps_and_cargos(): void
    {
        $trip = Trip::factory()->create();
        TripStep::factory()->create(['trip_id' => $trip->id, 'order' => 1, 'type' => 'loading']);
        TripStep::factory()->create(['trip_id' => $trip->id, 'order' => 2, 'type' => 'unloading']);
        $cargo = TripCargo::factory()->create(['trip_id' => $trip->id]);
        $trip->steps()->get()->each(function ($step, $i) use ($cargo) {
            $step->cargos()->attach($cargo->id, ['role' => $i === 0 ? 'loading' : 'unloading']);
        });

        $response = $this->actingAs($this->adminUser())
            ->get(route('trips.show', $trip));

        $response->assertOk();
        $response->assertSee('Reisa informācija', false);
    }

    #[Test]
    public function trip_create_page_loads(): void
    {
        $response = $this->actingAs($this->adminUser())
            ->get(route('trips.create'));

        $response->assertOk();
    }

    #[Test]
    public function adding_orders_to_trip_creates_steps_and_cargos(): void
    {
        $expeditorId = Company::query()->where('type', 'expeditor')->value('id');
        $clientId = Client::query()->value('id');
        $this->assertNotNull($expeditorId, 'Need at least one company (expeditor)');
        $this->assertNotNull($clientId, 'Need at least one client');

        $trip = Trip::factory()->create(['expeditor_id' => $expeditorId]);
        TripStep::factory()->create(['trip_id' => $trip->id, 'order' => 1, 'type' => 'loading']);
        TripStep::factory()->create(['trip_id' => $trip->id, 'order' => 2, 'type' => 'unloading']);

        $order = TransportOrder::create([
            'number'        => 'TO-TEST-001',
            'order_date'    => now(),
            'expeditor_id'  => $expeditorId,
            'customer_id'   => $clientId,
            'status'        => OrderStatus::CONFIRMED->value,
            'trip_id'       => null,
        ]);
        $order->steps()->create([
            'type'    => 'loading',
            'order'   => 1,
            'address' => 'Riga, test 1',
            'date'    => now()->addDay(),
        ]);
        $order->steps()->create([
            'type'    => 'unloading',
            'order'   => 2,
            'address' => 'Tallinn, test 2',
            'date'    => now()->addDays(2),
        ]);
        $order->cargos()->create([
            'customer_id'   => $clientId,
            'shipper_id'    => $clientId,
            'consignee_id'  => $clientId,
            'description'   => 'Test cargo',
        ]);

        $initialSteps = $trip->steps()->count();
        $initialCargos = $trip->cargos()->count();

        Livewire::actingAs($this->adminUser())
            ->test(ViewTrip::class, ['trip' => $trip])
            ->set('add_orders_selection', [$order->id])
            ->call('addOrdersToTrip');

        $trip->refresh();
        $trip->load(['steps', 'cargos', 'transportOrders']);

        $this->assertGreaterThan($initialSteps, $trip->steps()->count(), 'Trip should have more steps after adding order');
        $this->assertGreaterThan($initialCargos, $trip->cargos()->count(), 'Trip should have more cargos after adding order');
        $this->assertCount(1, $trip->transportOrders);
        $this->assertEquals(OrderStatus::CONVERTED->value, $order->fresh()->status->value);
        $this->assertEquals($trip->id, $order->fresh()->trip_id);
    }

    #[Test]
    public function add_orders_to_trip_with_empty_selection_does_nothing(): void
    {
        $trip = Trip::factory()->create();
        TripStep::factory()->create(['trip_id' => $trip->id, 'order' => 1, 'type' => 'loading']);
        TripStep::factory()->create(['trip_id' => $trip->id, 'order' => 2, 'type' => 'unloading']);

        $stepsBefore = $trip->steps()->count();

        Livewire::actingAs($this->adminUser())
            ->test(ViewTrip::class, ['trip' => $trip])
            ->set('add_orders_selection', [])
            ->call('addOrdersToTrip');

        $trip->refresh();
        $this->assertSame($stepsBefore, $trip->steps()->count());
    }

    #[Test]
    public function remove_order_from_trip_detaches_order_and_removes_its_steps_and_cargos(): void
    {
        $expeditorId = Company::query()->where('type', 'expeditor')->value('id');
        $clientId = Client::query()->value('id');

        $trip = Trip::factory()->create(['expeditor_id' => $expeditorId]);
        TripStep::factory()->create(['trip_id' => $trip->id, 'order' => 1, 'type' => 'loading']);
        TripStep::factory()->create(['trip_id' => $trip->id, 'order' => 2, 'type' => 'unloading']);

        $order = TransportOrder::create([
            'number'        => 'TO-TEST-002',
            'order_date'    => now(),
            'expeditor_id'  => $expeditorId,
            'customer_id'   => $clientId,
            'status'        => OrderStatus::CONVERTED->value,
            'trip_id'       => $trip->id,
        ]);
        $order->steps()->create(['type' => 'loading', 'order' => 1, 'address' => 'A', 'date' => now()]);
        $order->steps()->create(['type' => 'unloading', 'order' => 2, 'address' => 'B', 'date' => now()->addDay()]);
        $order->cargos()->create([
            'customer_id' => $clientId,
            'shipper_id'  => $clientId,
            'consignee_id' => $clientId,
            'description' => 'Cargo to remove',
        ]);

        // Simulate trip has one cargo linked to this order (as after addOrdersToTrip)
        $linkedCargo = TripCargo::factory()->create([
            'trip_id' => $trip->id,
            'transport_order_id' => $order->id,
            'customer_id' => $clientId,
            'shipper_id' => $clientId,
            'consignee_id' => $clientId,
        ]);
        $step1 = $trip->steps()->first();
        $step2 = $trip->steps()->skip(1)->first();
        if ($step1) {
            $linkedCargo->steps()->attach($step1->id, ['role' => 'loading']);
        }
        if ($step2) {
            $linkedCargo->steps()->attach($step2->id, ['role' => 'unloading']);
        }

        Livewire::actingAs($this->adminUser())
            ->test(ViewTrip::class, ['trip' => $trip])
            ->call('removeOrderFromTrip', $order->id);

        $order->refresh();
        $this->assertNull($order->trip_id);
        $this->assertEquals(OrderStatus::CONFIRMED->value, $order->status->value);

        $trip->refresh();
        $trip->load(['transportOrders', 'cargos', 'steps']);
        $this->assertCount(0, $trip->transportOrders);
        $this->assertDatabaseMissing('trip_cargos', [
            'trip_id' => $trip->id,
            'transport_order_id' => $order->id,
        ]);
    }
}

<?php

namespace App\Livewire\Maintenance;

use App\Models\Trailer;
use App\Models\Trip;
use App\Models\Truck;
use App\Models\TruckOdometerEvent;
use App\Services\ExpiringDocumentsService;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;

class MaintenanceIndex extends Component
{
    public string $expiringSearch = '';
    public string $expiringSort = 'expires_at';
    public string $expiringDir = 'asc';
    public int $expiringPerPage = 15;
    public int $expiringPage = 1;

    public string $upcomingSearch = '';
    public string $upcomingSort = 'expires_at';
    public string $upcomingDir = 'asc';
    public int $upcomingPerPage = 15;
    public int $upcomingPage = 1;

    protected $queryString = [
        'expiringSearch' => ['except' => ''],
        'expiringSort' => ['except' => 'expires_at'],
        'expiringDir' => ['except' => 'asc'],
        'expiringPage' => ['except' => 1],
        'upcomingSearch' => ['except' => ''],
        'upcomingSort' => ['except' => 'expires_at'],
        'upcomingDir' => ['except' => 'asc'],
        'upcomingPage' => ['except' => 1],
    ];

    public function updatingExpiringSearch(): void { $this->expiringPage = 1; }
    public function updatingExpiringSort(): void { $this->expiringPage = 1; }
    public function updatingExpiringDir(): void { $this->expiringPage = 1; }
    public function updatingUpcomingSearch(): void { $this->upcomingPage = 1; }
    public function updatingUpcomingSort(): void { $this->upcomingPage = 1; }
    public function updatingUpcomingDir(): void { $this->upcomingPage = 1; }

    public function setExpiringPage(int $page): void
    {
        $this->expiringPage = max(1, $page);
    }

    public function setUpcomingPage(int $page): void
    {
        $this->upcomingPage = max(1, $page);
    }

    /** Все истекающие документы (техосмотр, страховка, лицензия, ТО и т.д.) с поиском и сортировкой. */
    public function getExpiringDocumentsPaginatorProperty(): LengthAwarePaginator
    {
        $list = ExpiringDocumentsService::list(30);

        if ($this->expiringSearch !== '') {
            $term = mb_strtolower(trim($this->expiringSearch));
            $list = $list->filter(function ($item) use ($term) {
                return str_contains(mb_strtolower($item->entity_name), $term)
                    || str_contains(mb_strtolower(__('app.' . $item->doc_label_key)), $term);
            })->values();
        }

        $list = $this->expiringDir === 'desc'
            ? $list->sortByDesc($this->expiringSort)->values()
            : $list->sortBy($this->expiringSort)->values();

        $total = $list->count();
        $chunk = $list->forPage($this->expiringPage, $this->expiringPerPage)->values();

        return new LengthAwarePaginator(
            $chunk,
            $total,
            $this->expiringPerPage,
            $this->expiringPage,
            ['path' => request()->url(), 'pageName' => 'expiringPage']
        );
    }

    /** Предстоящее ТО: техника, у которой next_service_km или next_service_date в пределах порога (30 дней / 2000 км). */
    public function getUpcomingMaintenanceListProperty(): Collection
    {
        $today = Carbon::today();
        $dateLimit = $today->copy()->addDays(30);
        $kmThreshold = 2000;
        $list = collect();

        $truckOdometers = TruckOdometerEvent::query()
            ->select('truck_id')
            ->selectRaw('MAX(odometer_km) as last_km')
            ->whereNotNull('odometer_km')
            ->where('odometer_km', '>', 0)
            ->groupBy('truck_id')
            ->pluck('last_km', 'truck_id');

        $trucks = Truck::query()
            ->whereHas('company', fn ($c) => $c->where(fn ($q) => $q->where('is_third_party', false)->orWhereNull('is_third_party')))
            ->where(function ($q) use ($today, $dateLimit) {
                $q->whereNotNull('next_service_km')
                    ->orWhereNotNull('next_service_date');
            })
            ->get();

        foreach ($trucks as $truck) {
            $currentKm = (int) ($truckOdometers->get($truck->id) ?? Trip::where('truck_id', $truck->id)->max('odo_end_km') ?? 0);
            $dueByKm = $truck->next_service_km !== null && $currentKm > 0 && $currentKm >= (int) $truck->next_service_km - $kmThreshold;
            $dueByDate = $truck->next_service_date !== null && Carbon::parse($truck->next_service_date)->lte($dateLimit);
            if ($dueByKm || $dueByDate) {
                $list->push((object) [
                    'type' => 'truck',
                    'id' => $truck->id,
                    'name' => trim(($truck->brand ?? '') . ' ' . ($truck->model ?? '') . ' ' . ($truck->plate ?? '')),
                    'due_by_km' => $dueByKm,
                    'due_by_date' => $dueByDate,
                    'next_service_km' => $truck->next_service_km,
                    'next_service_date' => $truck->next_service_date,
                    'current_km' => $currentKm,
                    'sort_date' => $truck->next_service_date,
                ]);
            }
        }

        $trailers = Trailer::query()
            ->whereHas('company', fn ($c) => $c->where(fn ($q) => $q->where('is_third_party', false)->orWhereNull('is_third_party')))
            ->whereNotNull('next_service_date')
            ->whereDate('next_service_date', '<=', $dateLimit)
            ->get();

        foreach ($trailers as $trailer) {
            $list->push((object) [
                'type' => 'trailer',
                'id' => $trailer->id,
                'name' => trim(($trailer->brand ?? '') . ' ' . ($trailer->plate ?? '')),
                'due_by_km' => false,
                'due_by_date' => true,
                'next_service_km' => $trailer->next_service_km,
                'next_service_date' => $trailer->next_service_date,
                'current_km' => null,
                'sort_date' => $trailer->next_service_date,
            ]);
        }

        return $list->sortBy(fn ($i) => $i->sort_date ?? '9999-12-31')->values();
    }

    public function getUpcomingMaintenancePaginatorProperty(): LengthAwarePaginator
    {
        $list = $this->upcomingMaintenanceList;

        if ($this->upcomingSearch !== '') {
            $term = mb_strtolower(trim($this->upcomingSearch));
            $list = $list->filter(fn ($item) => str_contains(mb_strtolower($item->name), $term))->values();
        }

        $list = $this->upcomingDir === 'desc'
            ? $list->sortByDesc($this->upcomingSort === 'expires_at' ? 'sort_date' : 'name')->values()
            : $list->sortBy($this->upcomingSort === 'expires_at' ? 'sort_date' : 'name')->values();

        $total = $list->count();
        $chunk = $list->forPage($this->upcomingPage, $this->upcomingPerPage)->values();

        return new LengthAwarePaginator(
            $chunk,
            $total,
            $this->upcomingPerPage,
            $this->upcomingPage,
            ['path' => request()->url(), 'pageName' => 'upcomingPage']
        );
    }

    public function render()
    {
        return view('livewire.maintenance.maintenance-index', [
            'expiringDocuments' => $this->expiringDocumentsPaginator,
            'upcomingMaintenance' => $this->upcomingMaintenancePaginator,
        ])->layout('layouts.app', [
            'title' => __('app.maintenance.page_title'),
        ]);
    }
}

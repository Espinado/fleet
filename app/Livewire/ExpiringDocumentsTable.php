<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Driver;
use App\Models\Truck;
use App\Models\Trailer;
use App\Models\TripCargo;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;

class ExpiringDocumentsTable extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'expiry_date';
    public $sortDirection = 'asc';

    protected $queryString = ['search', 'perPage', 'sortField', 'sortDirection'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function sortBy(string $field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    protected function safeParseDate($value): ?Carbon
    {
        if (empty($value)) return null;

        try {
            if (is_numeric($value) && strlen((string) $value) <= 10) {
                $dt = Carbon::createFromTimestamp((int) $value);
            } else {
                $dt = Carbon::parse($value);
            }
        } catch (\Throwable $e) {
            return null;
        }

        if ($dt->year < 1900 || $dt->year > 2100) return null;

        return $dt->startOfDay();
    }

    protected function companyName(?int $companyId): string
    {
        $companies = config('companies', []);
        return $companies[$companyId]['name'] ?? '—';
    }

    /**
     * Собираем Driver/Truck/Trailer/Invoice (payment_terms) истекающие <= 30 дней
     * Возвращаем Collection объектов одинакового формата
     */
    public function collectItems(): Collection
    {
        $today = Carbon::today();
        $deadline = $today->copy()->addDays(30);

        $items = collect();

        // =======================
        // DRIVERS
        // =======================
        Driver::select(
            'id', 'first_name', 'last_name', 'pers_code', 'status', 'is_active', 'company',
            'license_end', 'code95_end', 'permit_expired', 'medical_expired', 'declaration_expired'
        )->get()->each(function ($d) use ($today, $deadline, $items) {

            $docs = [
                'License'     => $d->license_end,
                '95 Code'     => $d->code95_end ?? ($d->{'95code_end'} ?? null),
                'Permit'      => $d->permit_expired,
                'Medical'     => $d->medical_expired,
                'Declaration' => $d->declaration_expired,
            ];

            $companyId = (int) ($d->company ?? 0);

            foreach ($docs as $docName => $dateVal) {
                $expiry = $this->safeParseDate($dateVal);
                if (!$expiry) continue;
                if ($expiry->gt($deadline)) continue;

                $daysLeft = $today->diffInDays($expiry, false);
                if (abs($daysLeft) > 10000) continue;

                $items->push((object) [
                    'type'        => 'Driver',
                    'name'        => "{$d->first_name} {$d->last_name} ({$d->pers_code})",
                    'document'    => $docName,
                    'expiry_date' => $expiry,
                    'days_left'   => $daysLeft,
                    'company_id'  => $companyId,
                    'company'     => $this->companyName($companyId),
                    'status'      => $d->status,
                    'is_active'   => (bool) $d->is_active,
                    'id'          => $d->id,
                ]);
            }
        });

        // =======================
        // TRUCKS
        // =======================
        Truck::select(
            'id', 'brand', 'model', 'plate', 'status', 'is_active', 'company',
            'inspection_expired', 'insurance_expired', 'tech_passport_expired'
        )->get()->each(function ($t) use ($today, $deadline, $items) {

            $docs = [
                'Inspection'    => $t->inspection_expired,
                'Insurance'     => $t->insurance_expired,
                'Tech passport' => $t->tech_passport_expired,
            ];

            $companyId = (int) ($t->company ?? 0);

            foreach ($docs as $docName => $dateVal) {
                $expiry = $this->safeParseDate($dateVal);
                if (!$expiry) continue;
                if ($expiry->gt($deadline)) continue;

                $daysLeft = $today->diffInDays($expiry, false);
                if (abs($daysLeft) > 10000) continue;

                $items->push((object) [
                    'type'        => 'Truck',
                    'name'        => "{$t->brand} {$t->model} ({$t->plate})",
                    'document'    => $docName,
                    'expiry_date' => $expiry,
                    'days_left'   => $daysLeft,
                    'company_id'  => $companyId,
                    'company'     => $this->companyName($companyId),
                    'status'      => $t->status,
                    'is_active'   => (bool) $t->is_active,
                    'id'          => $t->id,
                ]);
            }
        });

        // =======================
        // TRAILERS
        // =======================
        Trailer::select(
            'id', 'brand', 'plate', 'status', 'is_active', 'company',
            'inspection_expired', 'insurance_expired', 'tir_expired', 'tech_passport_expired'
        )->get()->each(function ($tr) use ($today, $deadline, $items) {

            $docs = [
                'Inspection'    => $tr->inspection_expired,
                'Insurance'     => $tr->insurance_expired,
                'TIR'           => $tr->tir_expired,
                'Tech passport' => $tr->tech_passport_expired,
            ];

            $companyId = (int) ($tr->company ?? 0);

            foreach ($docs as $docName => $dateVal) {
                $expiry = $this->safeParseDate($dateVal);
                if (!$expiry) continue;
                if ($expiry->gt($deadline)) continue;

                $daysLeft = $today->diffInDays($expiry, false);
                if (abs($daysLeft) > 10000) continue;

                $items->push((object) [
                    'type'        => 'Trailer',
                    'name'        => "{$tr->brand} ({$tr->plate})",
                    'document'    => $docName,
                    'expiry_date' => $expiry,
                    'days_left'   => $daysLeft,
                    'company_id'  => $companyId,
                    'company'     => $this->companyName($companyId),
                    'status'      => $tr->status,
                    'is_active'   => (bool) $tr->is_active,
                    'id'          => $tr->id,
                ]);
            }
        });

        // =======================
        // INVOICES (Payment terms)
        // company = trips.expeditor_id ✅
        // Берём только если инвойс реально выписан: inv_nr + inv_file заполнены ✅
        // =======================
        TripCargo::query()
            ->select('id', 'payer_type_id', 'payment_terms', 'trip_id', 'inv_nr', 'inv_file')
            ->whereNotNull('payment_terms')
            ->whereNotNull('inv_nr')
            ->where('inv_nr', '!=', '')
            ->whereNotNull('inv_file')
            ->where('inv_file', '!=', '')
            ->with(['trip:id,expeditor_id'])
            ->get()
            ->each(function ($c) use ($today, $deadline, $items) {

                $expiry = $this->safeParseDate($c->payment_terms);
                if (!$expiry) return;
                if ($expiry->gt($deadline)) return;

                $daysLeft = $today->diffInDays($expiry, false);

                $payerMap = [
                    1 => $c->shipper,
                    2 => $c->consignee,
                    3 => $c->customer,
                ];
                $payer = $payerMap[$c->payer_type_id] ?? null;

                $companyId = (int) ($c->trip?->expeditor_id ?? 0);

                $color =
                    $daysLeft < 0 ? 'rose' :
                    ($daysLeft <= 10 ? 'red' :
                    ($daysLeft <= 20 ? 'orange' :
                    ($daysLeft <= 30 ? 'yellow' : 'white')));

                $items->push((object) [
                   'type'        => 'Invoice',
    // 👇 ТОЛЬКО номер инвойса
    'name'        => !empty($c->inv_nr)
        ? $c->inv_nr
        : ('INV-' . $c->id),

    'inv_nr'      => $c->inv_nr,
    'document'    => 'Payment terms',
    'expiry_date' => $expiry,
    'days_left'   => $daysLeft,
    'company_id'  => $companyId,
    'company'     => $this->companyName($companyId),
    'status'      => $color,
    'is_active'   => true,
    'id'          => $c->id,
                ]);
            });

        return $items->values();
    }

    public function render()
    {
        $all = $this->collectItems();

        // Поиск (case-insensitive)
        if (!empty($this->search)) {
            $needle = mb_strtolower($this->search);

            $all = $all->filter(fn ($it) =>
                str_contains(mb_strtolower($it->type), $needle) ||
                str_contains(mb_strtolower($it->name), $needle) ||
                str_contains(mb_strtolower($it->document), $needle) ||
                str_contains(mb_strtolower($it->company ?? ''), $needle)
            )->values();
        }

        // Сортировка
        $dirDesc = $this->sortDirection === 'desc';
        $all = $all->sortBy(function ($it) {
            $f = $this->sortField;
            if ($f === 'expiry_date') return $it->expiry_date->timestamp;

            return isset($it->{$f})
                ? (is_string($it->{$f}) ? mb_strtolower($it->{$f}) : $it->{$f})
                : null;
        }, SORT_REGULAR, $dirDesc)->values();

        // Пагинация вручную
        $page = Paginator::resolveCurrentPage();
        $perPage = (int) $this->perPage;
        $slice = $all->slice(($page - 1) * $perPage, $perPage)->values();

        if ($slice->isEmpty() && $page > 1) {
            $this->resetPage();
            $page = 1;
            $slice = $all->slice(0, $perPage)->values();
        }

        $paginator = new LengthAwarePaginator(
            $slice,
            $all->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath()]
        );

        return view('livewire.expiring-documents-table', [
            'items' => $paginator,
        ])->layout('layouts.app');
    }
}

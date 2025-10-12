<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Driver;
use App\Models\Truck;
use App\Models\Trailer;
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

    // можно хранить состояние в query string
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
            if (is_numeric($value) && strlen((string)$value) <= 10) {
                // короткий numeric — вероятно timestamp (сек)
                $dt = Carbon::createFromTimestamp((int)$value);
            } else {
                $dt = Carbon::parse($value);
            }
        } catch (\Throwable $e) {
            return null;
        }

        if ($dt->year < 1900 || $dt->year > 2100) return null;

        return $dt->startOfDay();
    }

    // Собираем элементы (Driver, Truck, Trailer) с документами, истекающими <= 30 дней
    public function collectItems(): Collection
    {
        $today = Carbon::today();
        $deadline = $today->copy()->addDays(30);

        $driversDocs = collect();
        Driver::select(
            'id','first_name','last_name','pers_code','status','is_active',
            'license_end','code95_end','permit_expired','medical_expired','declaration_expired'
        )->get()->each(function($d) use (&$driversDocs, $today, $deadline) {
            $docs = [
                'License'     => $d->license_end,
                '95 Code'     => $d->{'95code_end'} ?? null,
                'Permit'      => $d->permit_expired,
                'Medical'     => $d->medical_expired,
                'Declaration' => $d->declaration_expired,
            ];

            foreach ($docs as $docName => $dateVal) {
                $expiry = $this->safeParseDate($dateVal);
                if (!$expiry) continue;
                if ($expiry->gt($deadline)) continue;

                $daysLeft = $today->diffInDays($expiry, false);
                if (abs($daysLeft) > 10000) continue;

                $driversDocs->push((object)[
                    'type' => 'Driver',
                    'name' => "{$d->first_name} {$d->last_name} ({$d->pers_code})",
                    'document' => $docName,
                    'expiry_date' => $expiry,
                    'days_left' => $daysLeft,
                    'status' => $d->status,
                    'is_active' => (bool)$d->is_active,
                    'id' => $d->id,
                ]);
            }
        });

        $trucksDocs = collect();
        Truck::select(
            'id','brand','model','plate','status','is_active',
            'inspection_expired','insurance_expired','tech_passport_expired'
        )->get()->each(function($t) use (&$trucksDocs, $today, $deadline) {
            $docs = [
                'Inspection' => $t->inspection_expired,
                'Insurance'  => $t->insurance_expired,
                'Tech passport' => $t->tech_passport_expired,
            ];

            foreach ($docs as $docName => $dateVal) {
                $expiry = $this->safeParseDate($dateVal);
                if (!$expiry) continue;
                if ($expiry->gt($deadline)) continue;
                $daysLeft = $today->diffInDays($expiry, false);
                if (abs($daysLeft) > 10000) continue;

                $trucksDocs->push((object)[
                    'type' => 'Truck',
                    'name' => "{$t->brand} {$t->model} ({$t->plate})",
                    'document' => $docName,
                    'expiry_date' => $expiry,
                    'days_left' => $daysLeft,
                    'status' => $t->status,
                    'is_active' => (bool)$t->is_active,
                    'id' => $t->id,
                ]);
            }
        });

        $trailersDocs = collect();
        Trailer::select(
            'id','brand','plate','status','is_active',
            'inspection_expired','insurance_expired','tir_expired','tech_passport_expired'
        )->get()->each(function($tr) use (&$trailersDocs, $today, $deadline) {
            $docs = [
                'Inspection' => $tr->inspection_expired,
                'Insurance'  => $tr->insurance_expired,
                'TIR'        => $tr->tir_expired,
                'Tech passport' => $tr->tech_passport_expired,
            ];

            foreach ($docs as $docName => $dateVal) {
                $expiry = $this->safeParseDate($dateVal);
                if (!$expiry) continue;
                if ($expiry->gt($deadline)) continue;
                $daysLeft = $today->diffInDays($expiry, false);
                if (abs($daysLeft) > 10000) continue;

                $trailersDocs->push((object)[
                    'type' => 'Trailer',
                    'name' => "{$tr->brand} ({$tr->plate})",
                    'document' => $docName,
                    'expiry_date' => $expiry,
                    'days_left' => $daysLeft,
                    'status' => $tr->status,
                    'is_active' => (bool)$tr->is_active,
                    'id' => $tr->id,
                ]);
            }
        });

        // concat — безопасно добавляет элементы в одну коллекцию; затем values() очищает ключи
        return $driversDocs->concat($trucksDocs)->concat($trailersDocs)->values();
    }

    public function render()
    {
        $all = $this->collectItems();

        // Поиск (case-insensitive)
        if (!empty($this->search)) {
    $needle = mb_strtolower($this->search);
    $all = $all->filter(fn($it) =>
        str_contains(mb_strtolower($it->type), $needle) ||
        str_contains(mb_strtolower($it->name), $needle) ||
        str_contains(mb_strtolower($it->document), $needle)
    )->values();
}


        // Сортировка
        $dirDesc = $this->sortDirection === 'desc';
        $all = $all->sortBy(function ($it) {
            $f = $this->sortField;
            if ($f === 'expiry_date') return $it->expiry_date->timestamp;
            return isset($it->{$f}) ? (is_string($it->{$f}) ? mb_strtolower($it->{$f}) : $it->{$f}) : null;
        }, SORT_REGULAR, $dirDesc)->values();

        // Пагинация: делаем slice вручную и возвращаем LengthAwarePaginator
        $page = Paginator::resolveCurrentPage(); // берёт ?page=... автоматически
        $perPage = (int) $this->perPage;
        $slice = $all->slice(($page - 1) * $perPage, $perPage)->values();

        // если текущая страница пуста (например, перешли на 5, а перPage увеличили), то возвращаемся на 1
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
        ]);
    }
}

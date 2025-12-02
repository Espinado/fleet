<?php

namespace App\Helpers;

use App\Models\TripStep;

class TripStepSorter
{
    /**
     * Унифицированная сортировка шагов:
     *  1) по дате (null в конец)
     *  2) loading -> unloading
     *  3) по cargo
     */
    public static function sort($steps)
{
    return $steps->sort(function (TripStep $a, TripStep $b) {

        // 1) Sort by date (nulls last)
        $aTime = $a->date ? strtotime($a->date) : PHP_INT_MAX;
        $bTime = $b->date ? strtotime($b->date) : PHP_INT_MAX;

        if ($aTime !== $bTime) {
            return $aTime <=> $bTime;
        }

        // 2) loading before unloading
        if ($a->type !== $b->type) {
            return $a->type === 'loading' ? -1 : 1;
        }

        // 3) Sort by order
        return ($a->order ?? 0) <=> ($b->order ?? 0);
    })->values();
}
}

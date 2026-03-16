<?php

namespace App\Http\Controllers;

use App\Models\TripCargo;

class PublicTripTrackingController extends Controller
{
    /**
     * Публичная страница отслеживания груза по токену (без входа).
     * Клиент видит только свой груз: погрузка → разгрузка.
     * После завершения разгрузки ссылка недействительна.
     */
    public function show(string $token)
    {
        $cargo = TripCargo::findByTrackingToken($token);

        if (!$cargo) {
            abort(404, __('app.track.not_found'));
        }

        $cargo->load([
            'trip',
            'shipper',
            'consignee',
            'customer',
            'steps' => fn ($q) => $q->orderBy('trip_steps.order')->orderBy('trip_steps.id'),
        ]);

        if ($cargo->isTrackingLinkExpired()) {
            return view('track.completed', [
                'cargo' => $cargo,
            ]);
        }

        $loadingStep = $cargo->steps()->wherePivot('role', 'loading')->first();
        $unloadingStep = $cargo->steps()->wherePivot('role', 'unloading')->first();

        return view('track.show', [
            'cargo' => $cargo,
            'loadingStep' => $loadingStep,
            'unloadingStep' => $unloadingStep,
        ]);
    }
}

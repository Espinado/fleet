{{-- PDF: Vadītāja notikumi — выборка за период со сводкой --}}
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Vadītāja notikumi' }}</title>
    <style>
        @page { size: A4 landscape; margin: 12mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #333; margin: 0; padding: 0; }
        .sheet { padding: 0; }
        h1 { font-size: 14px; margin: 0 0 4px 0; }
        .summary { margin-bottom: 10px; padding: 8px; background: #f5f5f5; border: 1px solid #ddd; border-radius: 4px; }
        .summary-grid { display: table; width: 100%; margin-bottom: 6px; }
        .summary-cell { display: table-cell; padding: 4px 12px 4px 0; }
        .summary-label { font-size: 8px; color: #666; text-transform: uppercase; }
        .summary-value { font-size: 12px; font-weight: bold; }
        .summary-cats { margin-top: 6px; font-size: 8px; }
        .summary-cats table { width: auto; border-collapse: collapse; }
        .summary-cats td { padding: 2px 10px 2px 0; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.data th, table.data td { border: 0.5px solid #999; padding: 4px 6px; text-align: left; }
        table.data th { background: #e8e8e8; font-size: 8px; text-transform: uppercase; }
        table.data td { font-size: 8px; }
        table.data .num { text-align: right; }
        .event-badge { font-weight: bold; }
    </style>
</head>
<body>
<div class="sheet">
    <h1>{{ $title }}</h1>
    @if(!empty($summary['period_label']))
        <p style="margin: 0 0 8px 0; color: #666; font-size: 10px;">{{ $summary['period_label'] }}</p>
    @endif

    @if(isset($summary) && (($summary['total_amount'] ?? 0) > 0 || count($summary['by_category'] ?? []) > 0))
        <div class="summary">
            <div class="summary-grid">
                <div class="summary-cell">
                    <div class="summary-label">{{ __('app.stats.events.summary_total') }}</div>
                    <div class="summary-value">EUR {{ number_format((float)($summary['total_amount'] ?? 0), 2, ',', ' ') }}</div>
                </div>
            </div>
            @if(count($summary['by_category'] ?? []) > 0)
                <div class="summary-cats">
                    <div class="summary-label" style="margin-bottom: 2px;">{{ __('app.stats.events.col_event') }} / {{ __('app.stats.events.col_amount') }}</div>
                    <table>
                        @foreach($summary['by_category'] as $item)
                            <tr>
                                <td>{{ $item['label'] }}</td>
                                <td class="num">EUR {{ number_format((float)$item['amount'], 2, ',', ' ') }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            @endif
        </div>
    @endif

    <table class="data">
        <thead>
        <tr>
            <th>{{ __('app.stats.events.driver') }}</th>
            <th>{{ __('app.stats.events.truck') }}</th>
            <th>{{ __('app.stats.events.col_event') }}</th>
            <th>{{ __('app.stats.events.col_timestamp') }}</th>
            <th class="num">{{ __('app.stats.events.col_odo') }}</th>
            <th class="num">{{ __('app.stats.events.col_amount') }}</th>
            <th>{{ __('app.stats.events.col_details') }}</th>
        </tr>
        </thead>
        <tbody>
        @php
            use App\Models\TruckOdometerEvent;
            use App\Enums\TripStepStatus;

            $stepStatusLabel = function ($stepStatus) {
                if ($stepStatus === null || $stepStatus === '') return null;
                try { return TripStepStatus::from((int)$stepStatus)->label(); } catch (\Throwable $e) { return null; }
            };
        @endphp
        @foreach($rows as $row)
            @php
                $driver = trim(($row->d_first_name ?? '').' '.($row->d_last_name ?? '')) ?: '—';
                $truck = trim(($row->tr_brand ?? '').' '.($row->tr_model ?? '').' '.($row->tr_plate ?? '')) ?: '—';
                $rowKind = $row->row_kind ?? 'event';
                $typeVal = (int)($row->type ?? 0);
                $isEventRow = $rowKind === 'event';
                $isExpenseRow = $rowKind === 'expense';

                $rawOccurred = $row->occurred_at ?? null;
                $rawExpenseDate = $row->expense_date ?? null;
                if (!empty($rawOccurred)) {
                    $ts = date('d.m.Y H:i', strtotime($rawOccurred));
                } elseif (!empty($rawExpenseDate)) {
                    $ts = date('d.m.Y', strtotime($rawExpenseDate));
                } else {
                    $ts = '—';
                }

                $odoMainValue = $row->odometer_km ?? null;
                if ($isEventRow && $odoMainValue === null) {
                    if ($typeVal === TruckOdometerEvent::TYPE_DEPARTURE && isset($row->trip_odo_start_km)) $odoMainValue = $row->trip_odo_start_km;
                    elseif ($typeVal === TruckOdometerEvent::TYPE_RETURN && isset($row->trip_odo_end_km)) $odoMainValue = $row->trip_odo_end_km;
                }
                $odo = $odoMainValue !== null ? number_format((float)$odoMainValue, 1, ',', ' ') : '—';

                $typeLabel = '—';
                if ($isExpenseRow) {
                    $typeLabel = $row->expense_type_label ?? __('app.stats.events.badge_expense');
                    if (!empty($row->te_liters)) $typeLabel .= ' • '.number_format((float)$row->te_liters, 2, ',', ' ').' L';
                } elseif ($isEventRow && $typeVal === TruckOdometerEvent::TYPE_STEP) {
                    $stepLabel = $stepStatusLabel($row->step_status ?? null);
                    $stepName = trim((string)($row->step_address ?? ''));
                    $typeLabel = $stepName !== '' ? $stepName . ($stepLabel ? ' — ' . $stepLabel : '') : ($stepLabel ?? __('app.stats.events.badge_step'));
                } elseif ($isEventRow) {
                    if ($typeVal === TruckOdometerEvent::TYPE_DEPARTURE) $typeLabel = __('app.stats.departure_garage');
                    elseif ($typeVal === TruckOdometerEvent::TYPE_RETURN) $typeLabel = __('app.stats.return_garage');
                    else $typeLabel = __('app.stats.events.badge_event');
                }

                $amountStr = '—';
                if ($isExpenseRow && isset($row->amount)) {
                    $currency = $row->te_currency ?? 'EUR';
                    $amountStr = $currency . ' ' . number_format((float)$row->amount, 2, ',', ' ');
                }

                $details = '';
                if ($isExpenseRow && !empty($row->expense_type_label)) {
                    $details = $row->expense_type_label;
                    if (!empty($row->te_liters)) $details .= ' • ' . number_format((float)$row->te_liters, 2, ',', ' ') . ' L';
                    if (isset($row->odometer_km) && $row->odometer_km !== null) $details .= ' • ' . number_format((float)$row->odometer_km, 1, ',', ' ') . ' km';
                } elseif (!empty($row->note)) {
                    $details = $row->note;
                }
            @endphp
            <tr>
                <td>{{ $driver }}</td>
                <td>{{ $truck }}</td>
                <td class="event-badge">{{ $typeLabel }}</td>
                <td>{{ $ts }}</td>
                <td class="num">{{ $odo }}</td>
                <td class="num">{{ $amountStr }}</td>
                <td>{{ $details }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
</body>
</html>

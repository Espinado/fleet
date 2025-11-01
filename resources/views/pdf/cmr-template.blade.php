<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>CMR Form</title>
    <style>
        /* === Подключаем Unicode-шрифт (для EU-символов) === */
        @font-face {
            font-family: 'DejaVu Sans';
            src: url('{{ public_path('fonts/DejaVuSans.ttf') }}') format('truetype');
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9.5px;
            line-height: 1.3;
            color: #000;
            margin: 10px;
        }

        .cmr {
            border: 1px solid #000;
            padding: 6px;
        }

        .header {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            border-bottom: 1px solid #000;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }

        .section {
            display: flex;
            border: 1px solid #000;
            border-top: none;
        }

        .cell {
            flex: 1;
            border-right: 1px solid #000;
            padding: 4px;
            min-height: 60px;
            vertical-align: top;
            word-break: break-word;
        }

        .cell:last-child { border-right: none; }

        .label {
            font-weight: bold;
            font-size: 9px;
            margin-bottom: 2px;
            border-bottom: 0.5px solid #999;
        }

        .footer {
            display: flex;
            border: 1px solid #000;
            border-top: none;
        }

        .block {
            flex: 1;
            border-right: 1px solid #000;
            padding: 4px;
            min-height: 60px;
        }

        .block:last-child { border-right: none; }

        .small { font-size: 8px; color: #555; }

        .meta {
            font-size: 8px;
            text-align: center;
            margin-top: 3px;
            color: #666;
        }

        .cargo-block {
            border-top: 1px solid #000;
            padding: 5px 6px;
            page-break-inside: avoid;
        }

        .cargo-block b {
            display: inline-block;
            width: 130px;
        }

        .cargo-summary {
            border-top: 1px solid #000;
            padding: 5px;
            font-weight: bold;
            background: #f8f8f8;
        }
    </style>
</head>
<body>
<div class="cmr">

    <div class="header">
        МЕЖДУНАРОДНАЯ ТОВАРНО-ТРАНСПОРТНАЯ НАКЛАДНАЯ (CMR)
    </div>

    {{-- === 1–2 Отправитель / Получатель === --}}
    <div class="section" style="border-top:1px solid #000;">
        {{-- 1️⃣ Отправитель --}}
        <div class="cell">
            <div class="label">1. Отправитель</div>
            @php
                $senderName     = $sender['name'] ?? '—';
                $senderReg      = $sender['reg_nr'] ?? null;
                $senderAddress  = $sender['address'] ?? null;
                $senderCity     = $sender['city'] ?? null;
                $senderCountry  = $sender['country'] ?? null;
                $senderFull     = implode(', ', array_filter([$senderAddress, $senderCity, $senderCountry]));
            @endphp
            <strong>{{ $senderName }}</strong><br>
            @if($senderReg) Reg. Nr: {{ $senderReg }}<br> @endif
            {{ $senderFull ?: '—' }}
        </div>

        {{-- 2️⃣ Получатель --}}
        <div class="cell">
            <div class="label">2. Получатель</div>
            @php
                $receiverName     = $receiver['name'] ?? '—';
                $receiverReg      = $receiver['reg_nr'] ?? null;
                $receiverAddress  = $receiver['address'] ?? null;
                $receiverCity     = $receiver['city'] ?? null;
                $receiverCountry  = $receiver['country'] ?? null;
                $receiverFull     = implode(', ', array_filter([$receiverAddress, $receiverCity, $receiverCountry]));
            @endphp
            <strong>{{ $receiverName }}</strong><br>
            @if($receiverReg) Reg. Nr: {{ $receiverReg }}<br> @endif
            {{ $receiverFull ?: '—' }}
        </div>
    </div>

    {{-- === 3–4 Места === --}}
    <div class="section">
        {{-- 3️⃣ Место погрузки --}}
        <div class="cell">
            <div class="label">3. Место погрузки</div>
            @php
                $loadParts = array_filter([$loading_address ?? null, $loading_place ?? null]);
            @endphp
            {{ implode(', ', $loadParts) ?: '—' }}
        </div>

        {{-- 4️⃣ Место разгрузки --}}
        <div class="cell">
            <div class="label">4. Место разгрузки</div>
            @php
                $unloadParts = array_filter([$unloading_address ?? null, $unloading_place ?? null]);
            @endphp
            {{ implode(', ', $unloadParts) ?: '—' }}
        </div>
    </div>

    {{-- === 5 + 16 === --}}
    <div class="section">
        <div class="cell">
            <div class="label">5. Приложенные документы</div>
            {{ $documents ?? '—' }}
        </div>

        <div class="cell">
            <div class="label">16. Перевозчик / Экспедитор</div>
            @php
                $carrierName     = $carrier['name'] ?? '—';
                $carrierReg      = $carrier['reg_nr'] ?? null;
                $carrierAddress  = $carrier['address'] ?? null;
                $carrierCity     = $carrier['city'] ?? null;
                $carrierCountry  = $carrier['country'] ?? null;
                $carrierFull     = implode(', ', array_filter([$carrierAddress, $carrierCity, $carrierCountry]));
                $driverName      = $carrier['driver'] ?? '—';
                $truckBrand      = $carrier['truck'] ?? '—';
                $truckPlate      = $carrier['truck_plate'] ?? '—';
                $trailerBrand    = $carrier['trailer'] ?? '—';
                $trailerPlate    = $carrier['trailer_plate'] ?? '—';
            @endphp
            <strong>{{ $carrierName }}</strong><br>
            @if($carrierReg) Reg. Nr: {{ $carrierReg }}<br> @endif
            {{ $carrierFull ?: '—' }}
            <hr style="border:0;border-top:0.5px solid #999; margin:4px 0;">
            <div style="font-size:8.5px; line-height:1.4;">
                <b>Driver:</b> {{ $driverName }}<br>
                <b>Truck:</b> {{ $truckBrand }} ({{ $truckPlate }})<br>
                <b>Trailer:</b> {{ $trailerBrand }} ({{ $trailerPlate }})
            </div>
        </div>
    </div>

 {{-- === Грузы в табличном формате CMR (6–12) === --}}
@php
    $totalQty = collect($items ?? [])->sum('qty');
    $totalWeight = collect($items ?? [])->sum('gross');
@endphp

<table style="width:100%; border-collapse:collapse; font-size:9px; margin-top:-1px; border:1px solid #000;">
    <thead style="background:#f5f5f5;">
        <tr>
            <th style="border:1px solid #000; padding:4px; text-align:center;">№</th>
            <th style="border:1px solid #000; padding:4px; text-align:left;">6️⃣ Знаки и номера</th>
            <th style="border:1px solid #000; padding:4px; text-align:center;">7️⃣ Кол-во мест</th>
            <th style="border:1px solid #000; padding:4px; text-align:left;">10️⃣ Наименование груза</th>
            <th style="border:1px solid #000; padding:4px; text-align:right;">11️⃣ Вес брутто (кг)</th>
            <th style="border:1px solid #000; padding:4px; text-align:right;">12️⃣ Объём (м³)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items ?? [] as $index => $item)
            <tr>
                <td style="border:1px solid #000; padding:3px; text-align:center;">{{ $index + 1 }}</td>
                <td style="border:1px solid #000; padding:3px;">{{ $item['marks'] ?? '—' }}</td>
                <td style="border:1px solid #000; padding:3px; text-align:center;">{{ $item['qty'] ?? '—' }}</td>
                <td style="border:1px solid #000; padding:3px;">{{ $item['desc'] ?? '—' }}</td>
                <td style="border:1px solid #000; padding:3px; text-align:right;">{{ $item['gross'] ?? '—' }}</td>
                <td style="border:1px solid #000; padding:3px; text-align:right;">{{ $item['volume'] ?? '—' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" style="border:1px solid #000; padding:5px; text-align:center; color:#666;">
                    — Нет данных о грузе —
                </td>
            </tr>
        @endforelse
    </tbody>
    <tfoot style="font-weight:bold; background:#f8f8f8;">
        <tr>
            <td colspan="2" style="border:1px solid #000; padding:4px; text-align:right;">ИТОГО:</td>
            <td style="border:1px solid #000; padding:4px; text-align:center;">{{ $totalQty ?: '—' }}</td>
            <td style="border:1px solid #000; padding:4px; text-align:right;">—</td>
            <td style="border:1px solid #000; padding:4px; text-align:right;">{{ $totalWeight ?: '—' }}</td>
            <td style="border:1px solid #000; padding:4px; text-align:right;">—</td>
        </tr>
    </tfoot>
</table>

    {{-- === 22–24 Подписи === --}}
    <div class="footer">
        <div class="block">
            <div class="label">22. Отправитель</div>
            <div class="small">Подпись / печать</div>
        </div>
        <div class="block">
            <div class="label">23. Перевозчик</div>
            <div class="small">Подпись / печать</div>
        </div>
        <div class="block">
            <div class="label">24. Получатель</div>
            <div class="small">Подпись / печать</div>
        </div>
    </div>

    <p class="meta">
        * Автоматически создано системой Fleet Manager ({{ $date ?? date('d.m.Y') }})
    </p>
</div>
</body>
</html>

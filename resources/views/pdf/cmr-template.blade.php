{{-- resources/views/pdf/cmr-template.blade.php --}}
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>CMR Consignment Note</title>

    <style>
        @font-face {
            font-family: 'DejaVu Sans';
            src: url('{{ public_path('fonts/DejaVuSans.ttf') }}') format('truetype');
        }

        html, body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11.5px;
            line-height: 1.3;
            color: #000;
            margin: 0;
            padding: 0;
        }

        @page {
            size: A4;
            margin: 12mm 14mm;
        }

        .wrapper {
            padding: 6mm;
            position: relative;
            z-index: 2;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            page-break-inside: avoid;
        }

        td, th {
            border: 0.7px solid #000;
            padding: 4px 6px;
            vertical-align: top;
        }

        th {
            background: #f2f2f2;
            text-align: center;
        }

        .title {
            font-weight: bold;
            text-align: center;
            font-size: 13px;
            border: 1px solid #000;
            padding: 6px;
            margin-bottom: 8px;
        }

        .field-num {
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 2px;
        }

        .center { text-align: center; }
        .right { text-align: right; }

        .cmr-bg {
            position: fixed;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            font-size: 140px;
            color: rgba(0,0,0,0.05);
            font-weight: bold;
            letter-spacing: 3px;
            z-index: 0;
        }

        ul {
            margin: 0 0 4px 14px;
            padding: 0;
            list-style: disc;
        }
    </style>
</head>

<body>

<div class="cmr-bg">CMR</div>

<div class="wrapper">

    <div class="title">
        TARPTAUTINIS KROVINIŲ TRANSPORTAVIMO VAŽTARAŠTIS /
        STARPTAUTISKĀ KRAVAS PIEGĀDES PAVADZĪME /
        INTERNATIONAL CONSIGNMENT NOTE (CMR) Nr. {{ $cmr_nr ?? '—' }}
    </div>

    {{-- === BLOCK 1–5 === --}}
    <table>
        <tr>
            <td width="50%">
                <div class="field-num">1. Sūtītājs / Sender</div>
                <b>{{ $sender['name'] }}</b><br>
                {{ $sender['address'] }}<br>
                {{ $sender['city'] }}, {{ $sender['country'] }}<br>
                Reg. Nr: {{ $sender['reg_nr'] }}
            </td>

            <td>
                <div class="field-num">2. Saņēmējs / Consignee</div>
                <b>{{ $receiver['name'] }}</b><br>
                {{ $receiver['address'] }}<br>
                {{ $receiver['city'] }}, {{ $receiver['country'] }}<br>
                Reg. Nr: {{ $receiver['reg_nr'] }}
            </td>
        </tr>

        <tr>
            <td>
                <div class="field-num">4. Iekraušanas vieta un datums / Place and date of taking over</div>

                {{-- MULTIPLE LOADING PLACES --}}
                @foreach($loading_places as $p)
                    • {{ $p }}<br>
                @endforeach

                Date: {{ $date }}
            </td>

            <td>
                <div class="field-num">3. Piegādes vieta / Place of delivery</div>

                {{-- MULTIPLE UNLOADING PLACES --}}
                @foreach($unloading_places as $p)
                    • {{ $p }}<br>
                @endforeach
            </td>
        </tr>

        <tr>
            <td colspan="2">
                <div class="field-num">5. Pievienotie dokumenti / Documents attached</div>
                Invoice nr. {{ $cmr_nr }}
            </td>
        </tr>
    </table>


    {{-- === BLOCK 6–12 MAIN SUM TABLE === --}}
    <table>
        <tr class="center">
            <th width="10%">6. Paletes</th>
            <th width="8%">7. Vietas</th>
            <th width="12%">8. Tonnas</th>
            <th width="29%">9. Apraksts</th>
            <th width="12%">10. Cena ar PVN</th>
            <th width="9%">11. Bruto kg</th>
            <th width="8%">12. m³</th>
        </tr>

        @php
            $t_pallets = 0;
            $t_packages = 0;
            $t_tonnes = 0;
            $t_gross = 0;
            $t_volume = 0;
            $t_price = 0;
        @endphp

        @foreach($items as $it)
            @php
                $pallets = $it['pallets'] ?? 0;
                $packages = $it['packages'] ?? 0;
                $tonnes = $it['tonnes'] ?? 0;
                $gross = $it['gross_weight'] ?? 0;
                $volume = $it['volume'] ?? 0;
                $price = $it['price_with_tax'] ?? 0;

                $t_pallets += $pallets;
                $t_packages += $packages;
                $t_tonnes += $tonnes;
                $t_gross += $gross;
                $t_volume += $volume;
                $t_price += $price;
            @endphp

            <tr>
                <td class="center">{{ $pallets ?: '—' }}</td>
                <td class="center">{{ $packages ?: '—' }}</td>
                <td class="center">{{ $tonnes ?: '—' }}</td>
                <td>{{ $it['description'] ?? '' }}</td>
                <td class="right">
                    @if($price)
                        {{ number_format($price, 2, '.', ' ') }} €
                    @else — @endif
                </td>
                <td class="right">{{ $gross ?: '—' }}</td>
                <td class="right">{{ $volume ?: '—' }}</td>
            </tr>
        @endforeach

        <tr style="font-weight:bold; background:#f4f4f4;">
            <td class="center">{{ $t_pallets }}</td>
            <td class="center">{{ $t_packages }}</td>
            <td class="center">{{ number_format($t_tonnes, 2) }}</td>
            <td class="right">TOTALS:</td>
          <td class="right">{{ number_format($total_price_with_tax, 2, '.', ' ') }} €</td>
            <td class="right">{{ number_format($t_gross, 2) }}</td>
            <td class="right">{{ number_format($t_volume, 2) }}</td>
        </tr>
    </table>


{{-- === ITEM DETAILS BLOCK (HORIZONTAL TABLE) === --}}
{{-- === ITEM DETAILS BLOCK: SINGLE TABLE WITH SPLIT AFTER 8 ROWS === --}}
@if(!empty($items))

    <h4 style="margin:10px 0 4px; font-weight:bold;">Cargo item details</h4>

    @php
        // Разбиваем на две части: первые 8, остальные — вторая таблица
        $chunks = array_chunk($items, 8);
    @endphp

    @foreach($chunks as $chunkIndex => $chunk)
        <table style="margin-bottom:10px;">
            <tr class="center">
                <th>Paletes</th>
                <th>Vietas</th>
                <th>Gab.</th>
                <th>Tonnas</th>
                <th>Net kg</th>
                <th>Bruto kg</th>
                <th>m³</th>
                <th>LM</th>
                <th>ADR</th>
                <th>Temp</th>
                <th>Stackable</th>
                <th>Apraksts</th>
            </tr>

            @foreach($chunk as $it)
                @php
                    $pallets = $it['pallets'] ?? '—';
                    $packages = $it['packages'] ?? '—';
                    $units = $it['units'] ?? '—';
                    $tonnes = $it['tonnes'] ?? '—';
                    $net = $it['net_weight'] ?? '—';
                    $gross = $it['gross_weight'] ?? '—';
                    $volume = $it['volume'] ?? '—';
                    $lm = $it['loading_meters'] ?? '—';
                    $adr = $it['hazmat'] ?? '—';
                    $temp = $it['temperature'] ?? '—';
                    $stack = isset($it['stackable']) ? ($it['stackable'] ? 'Yes' : 'No') : '—';
                    $desc = $it['description'] ?? '—';
                @endphp

                <tr>
                    <td class="center">{{ $pallets }}</td>
                    <td class="center">{{ $packages }}</td>
                    <td class="center">{{ $units }}</td>
                    <td class="center">{{ $tonnes }}</td>
                    <td class="right">{{ $net }}</td>
                    <td class="right">{{ $gross }}</td>
                    <td class="right">{{ $volume }}</td>
                    <td class="center">{{ $lm }}</td>
                    <td class="center">{{ $adr }}</td>
                    <td class="center">{{ $temp }}</td>
                    <td class="center">{{ $stack }}</td>
                    <td>{{ $desc }}</td>
                </tr>
            @endforeach

        </table>
    @endforeach

@endif




    {{-- === BLOCK 13–20 === --}}
    <table>
        <tr>
            <td width="50%">
                <div class="field-num">13. Nosūtītāja norādījumi / Sender's instructions</div>
                — 
            </td>
            <td>
                <div class="field-num">14. Samaksa pēc piegādes / Cash on delivery</div>
                —
            </td>
        </tr>

        <tr>
            <td>
                <div class="field-num">15. Apdrošinājuma vērtība / Declared value</div>
                —
            </td>

            <td>
                <div class="field-num">16. Pārvadātājs / Carrier</div>
                <b>{{ $carrier['name'] }}</b><br>
                {{ $carrier['address'] }}, {{ $carrier['city'] }}<br>
                {{ $carrier['country'] }}<br>
                Reg. Nr: {{ $carrier['reg_nr'] }}
            </td>
        </tr>
    </table>


    {{-- === FOOTER 21–25 === --}}
    <table>
        <tr>
            <td width="25%">
                <div class="field-num">21. Sastādīts / Established</div>
                {{ $date }}
            </td>

            <td width="25%">
                <div class="field-num">22. Iekraušana / Taking over</div>
                Time: ___________
            </td>

            <td width="25%">
                <div class="field-num">23. Transportlīdzeklis</div>
                {{ $carrier['truck'] }} &nbsp;{{ $carrier['truck_plate'] }}
            </td>

            <td>
                <div class="field-num">24. Piekabe / Trailer</div>
                {{ $carrier['trailer'] }} &nbsp;{{ $carrier['trailer_plate'] }}
            </td>
        </tr>
    </table>

    <table style="margin-top: 6px;">
        <tr>
            <td>
                <div class="field-num">25. Krava saņemta / Goods received</div>
                Date: ________________ &nbsp;&nbsp; Time: ________________<br><br>
                Signature and stamp of consignee:
                <div style="height:40px; border:1px solid #000; margin-top:4px;"></div>
            </td>
        </tr>
    </table>

</div>
</body>
</html>

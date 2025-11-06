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
            line-height: 1.25;
            color: #000;
            margin: 0;
            padding: 0;
        }

        /* ⚙️ DomPDF A4 */
        @page {
            size: A4;
            margin-top: 16mm;
            margin-right: 16mm;
            margin-bottom: 16mm;
            margin-left: 16mm;
        }

        .wrapper {
            padding: 8mm 8mm 5mm 8mm;
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
            padding: 3px 5px;
            vertical-align: top;
        }

        th {
            background: #f8f8f8;
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
            font-size: 10.5px;
            margin-bottom: 2px;
        }

        .center { text-align: center; }
        .right { text-align: right; }

        .cmr-bg {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 140px;
            color: rgba(0,0,0,0.05);
            font-weight: bold;
            letter-spacing: 3px;
            z-index: 0;
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

    {{-- === 1–5 === --}}
    <table>
        <tr>
            <td width="50%">
                <div class="field-num">1. Sūtītājs / Sender</div>
                <b>{{ $sender['name'] ?? '—' }}</b><br>
                {{ $sender['address'] ?? '' }}<br>
                {{ $sender['city'] ?? '' }}, {{ $sender['country'] ?? '' }}<br>
                Reg. Nr: {{ $sender['reg_nr'] ?? '—' }}
            </td>
            <td>
                <div class="field-num">2. Saņēmējs / Consignee</div>
                <b>{{ $receiver['name'] ?? '—' }}</b><br>
                {{ $receiver['address'] ?? '' }}<br>
                {{ $receiver['city'] ?? '' }}, {{ $receiver['country'] ?? '' }}<br>
                Reg. Nr: {{ $receiver['reg_nr'] ?? '—' }}
            </td>
        </tr>
        <tr>
            <td>
                <div class="field-num">4. Iekraušanas vieta un datums / Place and date of taking over</div>
                {{ $loading_address ?? '' }}<br>
                {{ $loading_place ?? '' }}<br>
                Date: {{ $date ?? '' }}
            </td>

            <td>
                <div class="field-num">3. Piegādes vieta / Place of delivery</div>
                {{ $unloading_address ?? '' }}<br>
                {{ $unloading_place ?? '' }}
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div class="field-num">5. Pievienotie dokumenti / Documents attached</div>
                Invoice nr. {{ $cmr_nr ?? '—' }}
            </td>
        </tr>
    </table>

    {{-- === 6–13 === --}}
    <table>
        <tr class="center">
            <th width="10%">6. Paletes<br>Pallets</th>
            <th width="8%">7. Vietu skaits<br>Packages</th>
            <th width="12%">8. Svars, tonnas<br>Weight, tonnes</th>
            <th width="29%">9. Kravas apraksts<br>Nature of goods</th>
            <th width="12%">10. Cena (ar PVN)<br>Price (with tax)</th>
            <th width="9%">11. Bruto svars, kg<br>Gross weight</th>
            <th width="8%">12. Tilpums, m³<br>Volume</th>
        </tr>

        @php
            $totalPaletes = 0;
            $totalPackages = 0;
            $totalTonnes = 0;
            $totalGross = 0;
            $totalVolume = 0;
            $totalPriceWithTax = 0;
        @endphp

        @forelse($items ?? [] as $it)
            @php
                $paletes = (float)($it['cargo_paletes'] ?? 0);
                $packages = (float)($it['packages'] ?? 0);
                $tonnes = (float)($it['cargo_tonnes'] ?? 0);
                $gross = (float)($it['gross'] ?? ($it['weight'] ?? 0));
                $volume = (float)($it['volume'] ?? 0);
                $priceWithTax = (float)($it['price_with_tax'] ?? 0);

                $totalPaletes += $paletes;
                $totalPackages += $packages;
                $totalTonnes += $tonnes;
                $totalGross += $gross;
                $totalVolume += $volume;
                $totalPriceWithTax += $priceWithTax;
            @endphp

            <tr>
                <td class="center">{{ $paletes ?: '—' }}</td>
                <td class="center">{{ $packages ?: '—' }}</td>
                <td class="center">{{ $tonnes ?: '—' }}</td>
                <td>{{ $it['desc'] ?? '' }}</td>
                <td class="right">{{ $priceWithTax ? number_format($priceWithTax, 2, '.', ' ') . ' €' : '—' }}</td>
                <td class="right">{{ $gross ?: '—' }}</td>
                <td class="right">{{ $volume ?: '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="center small">No cargo items</td></tr>
        @endforelse

        <tr style="font-weight:bold; background:#f4f4f4;">
            <td class="center">{{ $totalPaletes, 2, '.', ' ' }}</td>
            <td class="center">{{ $totalPackages, 2, '.', ' ' }}</td>
            <td class="center">{{ number_format($totalTonnes, 2, '.', '') }}</td>
            <td class="right">TOTALS:</td>
            <td class="right">{{ number_format($totalPriceWithTax, 2, '.', ' ') }} €</td>
            <td class="right">{{ number_format($totalGross, 2, '.', '') }}</td>
            <td class="right">{{ number_format($totalVolume, 2, '.', '') }}</td>
        </tr>
    </table>

    {{-- === 13–20 === --}}
    <table>
        <tr>
            <td width="50%">
                <div class="field-num">13. Nosūtītāja norādījumi / Sender's instructions</div>
                {{ $instructions ?? '—' }}
            </td>
            <td>
                <div class="field-num">14. Samaksa pēc piegādes / Cash on delivery</div>
                {{ $cash_on_delivery ?? '—' }}
            </td>
        </tr>
        <tr>
            <td>
                <div class="field-num">15. Apdrošinājuma vērtība / Declared value</div>
                {{ $declared_value ?? '—' }}
            </td>
            <td>
                <div class="field-num">16. Pārvadātājs / Carrier</div>
                <b>{{ $carrier['name'] ?? '—' }}</b><br>
                {{ $carrier['address'] ?? '' }}, {{ $carrier['city'] ?? '' }}<br>
                {{ $carrier['country'] ?? '' }}<br>
                Reg. Nr: {{ $carrier['reg_nr'] ?? '' }}
            </td>
        </tr>
    </table>

    {{-- === 21–25 === --}}
    <table>
        <tr>
            <td width="25%">
                <div class="field-num">21. Sastādīts / Established</div>
                Date: {{ $date ?? '' }}
            </td>
            <td width="25%">
                <div class="field-num">22. Iekraušana / Taking over</div>
                Time: ___________
            </td>
            <td width="25%">
                <div class="field-num">23. Transportlīdzeklis / Vehicle Reg. No</div>
                {{ $carrier['truck'] ?? '' }} &nbsp;{{ $carrier['truck_plate'] ?? '' }}
            </td>
            <td>
                <div class="field-num">24. Piekabe / Trailer</div>
                {{ $carrier['trailer'] ?? '' }} &nbsp;{{ $carrier['trailer_plate'] ?? '' }}
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

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
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 0;
        }

        /* ⚙️ Реальные поля для DomPDF */
        @page {
            size: A4;
            margin-top: 20mm;
            margin-right: 20mm;
            margin-bottom: 20mm;
            margin-left: 20mm;
        }

        .wrapper {
            padding: 10mm 10mm 5mm 10mm;
            position: relative;
            z-index: 2;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            page-break-inside: avoid;
        }

        td, th {
            border: 0.7px solid #000;
            padding: 5px 6px;
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
            padding: 8px;
            margin-bottom: 10px;
        }

        .field-num { font-weight: bold; font-size: 11px; margin-bottom: 4px; }
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

    {{-- === 6–12 === --}}
    <table>
        <tr class="center">
            <th width="14%">6. Zīmes un numuri<br>Marks & Numbers</th>
            <th width="8%">7. Vietu skaits<br>Number<br>of packages</th>
            <th width="13%">8. Iepakojuma veids<br>Method of packing</th>
            <th width="38%">9. Kravas apraksts<br>Nature of goods</th>
            <th width="9%">11. Bruto svars, kg<br>Gross weight</th>
            <th width="8%">12. Tilpums, m³<br>Volume</th>
        </tr>
        @forelse($items ?? [] as $it)
            <tr>
                <td>{{ $it['marks'] ?? '' }}</td>
                <td class="center">{{ $it['qty'] ?? '' }}</td>
                <td class="center">—</td>
                <td>{{ $it['desc'] ?? '' }}</td>
                <td class="right">{{ $it['gross'] ?? '' }}</td>
                <td class="right">{{ $it['volume'] ?? '' }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="center small">No cargo items</td></tr>
        @endforelse
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

    {{-- === 25. Подпись получателя === --}}
    <table style="margin-top: 8px;">
        <tr>
            <td>
                <div class="field-num">25. Krava saņemta / Goods received</div>
                Date: ________________ &nbsp;&nbsp; Time: ________________<br><br>
                Signature and stamp of consignee:
                <div style="height:45px; border:1px solid #000; margin-top:4px;"></div>
            </td>
        </tr>
    </table>

</div>
</body>
</html>

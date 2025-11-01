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

        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            color: #000;
        }

        /* 🟦 Увеличенные отступы */
        @page { size: A4; margin: 20mm 15mm 18mm 15mm; }

        table { border-collapse: collapse; width: 100%; page-break-inside: avoid; }
        td, th {
            border: 0.7px solid #000;
            vertical-align: top;
            padding: 3px 4px;
        }
        th { background: #f8f8f8; }

        .title {
            font-weight: bold;
            text-align: center;
            font-size: 12px;
            padding: 6px;
            border: 1px solid #000;
            margin-bottom: 2px;
        }

        .field-num { font-weight: bold; font-size: 8px; margin-bottom: 2px; }
        .small { font-size: 8px; color: #555; }
        .right { text-align: right; }
        .center { text-align: center; }

        .cmr-bg {
            position: absolute;
            top: 47%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 120px;
            color: rgba(0,0,0,0.06);
            font-weight: bold;
            z-index: 0;
            letter-spacing: 2px;
        }

        .page {
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body>

<div class="cmr-bg">CMR</div>
<div class="page">

    {{-- === Header title === --}}
    <div class="title">
        TARPTAUTINIS KROVINIŲ TRANSPORTAVIMO VAŽTARAŠTIS / 
        STARPTAUTISKĀ KRAVAS PIEGĀDES PAVADZĪME / 
        INTERNATIONAL CONSIGNMENT NOTE (CMR)
    </div>

    {{-- === 1–2 Sender / Consignee === --}}
    <table>
        <tr>
            <td width="50%">
                <div class="field-num">1. Sūtītājs / Sender (name, address, country)</div>
                <b>{{ $sender['name'] ?? '—' }}</b><br>
                {{ $sender['address'] ?? '' }}<br>
                {{ $sender['city'] ?? '' }}, {{ $sender['country'] ?? '' }}<br>
                Reg. Nr: {{ $sender['reg_nr'] ?? '—' }}
            </td>
            <td>
                <div class="field-num">2. Saņēmējs / Consignee (name, address, country)</div>
                <b>{{ $receiver['name'] ?? '—' }}</b><br>
                {{ $receiver['address'] ?? '' }}<br>
                {{ $receiver['city'] ?? '' }}, {{ $receiver['country'] ?? '' }}<br>
                Reg. Nr: {{ $receiver['reg_nr'] ?? '—' }}
            </td>
        </tr>

        {{-- === 3–5 === --}}
        <tr>
            <td width="50%">
                <div class="field-num">3. Piegādes vieta / Place of delivery</div>
                {{ $unloading_address ?? '' }}<br>
                {{ $unloading_place ?? '' }}
            </td>
            <td>
                <div class="field-num">4. Iekraušanas vieta un datums / Place and date of taking over</div>
                {{ $loading_address ?? '' }}<br>
                {{ $loading_place ?? '' }}<br>
                Date: {{ $date ?? '' }}
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div class="field-num">5. Pievienotie dokumenti / Documents attached</div>
                {{ $documents ?? '—' }}
            </td>
        </tr>
    </table>

    {{-- === 6–12 Cargo table === --}}
    <table style="margin-top:-1px;">
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
                <div class="field-num">13. Nosūtītāja norādījumi / Sender's instructions (Customs and other formalities)</div>
                {{ $instructions ?? '—' }}
            </td>
            <td>
                <div class="field-num">14. Samaksa pēc piegādes / Cash on delivery</div>
                {{ $cash_on_delivery ?? '—' }}
            </td>
        </tr>
        <tr>
            <td>
                <div class="field-num">15. Apdrošinājuma vērtība / Declared value of goods</div>
                {{ $declared_value ?? '—' }}
            </td>
            <td>
                <div class="field-num">16. Pārvadātājs / Carrier (name, address, country)</div>
                <b>{{ $carrier['name'] ?? '—' }}</b><br>
                {{ $carrier['address'] ?? '' }}, {{ $carrier['city'] ?? '' }}<br>
                {{ $carrier['country'] ?? '' }}<br>
                Reg. Nr: {{ $carrier['reg_nr'] ?? '' }}<br>
             
            </td>
        </tr>
        <tr>
            <td>
                <div class="field-num">17. Citi pārvadātāji / Following carrier</div>
                {{ $following_carrier ?? '—' }}
            </td>
            <td>
                <div class="field-num">18. Pārvadātāja piezīmes / Carrier’s observations</div>
                {{ $carrier_notes ?? '—' }}
            </td>
        </tr>
        <tr>
            <td>
                <div class="field-num">19. Apmaksa / To be paid by</div>
                {{ $payment_terms ?? '—' }}
            </td>
            <td>
                <div class="field-num">20. Īpašie nosacījumi / Special agreements</div>
                {{ $special_terms ?? '—' }}
            </td>
        </tr>
    </table>

    {{-- === 21–23 Signatures === --}}
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

    {{-- === New 25. Goods received === --}}
    <table style="margin-top:10px;">
        <tr>
            <td>
                <div class="field-num">25. Krava saņemta / Goods received</div>
                Date: ___________ &nbsp;&nbsp; Time: ___________<br><br>
                Signature and stamp of consignee:
                <div style="height:40px; border:1px solid #000; margin-top:4px;"></div>
            </td>
        </tr>
    </table>

</div>
</body>
</html>

{{-- resources/views/pdf/invoice-template.blade.php --}}
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Faktūrrēķins</title>

    <style>
        @font-face {
            font-family: 'DejaVu Sans';
            src: url('{{ public_path('fonts/DejaVuSans.ttf') }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        html, body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            color: #000;
            line-height: 1.4;
        }

        @page {
            size: A4;
            margin: 28mm 20mm 25mm 24mm;
        }

        .wrapper {
            width: 100%;
            padding: 0 2mm;
            box-sizing: border-box;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .cell {
            padding: 2mm 2.3mm;
            vertical-align: top;
            word-wrap: break-word;
        }

        .table-border th,
        .table-border td {
            border: 0.8px solid #000;
        }

        .fw-bold { font-weight: bold; }
        .text-center { text-align: center; }
        .text-right  { text-align: right; }
        .small { font-size: 10px; }

        .mt-2 { margin-top: 2mm; }
        .mt-3 { margin-top: 3mm; }
        .mt-5 { margin-top: 5mm; }

        .w-10 { width: 10%; }
        .w-12 { width: 12%; }
        .w-15 { width: 15%; }
        .w-18 { width: 18%; }
        .w-33 { width: 33.33%; }
        .w-50 { width: 50%; }

        .amount {
            text-align: right;
            white-space: nowrap;
        }

        .signature-block {
            margin-top: 28mm;
        }

        .signature-note {
            font-size: 11px;
        }
    </style>
</head>

<body>

<div class="wrapper">

    {{-- ==== HEADER: DATE & INVOICE NUMBER ==== --}}
    <table class="no-border">
        <tr>
            <td class="cell">Datums: {{ $invoice_date }}</td>
            <td class="cell text-right">Faktūrrēķins Nr.: {{ $invoice_nr }}</td>
        </tr>
    </table>


    {{-- ==== SUPPLIER & PAYER ==== --}}
    <table class="no-border mt-3">
        <tr>

            {{-- EXPEDITOR --}}
            <td class="cell w-50">
                <div class="fw-bold">Piegādātājs</div>
                <div class="fw-bold">{{ $expeditor['name'] }}</div>
                <div>Vien. reģ. Nr.: {{ $expeditor['reg_nr'] }}</div>
                <div>Adrese: {{ $expeditor['address'] }}, {{ $expeditor['city'] }}</div>
                <div>Valsts: {{ $expeditor['country'] }}</div>
                <div>E-pasts: {{ $expeditor['email'] }}</div>
                <div>Tālrunis: {{ $expeditor['phone'] }}</div>
                <div>Banka: {{ $expeditor['bank_name'] }} {{ $expeditor['bic'] }}</div>
                <div>Konts: {{ $expeditor['iban'] }}</div>
            </td>

            {{-- PAYER --}}
            <td class="cell w-50">
                <div class="fw-bold">Maksātājs ({{ $payer['label'] }})</div>
                <div class="fw-bold">{{ $payer['name'] }}</div>
                <div>Vien. reģ. Nr.: {{ $payer['reg_nr'] }}</div>
                <div>Adrese: {{ $payer['address'] }}</div>
                <div>{{ $payer['city'] }}, {{ $payer['country'] }}</div>
            </td>
        </tr>
    </table>


    {{-- ==== ORDER & DATES ==== --}}
    <table class="table-border mt-5">
        <tr>
            <th class="cell w-33 text-center">Pasūtījuma Nr.</th>
            <th class="cell w-33 text-center">Iekraušanas datums</th>
            <th class="cell w-33 text-center">Izlādes datums</th>
        </tr>
        <tr>
            <td class="cell text-center">{{ $order_nr }}</td>

            <td class="cell text-center">
                {{ $first_loading_date ? \Carbon\Carbon::parse($first_loading_date)->format('d.m.Y') : '—' }}
            </td>

            <td class="cell text-center">
                {{ $last_unloading_date ? \Carbon\Carbon::parse($last_unloading_date)->format('d.m.Y') : '—' }}
            </td>
        </tr>
    </table>


    {{-- ==== MAIN SERVICES TABLE ==== --}}
    <table class="table-border mt-3">
        <tr>
            <th class="cell w-10 text-center">Nr.</th>
            <th class="cell">Nosaukums</th>
            <th class="cell w-15 text-center">Mērvienība</th>
            <th class="cell w-12 text-center">Daudzums</th>
            <th class="cell w-18 text-center">Cena</th>
            <th class="cell w-18 text-center">Summa</th>
        </tr>

        @php $nr = 1; @endphp
        @foreach ($cargos as $cg)
            <tr>
                <td class="cell text-center">{{ $nr++ }}</td>

                <td class="cell">
                    Transporta pakalpojumi <br>
                    {{ $loading_country_iso }} – {{ $unloading_country_iso }}

                    <div style="margin-left:8mm;">
                        a/m {{ $trip->truck->brand }} {{ $trip->truck->plate }}
                        / {{ $trip->trailer->brand }} {{ $trip->trailer->plate }}
                    </div>

                    <div style="margin-left:8mm;">
                        CMR Nr.: {{ $cg->cmr_nr ?? $order_nr }}
                    </div>
                </td>

                <td class="cell text-center">Reiss</td>
                <td class="cell text-center">1.00</td>

                <td class="cell amount">
                    {{ number_format($cg->price, 2, '.', ' ') }}
                </td>

                <td class="cell amount">
                    {{ number_format($cg->price, 2, '.', ' ') }}
                </td>
            </tr>
        @endforeach

        {{-- ==== TOTALS ==== --}}
        <tr>
            <td class="cell" colspan="4" rowspan="3"></td>
            <td class="cell fw-bold">Kopā:</td>
            <td class="cell amount fw-bold">{{ number_format($subtotal, 2, '.', ' ') }}</td>
        </tr>
        <tr>
            <td class="cell">PVN {{ $cargos->first()->tax_percent ?? 21 }}%</td>
            <td class="cell amount">{{ number_format($vat, 2, '.', ' ') }}</td>
        </tr>
        <tr>
            <td class="cell fw-bold">Summa apmaksai:</td>
            <td class="cell amount fw-bold">{{ number_format($total, 2, '.', ' ') }}</td>
        </tr>

    </table>


    {{-- ==== SUM IN WORDS ==== --}}
    <div class="mt-3">
        <span class="fw-bold">Summa vārdiem:</span> {{ $sum_in_words }}
    </div>


    {{-- ==== FOOTER ==== --}}
    <div class="signature-block">
        <div class="signature-note">
            Rēķins sagatavots elektroniski un derīgs bez paraksta
        </div>
    </div>

</div>

</body>
</html>

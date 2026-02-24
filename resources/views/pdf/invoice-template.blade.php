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

        @page { size: A4; margin: 0; }
        * { box-sizing: border-box; }

        html, body{
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 0;
            color: #000;

            /* ✅ крупнее */
            font-size: 13.2px;
            line-height: 1.35;
        }

   .sheet{
    width: 210mm;
    padding: 12mm 19mm 12mm 6.5mm; /* ещё левее */
    margin: 0 auto;
    position: relative;
    overflow: hidden;
}

/* рабочая ширина пересчитывается: 210 - (16+9)=185mm */
.outer{
    width: 185mm;
    margin: 0 auto;
    padding-right: 3mm;
}

        table{
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .cell{
            padding: 2.3mm 2.3mm;
            vertical-align: top;
            word-break: break-word;
            overflow: hidden;
        }

        .table-border th,
        .table-border td{
            border: 0.8px solid #000;
        }

        .no-border td, .no-border th{ border: none !important; }

        .fw-bold{ font-weight: bold; }
        .text-center{ text-align: center; }
        .text-right{ text-align: right; }
        .amount{ text-align: right; white-space: nowrap; }

        .small{ font-size: 11px; }
        .nowrap{ white-space: nowrap; }

        .mt-2{ margin-top: 2mm; }
        .mt-3{ margin-top: 3.2mm; }
        .mt-5{ margin-top: 5.5mm; }

        /* ✅ футер вниз листа */
      .footer{
    margin-top: 22mm;   /* подгоняем вниз */
}
.signature-note{ font-size: 11.5px; }
    </style>
</head>
<body>

<div class="sheet">
    <div class="outer">

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
                <td class="cell" style="width:50%;">
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

                <td class="cell" style="width:50%;">
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
                <th class="cell text-center" style="width:33.33%;">Pasūtījuma Nr.</th>
                <th class="cell text-center" style="width:33.33%;">Iekraušanas datums</th>
                <th class="cell text-center" style="width:33.33%;">Izkraušanas datums</th>
            </tr>
            <tr>
                <td class="cell text-center">{{ $order_nr ?: '—' }}</td>
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
    <th class="cell text-center" style="width:6%;">Nr.</th>
    <th class="cell" style="width:36%;">Nosaukums</th>
    <th class="cell text-center" style="width:16%;">Mērvienība</th>
    <th class="cell text-center nowrap" style="width:12%;">Daudzums</th>
    <th class="cell text-center" style="width:15%;">Cena</th>
    <th class="cell text-center" style="width:15%;">Summa</th>
</tr>

            @php $nr = 1; @endphp
            @foreach ($cargos as $cg)
                <tr>
                    <td class="cell text-center">{{ $nr++ }}</td>

                    <td class="cell">
                        Transporta pakalpojumi <br>
                        {{-- {{ $loading_country_iso }} – {{ $unloading_country_iso }} --}}

                        <div style="margin-left:4mm;">
                            a/m {{ $trip->truck->brand }} {{ $trip->truck->plate }}
                            / {{ $trip->trailer->brand }} {{ $trip->trailer->plate }}
                        </div>

                        <div style="margin-left:4mm;">
                            CMR Nr.: {{ $cg->cmr_nr ?? $order_nr }}
                        </div>
                         <div style="margin-left:4mm;">
                            Invoice Nr.: {{ $cg->supplier_invoice_nr }}
                        </div>
                    </td>

                    <td class="cell text-center">Reiss</td>
                    <td class="cell text-center">1.00</td>
                    <td class="cell amount">{{ number_format($cg->price, 2, '.', ' ') }}</td>
                    <td class="cell amount">{{ number_format($cg->price, 2, '.', ' ') }}</td>
                </tr>
            @endforeach

            {{-- ✅ TOTALS без rowspan и без огромной пустоты --}}
            <tr>
                <td class="cell" colspan="4"></td>
                <td class="cell fw-bold">Kopā:</td>
                <td class="cell amount fw-bold">{{ number_format($subtotal, 2, '.', ' ') }}</td>
            </tr>
            <tr>
                <td class="cell" colspan="4"></td>
              @php $taxPercent = (float)($cargos->first()->tax_percent ?? 21); @endphp
<td class="cell">
    PVN {{ rtrim(rtrim(number_format($taxPercent, 2, '.', ''), '0'), '.') }}%
    @if($taxPercent == 0)
        <sup style="font-size:9px;">*</sup>
    @endif
</td>
                <td class="cell amount">{{ number_format($vat, 2, '.', ' ') }}</td>
            </tr>
            <tr>
                <td class="cell" colspan="4"></td>
                <td class="cell fw-bold">Summa apmaksai:</td>
                <td class="cell amount fw-bold">{{ number_format($total, 2, '.', ' ') }}</td>
            </tr>
        </table>

        {{-- ==== SUM IN WORDS ==== --}}
        <div class="mt-3">
            <span class="fw-bold">Summa vārdiem:</span> {{ $sum_in_words ?: '—' }}
        </div>

        @if(($taxPercent ?? 21) == 0)
    <div class="mt-2 small">
        <sup style="font-size:9px;">*</sup>
        Saskana ar ligumu ...  {{-- тут потом вставишь полный текст --}}
    </div>
@endif

        {{-- ==== FOOTER ==== --}}
        <div class="footer">
            <div class="signature-note">
                Rēķins sagatavots elektroniski un derīgs bez paraksta
            </div>
        </div>

    </div>
</div>

</body>
</html>

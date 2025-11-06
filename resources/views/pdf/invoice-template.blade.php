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
        color: #000;
        margin: 0;
        padding: 0;
        line-height: 1.4;
    }

    @page {
        size: A4;
        margin-top: 28mm;
        margin-bottom: 25mm;
        margin-left: 24mm;
        margin-right: 20mm;
    }

    .wrapper {
        width: 96%;
        margin: 0 auto;
        padding: 2mm 6mm;
        box-sizing: border-box;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .cell { padding: 2.3mm; vertical-align: top; word-wrap: break-word; }
    .table-border, .table-border td, .table-border th { border: 0.8px solid #000; }
    .no-border td { border: none; }

    .fw-bold { font-weight: bold; }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .small { font-size: 10px; }

    .mt-2 { margin-top: 2mm; }
    .mt-3 { margin-top: 3mm; }
    .mt-5 { margin-top: 5mm; }

    .w-10 { width: 10%; }
    .w-12 { width: 12%; }
    .w-15 { width: 15%; }
    .w-18 { width: 18%; }
    .w-20 { width: 20%; }
    .w-25 { width: 25%; }
    .w-30 { width: 30%; }
    .w-33 { width: 33.33%; }
    .w-50 { width: 50%; }

    .amount { text-align: right; white-space: nowrap; }

    .signature-block {
        margin-top: 28mm;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
    }
    .signature-left { width: 50%; text-align: left; font-size: 11px; }
    .signature-right { width: 50%; text-align: right; font-size: 11px; }
    .sigline { margin-top: 18mm; border-top: 1px solid #000; width: 60%; }
    .footer-entry { margin-top: 12mm; font-size: 11px; }
</style>
</head>
<body>
<div class="wrapper">

    {{-- === Верхняя линия: дата и номер === --}}
    <table class="no-border header-line">
        <tr>
            <td class="cell left">Datums: {{ $invoice_date ?? now()->format('d.m.Y') }}</td>
            <td class="cell right">Faktūrrēķins Nr.: {{ $invoice_nr ?? '—' }}</td>
        </tr>
    </table>

    {{-- === Поставщик (Expeditor) / Плательщик (Payer) === --}}
    <table class="no-border mt-3">
        <tr>
            {{-- 🧾 Sender (Expeditor company) --}}
            <td class="w-50 cell">
                <div class="fw-bold">Piegādātājs</div>
                <div class="fw-bold">{{ $expeditor['name'] ?? '—' }}</div>
                <div>Vien. reģ. Nr.: {{ $expeditor['reg_nr'] ?? '—' }}</div>
                <div>Adrese: {{ $expeditor['address'] ?? '—' }}, {{ $expeditor['city'] ?? '' }}</div>
                <div>Valsts: {{ $expeditor['country'] ?? '' }}</div>
                <div>E-pasts: {{ $expeditor['email'] ?? '' }}</div>
                <div>Tālrunis: {{ $expeditor['phone'] ?? '' }}</div>
            </td>

            {{-- 💳 Payer --}}
            <td class="w-50 cell">
                <div class="fw-bold">Maksātājs ({{ $payer['label'] ?? '' }})</div>
                <div class="fw-bold">{{ $payer['name'] ?? '—' }}</div>
                <div>Vien. reģ. Nr.: {{ $payer['reg_nr'] ?? '—' }}</div>
                <div>Adrese: {{ $payer['address'] ?? '—' }}</div>
                <div>{{ $payer['city'] ?? '' }}, {{ $payer['country'] ?? '' }}</div>
            </td>
        </tr>
    </table>

    {{-- === Таблица с датами === --}}
    <table class="table-border mt-5">
        <tr>
            <th class="cell w-33 text-center">Pasūtījuma Nr.</th>
            <th class="cell w-33 text-center">Iekraušanas datums</th>
            <th class="cell w-33 text-center">Izlādes datums</th>
        </tr>
        <tr>
            <td class="cell text-center">{{ $trip->order_nr ?? '—' }}</td>
            <td class="cell text-center">
                {{ optional($trip->cargos->first())->loading_date ? \Carbon\Carbon::parse($trip->cargos->first()->loading_date)->format('d.m.Y') : '—' }}
            </td>
            <td class="cell text-center">
                {{ optional($trip->cargos->first())->unloading_date ? \Carbon\Carbon::parse($trip->cargos->first()->unloading_date)->format('d.m.Y') : '—' }}
            </td>
        </tr>
    </table>

    {{-- === Основная таблица === --}}
    <table class="table-border mt-3">
        <tr>
            <th class="cell w-10 text-center">Nr.</th>
            <th class="cell">Nosaukums</th>
            <th class="cell w-15 text-center">Mērvienība</th>
            <th class="cell w-12 text-center">Daudzums</th>
            <th class="cell w-18 text-center">Cena</th>
            <th class="cell w-18 text-center">Summa</th>
        </tr>

        @php $i = 1; @endphp
        @foreach ($cargos as $cg)
            <tr>
                <td class="cell text-center">{{ $i++ }}</td>
                <td class="cell">
                    Transporta pakalpojumi <br> {{ $loading_country_iso ?? '—' }}-{{ $unloading_country_iso ?? '—' }}
                    <span style="display:block; margin-left:10mm;">
                        a/m {{ $trip->truck->brand ?? '' }} {{ $trip->truck->plate ?? '' }}
                        / {{ $trip->trailer->brand ?? '' }} {{ $trip->trailer->plate ?? '' }}
                    </span>
                    <span style="display:block; margin-left:10mm;">CMR Nr. {{ $cg->cmr_nr ?? $trip->order_nr ?? '—' }}</span>
                </td>
                <td class="cell text-center">Reiss</td>
                <td class="cell text-center">1.00</td>
                <td class="cell amount">{{ number_format($cg->price, 2, '.', ' ') }}</td>
                <td class="cell amount">{{ number_format($cg->price, 2, '.', ' ') }}</td>
            </tr>
        @endforeach

        {{-- === Итоги === --}}
        <tr>
            <td class="cell" colspan="4" rowspan="3"></td>
            <td class="cell fw-bold">Kopā:</td>
            <td class="cell amount fw-bold">{{ number_format($subtotal ?? 0, 2, '.', ' ') }}</td>
        </tr>
        <tr>
            <td class="cell">PVN {{ $cargos->first()->tax_percent ?? 21 }}%</td>
            <td class="cell amount">{{ number_format($vat ?? 0, 2, '.', ' ') }}</td>
        </tr>
        <tr>
            <td class="cell fw-bold">Summa apmaksai</td>
            <td class="cell amount fw-bold">{{ number_format($total ?? 0, 2, '.', ' ') }}</td>
        </tr>
    </table>

    {{-- === Сумма словами === --}}
   <div class="mt-3">
    <span class="fw-bold">Summa vārdiem:</span>
    {{ $sum_in_words ?? '' }}
</div>
    {{-- === Подписи === --}}
    <div class="signature-block">
        <div class="signature-left">
            <div class="small">Reķins sagatavots elektroniski un derīgs bez paraksta</div>
           
        </div>
        
    </div>

    {{-- === Проводка (D/K) === --}}
    <!-- <div class="footer-entry">
        D 2310 K 6111&nbsp;&nbsp;&nbsp;&nbsp;{{ number_format($total ?? 0, 2, '.', ' ') }}
    </div> -->

</div>
</body>
</html>

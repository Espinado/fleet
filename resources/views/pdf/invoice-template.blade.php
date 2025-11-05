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

    /* === Реальные поля для DomPDF (с запасом) === */
    @page {
        size: A4;
        margin-top: 28mm;     /* ↑ верх чуть больше */
        margin-bottom: 25mm;
        margin-left: 24mm;    /* ← левое поле увеличено */
        margin-right: 20mm;
    }

    /* === Безопасная рабочая зона === */
    .wrapper {
        width: 96%;           /* немного уже, чтобы не тянуло за край */
        margin: 0 auto;
        padding: 2mm 6mm;     /* внутренний буфер */
        box-sizing: border-box;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .cell {
        padding: 2.3mm;
        vertical-align: top;
        word-wrap: break-word;
    }

    .table-border,
    .table-border td,
    .table-border th {
        border: 0.8px solid #000;
    }

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

    /* === Подписи === */
    .signature-block {
        margin-top: 28mm;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
    }

    .signature-left {
        width: 50%;
        text-align: left;
        font-size: 11px;
    }

    .signature-right {
        width: 50%;
        text-align: right;
        font-size: 11px;
    }

    .sigline {
        margin-top: 18mm;
        border-top: 1px solid #000;
        width: 60%;
    }

    .footer-entry {
        margin-top: 12mm;
        font-size: 11px;
    }
</style>

</head>
<body>
<div class="wrapper">

    {{-- === Верхняя линия: дата и номер === --}}
    <table class="no-border header-line">
        <tr>
            <td class="cell left">Datums: {{ $invoice['date'] ?? '31.10.2025' }}</td>
            <td class="cell right">Faktūrrēķins Nr.: {{ $invoice['number'] ?? 'MB486/25' }}</td>
        </tr>
    </table>

    {{-- === Поставщик / Плательщик === --}}
    <table class="no-border mt-3">
        <tr>
            <td class="w-50 cell">
                <div class="fw-bold">Piegādātājs</div>
                <div class="fw-bold">{{ $supplier['name'] ?? 'SIA „PADEKS”' }}</div>
                <div>Vien. reģ. Nr.: {{ $supplier['reg'] ?? 'LV40003385347' }}</div>
                <div>Jur. Adrese: {{ $supplier['address'] ?? 'Valērijas Seiles iela 7A-3, Rīga, LV-1019' }}</div>
                <div>Banka: {{ $supplier['bank'] ?? 'AS SWEDBANK' }}</div>
                <div>Bankas konts: {{ $supplier['swift'] ?? 'HABALV22' }}</div>
                <div>Konta Nr.: {{ $supplier['iban'] ?? 'LV40HABA0551045751481' }}</div>
            </td>
            <td class="w-50 cell">
                <div class="fw-bold">Maksātājs</div>
                <div class="fw-bold">{{ $buyer['name'] ?? 'Tagrolat SIA' }}</div>
                <div>Vien. reģ. Nr.: {{ $buyer['reg'] ?? 'LV40203201534' }}</div>
                <div>Jur. Adrese: {{ $buyer['address'] ?? 'Miera iela 32, LV2169, Salaspils' }}</div>
            </td>
        </tr>
    </table>

    {{-- === Маленькая таблица с датами === --}}
    <table class="table-border mt-5">
        <tr>
            <th class="cell w-33 text-center">Ligums Nr.</th>
            <th class="cell w-33 text-center">Pakalpojuma sniegšanas datums</th>
            <th class="cell w-33 text-center">Apmaksas termiņš</th>
        </tr>
        <tr>
            <td class="cell text-center">{{ $invoice['contract'] ?? '2025.10.31' }}</td>
            <td class="cell text-center">{{ $invoice['service_date'] ?? '31.10.2025' }}</td>
            <td class="cell text-center">{{ $invoice['due_date'] ?? '07.11.2025' }}</td>
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

        <tr>
            <td class="cell text-center">1</td>
            <td class="cell">
                Transporta pakalpojumi (LV–GR)<br>
                <span style="display:block; margin-left:10mm;">a/m MB7803/N2938</span>
                <span style="display:block; margin-left:10mm;">CMR Nr. (31.10.2025)</span>
            </td>
            <td class="cell text-center">Reiss</td>
            <td class="cell text-center">1.00</td>
            <td class="cell amount">3300.00</td>
            <td class="cell amount">3300.00</td>
        </tr>

        <tr>
            <td class="cell" colspan="4" rowspan="3"></td>
            <td class="cell fw-bold">Kopā:</td>
            <td class="cell amount fw-bold">3300.00</td>
        </tr>
        <tr>
            <td class="cell">PVN 21%</td>
            <td class="cell amount">693.00</td>
        </tr>
        <tr>
            <td class="cell fw-bold">Summa apmaksai</td>
            <td class="cell amount fw-bold">3993.00</td>
        </tr>
    </table>

    {{-- === Сумма словами === --}}
    <div class="mt-3">
        <span class="fw-bold">Summa vārdiem:</span>
        Trīs tūkstoši deviņsimt deviņdesmit trīs EUR, 00 centi
    </div>

    {{-- === Подписи === --}}
    <div class="signature-block">
        <div class="signature-left">
            <div class="small">Valdes loceklis</div>
            <div class="sigline"></div>
        </div>
        <div class="signature-right">
            <div>Jevgēnija Mikulko</div>
        </div>
    </div>

    {{-- === Проводка === --}}
    <div class="footer-entry">
        D 2310 K 6111&nbsp;&nbsp;&nbsp;&nbsp;3993.00
    </div>

</div>
</body>
</html>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Transporta pasūtījuma līgums</title>
    <style>
        @font-face {
            font-family: 'DejaVu Sans';
            src: url('{{ public_path('fonts/DejaVuSans.ttf') }}') format('truetype');
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #111;
            margin: 40px 45px;
            position: relative;
        }

        /* 💧 Dinamisks ūdenszīmes teksts */
        .watermark {
            position: fixed;
            top: 40%;
            left: 25%;
            width: 50%;
            text-align: center;
            opacity: 0.08;
            font-size: 80px;
            transform: rotate(-30deg);
            z-index: -1;
            color: #000;
            word-break: break-word;
        }

        h1 {
            text-align: center;
            text-transform: uppercase;
            font-size: 18px;
            margin-top: 10px;
            margin-bottom: 25px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        td {
            vertical-align: top;
            padding: 4px 6px;
        }

        .bordered td {
            border: 1px solid #666;
        }

        .section-title {
            font-weight: bold;
            background: #f3f3f3;
            padding: 4px;
            border: 1px solid #999;
        }

        .signature-block {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }

        .signature {
            width: 45%;
            text-align: center;
        }

        .small {
            font-size: 10px;
            color: #555;
        }

        .info-right {
            position: absolute;
            right: 45px;
            top: 40px;
            font-size: 10px;
            text-align: right;
            line-height: 1.4;
        }
    </style>
</head>
<body>

{{-- 💧 Dinamisks ūdenszīmes teksts (nosūtītāja nosaukums) --}}
<div class="watermark">
    {{ strtoupper($sender['name'] ?? '—') }}
</div>

{{-- === Labajā augšā informācija par pārvadātāju === --}}
<!-- <div class="info-right">
    <b>{{ $carrier['name'] ?? '—' }}</b><br>
    Reg. Nr: {{ $carrier['reg_nr'] ?? '—' }}<br>
    {{ $carrier['address'] ?? '—' }}<br>
    {{ $carrier['city'] ?? '—' }}, {{ $carrier['country'] ?? '—' }}<br>
</div> -->

<h1>Transporta pasūtījuma līgums Nr {{$order_nr}}</h1>

<table>
    <tr>
        <td width="50%">
            <b>Pasūtītājs:</b><br>
            {{ $customer['name'] ?? '—' }}<br>
            Reģ. Nr: {{ $sender['reg_nr'] ?? '—' }}<br>
            {{ $customer['address'] ?? '' }}<br>
            {{ $customer['city'] ?? '' }}, {{ $sender['country'] ?? '' }}
        </td>
        <td width="50%">
            <b>Pārvadātājs:</b><br>
            {{ $carrier['name'] ?? '—' }}<br>
            Reģ. Nr: {{ $carrier['reg_nr'] ?? '—' }}<br>
            {{ $carrier['address'] ?? '' }}<br>
            {{ $carrier['city'] ?? '' }}, {{ $carrier['country'] ?? '' }}
        </td>
    </tr>
</table>

<table>
    <tr>
        <td width="50%">
             <b>Iekraušanas vieta:</b><br>
            {{ $loading_place ?? '—' }}<br>
            {{ $loading_address ?? '' }}<br><br>

        </td>
        <td width="50%">
          
            <b>Izlādes vieta:</b><br>
            {{ $unloading_place ?? '—' }}<br>
            {{ $unloading_address ?? '' }}
        </td>
    </tr>
</table>

{{-- 💶 Frakts un apmaksa --}}
<table class="bordered">
    <tr>
        <td class="section-title" colspan="2">Frakts un apmaksa</td>
    </tr>
    <tr>
        <td width="50%">
            <b>Frakts:</b>
           {{ number_format($total_price_with_tax ?? 0, 2, '.', ' ') }} {{ $cargo->currency ?? 'EUR' }} 
        </td>
        <td width="50%">
            <b>Maksājuma termiņš:</b>
           @if(!empty($payment_terms))
    {{ \Carbon\Carbon::parse($payment_terms)->format('d.m.Y') }}
@else
    30 dienas pēc CMR un rēķina saņemšanas
@endif
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <b>Autotransports:</b>
             {{ $carrier['truck'] ?? '—' }} ({{ $carrier['truck_plate'] ?? '—' }}), {{ $carrier['trailer'] ?? '—' }} ({{ $carrier['trailer_plate'] ?? '—' }})<br>
        </td>
    </tr>
</table>

{{-- 📋 Pakalpojuma nosacījumi --}}
<table class="bordered">
    <tr>
        <td class="section-title" colspan="2">Pakalpojuma nosacījumi</td>
    </tr>
    <tr>
        <td colspan="2">
            Pārvadātājs apņemas veikt kravas pārvadājumu saskaņā ar šajā līgumā noteiktajiem nosacījumiem un
            starptautiskajiem CMR konvencijas noteikumiem. Krava tiek pieņemta un nodota ar atbilstošiem pavaddokumentiem (CMR, rēķins u.c.).<br><br>
            Pārvadājuma izpildes termiņš: līdz {{ $unloading_place ?? '—' }} bez liekas kavēšanās.<br><br>
            Par katru kavējuma dienu pasūtītājam ir tiesības piemērot līgumsodu 100 EUR apmērā.
            Ja pārvadājums netiek uzsākts pēc līguma noslēgšanas, pasūtītājam ir tiesības uz 20% līgumsodu no pārvadājuma summas.
        </td>
    </tr>
</table>

{{-- 💳 Samaksas kārtība --}}
<table class="bordered">
    <tr>
        <td class="section-title" colspan="2">Samaksas kārtība</td>
    </tr>
    <tr>
        <td colspan="2">
            Samaksa tiek veikta 30 dienu laikā pēc CMR un rēķina saņemšanas, uz pasūtītāja norādīto bankas kontu, ja vien nav norunāts citādi.
        </td>
    </tr>
</table>

{{-- 📌 Papildu noteikumi --}}
<table class="bordered">
    <tr>
        <td class="section-title" colspan="2">Papildu noteikumi</td>
    </tr>
    <tr>
        <td colspan="2">
            Pārvadātājam ir pienākums nekavējoties informēt pasūtītāju par jebkādām novirzēm, bojājumiem vai aizturēšanu
            pārvadājuma laikā. Visi strīdi tiek risināti saskaņā ar Latvijas Republikas likumdošanu.
        </td>
    </tr>
</table>

{{-- ✍️ Paraksti --}}
<table style="width:100%; margin-top:60px; font-size:12px; border-collapse:collapse; table-layout:fixed;">
    <tr>
        <!-- 🟦 Левая колонка: Заказчик -->
        <td style="width:50%; text-align:center; vertical-align:bottom; padding:0 10px;">
            <div style="border-bottom:1px solid #000; width:80%; margin:0 auto 5px auto; height:20px;"></div>
            <b>Pasūtītājs</b><br>
            <span style="font-size:11px; color:#333;">
                {{ $customer['name'] ?? '—' }}
            </span>
        </td>

        <!-- 🟩 Правая колонка: Перевозчик -->
        <td style="width:50%; text-align:center; vertical-align:bottom; padding:0 10px;">
            <div style="border-bottom:1px solid #000; width:80%; margin:0 auto 5px auto; height:20px;"></div>
            <b>Pārvadātājs</b><br>
            <span style="font-size:11px; color:#333;">
                {{ $carrier['name'] ?? '—' }}
            </span>
        </td>
    </tr>
</table>

<p style="font-size:10px; color:#555; margin-top:20px;">
    Datums: {{ $date ?? now()->format('d.m.Y') }}
</p>

</body>
</html>

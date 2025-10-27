<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>CMR Form</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .cmr {
            border: 1px solid #000;
            padding: 10px;
            width: 100%;
        }
        .header {
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
            margin-bottom: 8px;
        }
        .section {
            display: flex;
            border: 1px solid #000;
            border-top: none;
        }
        .cell {
            flex: 1;
            border-right: 1px solid #000;
            padding: 5px;
            min-height: 70px;
            vertical-align: top;
        }
        .cell:last-child { border-right: none; }
        .label {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 3px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            font-size: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 3px;
            text-align: center;
        }
        th {
            background: #f3f3f3;
            font-weight: bold;
        }
        .footer {
            display: flex;
            border: 1px solid #000;
            border-top: none;
        }
        .block {
            flex: 1;
            border-right: 1px solid #000;
            padding: 5px;
            min-height: 80px;
        }
        .block:last-child { border-right: none; }
        .small {
            font-size: 9px;
            color: #555;
        }
        .center {
            text-align: center;
        }
    </style>
</head>
<body>
<div class="cmr">
    <div class="header">
        МЕЖДУНАРОДНАЯ ТОВАРНО-ТРАНСПОРТНАЯ НАКЛАДНАЯ (CMR)
    </div>

    {{-- 1–2 Отправитель и Получатель --}}
    <div class="section" style="border-top:1px solid #000;">
        <div class="cell">
            <div class="label">1. Отправитель</div>
            @if(!empty($sender))
                <strong>{{ $sender['name'] ?? '—' }}</strong><br>
                Reg. Nr: {{ $sender['reg_nr'] ?? '—' }}<br>
                {{ $sender['address'] ?? '' }}, {{ $sender['city'] ?? '' }}<br>
                {{ $sender['country'] ?? '' }}<br>
                📧 {{ $sender['email'] ?? '' }}<br>
                ☎ {{ $sender['phone'] ?? '' }}
            @else
                —
            @endif
        </div>

        <div class="cell">
            <div class="label">2. Получатель</div>
            @if(!empty($receiver))
                <strong>{{ $receiver['name'] ?? '—' }}</strong><br>
                Reg. Nr: {{ $receiver['reg_nr'] ?? '—' }}<br>
                {{ $receiver['address'] ?? '' }}, {{ $receiver['city'] ?? '' }}<br>
                {{ $receiver['country'] ?? '' }}<br>
                📧 {{ $receiver['email'] ?? '' }}<br>
                ☎ {{ $receiver['phone'] ?? '' }}
            @else
                —
            @endif
        </div>
    </div>

    {{-- 3–4 Места --}}
    <div class="section">
        <div class="cell">
            <div class="label">3. Место погрузки</div>
            {{ $loading_place ?? '—' }}
        </div>
        <div class="cell">
            <div class="label">4. Место разгрузки</div>
            {{ $unloading_place ?? '—' }}
        </div>
    </div>

    {{-- 5 + 16 --}}
    <div class="section">
        <div class="cell">
            <div class="label">5. Приложенные документы</div>
            {{ $documents ?? '—' }}
        </div>
        <div class="cell">
            <div class="label">16. Перевозчик / Экспедитор</div>
            @if(!empty($carrier))
                <strong>{{ $carrier['name'] ?? '—' }}</strong><br>
                Reg. Nr: {{ $carrier['reg_nr'] ?? '—' }}<br>
                {{ $carrier['address'] ?? '' }}, {{ $carrier['city'] ?? '' }}<br>
                {{ $carrier['country'] ?? '' }}<br>
                📧 {{ $carrier['email'] ?? '' }}<br>
                ☎ {{ $carrier['phone'] ?? '' }}
            @else
                —
            @endif
        </div>
    </div>

    {{-- 6–12 Таблица груза --}}
    <table>
        <thead>
            <tr>
                <th>6. Знаки и номера</th>
                <th>7. Кол-во мест</th>
                <th>8. Род упаковки</th>
                <th>9. Наименование груза</th>
                <th>10. Стат. №</th>
                <th>11. Вес брутто, кг</th>
                <th>12. Объём, м³</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items ?? [] as $item)
                <tr>
                    <td>{{ $item['marks'] ?? '' }}</td>
                    <td>{{ $item['qty'] ?? '' }}</td>
                    <td>{{ $item['pack'] ?? '' }}</td>
                    <td style="text-align:left;">{{ $item['desc'] ?? '' }}</td>
                    <td>{{ $item['stat'] ?? '' }}</td>
                    <td>{{ $item['gross'] ?? '' }}</td>
                    <td>{{ $item['volume'] ?? '' }}</td>
                </tr>
            @empty
                @for($i = 0; $i < 5; $i++)
                    <tr><td colspan="7">&nbsp;</td></tr>
                @endfor
            @endforelse
        </tbody>
    </table>

    {{-- 13–19 Указания и платежи --}}
    <div class="section">
        <div class="cell">
            <div class="label">13. Указания отправителя</div>
            —
        </div>
        <div class="cell">
            <div class="label">19. Платежи</div>
            —
        </div>
    </div>

    {{-- 22–24 Подписи сторон --}}
    <div class="footer">
        <div class="block">
            <div class="label">22. Отправитель</div>
            <div class="small">Подпись / печать</div><br><br><br>
        </div>
        <div class="block">
            <div class="label">23. Перевозчик</div>
            <div class="small">Подпись / печать</div><br><br><br>
        </div>
        <div class="block">
            <div class="label">24. Получатель</div>
            <div class="small">Подпись / печать</div><br><br><br>
        </div>
    </div>

    <p class="center small" style="margin-top:6px;">
        * Данный документ создан автоматически системой Fleet Manager ({{ date('d.m.Y') }})
    </p>
</div>
</body>
</html>

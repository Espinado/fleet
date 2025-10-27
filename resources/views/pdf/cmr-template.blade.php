<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>CMR Form</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9.5px;
            line-height: 1.25;
            color: #000;
            margin: 10px;
        }
        .cmr {
            border: 1px solid #000;
            padding: 6px;
        }
        .header {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            border-bottom: 1px solid #000;
            padding-bottom: 4px;
            margin-bottom: 4px;
        }
        .section {
            display: flex;
            border: 1px solid #000;
            border-top: none;
        }
        .cell {
            flex: 1;
            border-right: 1px solid #000;
            padding: 4px;
            min-height: 55px;
            vertical-align: top;
        }
        .cell:last-child { border-right: none; }
        .label {
            font-weight: bold;
            font-size: 9px;
            margin-bottom: 2px;
            border-bottom: 0.5px solid #999;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            font-size: 9px;
            margin-top: -1px;
        }
        th, td {
            border: 1px solid #000;
            padding: 2px 3px;
            text-align: center;
        }
        th {
            background: #f7f7f7;
            font-weight: bold;
            font-size: 8.5px;
        }
        td {
            line-height: 1.2;
            vertical-align: top;
        }
        .footer {
            display: flex;
            border: 1px solid #000;
            border-top: none;
        }
        .block {
            flex: 1;
            border-right: 1px solid #000;
            padding: 4px;
            min-height: 55px;
        }
        .block:last-child { border-right: none; }
        .small {
            font-size: 8px;
            color: #555;
        }
        .center {
            text-align: center;
        }
        .meta {
            font-size: 8px;
            text-align: center;
            margin-top: 3px;
            color: #666;
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
            @if(!empty($sender) && is_array($sender))
                <strong>{{ (string)($sender['name'] ?? '—') }}</strong><br>
                Reg. Nr: {{ (string)($sender['reg_nr'] ?? '—') }}<br>
                {{ (string)($sender['address'] ?? '') }}, {{ (string)($sender['city'] ?? '') }}<br>
                {{ (string)($sender['country'] ?? '') }}
            @else
                {{ (string)($sender ?? '—') }}
            @endif
        </div>

        <div class="cell">
            <div class="label">2. Получатель</div>
            @if(!empty($receiver) && is_array($receiver))
                <strong>{{ (string)($receiver['name'] ?? '—') }}</strong><br>
                Reg. Nr: {{ (string)($receiver['reg_nr'] ?? '—') }}<br>
                {{ (string)($receiver['address'] ?? '') }}, {{ (string)($receiver['city'] ?? '') }}<br>
                {{ (string)($receiver['country'] ?? '') }}
            @else
                {{ (string)($receiver ?? '—') }}
            @endif
        </div>
    </div>

    {{-- 3–4 Места --}}
    <div class="section">
        <div class="cell">
            <div class="label">3. Место погрузки</div>
            {{ (string)($loading_place ?? '—') }}
        </div>
        <div class="cell">
            <div class="label">4. Место разгрузки</div>
            {{ (string)($unloading_place ?? '—') }}
        </div>
    </div>

    {{-- 5 + 16 --}}
    <div class="section">
        <div class="cell">
            <div class="label">5. Приложенные документы</div>
            {{ (string)($documents ?? '—') }}
        </div>
        <div class="cell">
            <div class="label">16. Перевозчик / Экспедитор</div>
            @if(!empty($carrier) && is_array($carrier))
                <strong>{{ (string)($carrier['name'] ?? '—') }}</strong><br>
                Reg. Nr: {{ (string)($carrier['reg_nr'] ?? '—') }}<br>
                {{ (string)($carrier['address'] ?? '') }}, {{ (string)($carrier['city'] ?? '') }}<br>
                {{ (string)($carrier['country'] ?? '') }}
            @else
                {{ (string)($carrier ?? '—') }}
            @endif
        </div>
    </div>

    {{-- 6–12 Таблица груза --}}
    <table>
        <thead>
        <tr>
            <th>6. Знаки</th>
            <th>7. Мест</th>
            <th>8. Упак.</th>
            <th>9. Наименование</th>
            <th>10. Стат.</th>
            <th>11. Брутто, кг</th>
            <th>12. Объём, м³</th>
        </tr>
        </thead>
        <tbody>
        @forelse($items ?? [] as $item)
            <tr>
                <td>{{ (string)($item['marks'] ?? '') }}</td>
                <td>{{ (string)($item['qty'] ?? '') }}</td>
                <td>{{ (string)($item['pack'] ?? '') }}</td>
                <td style="text-align:left;">{{ (string)($item['desc'] ?? '') }}</td>
                <td>{{ (string)($item['stat'] ?? '') }}</td>
                <td>{{ (string)($item['gross'] ?? '') }}</td>
                <td>{{ (string)($item['volume'] ?? '') }}</td>
            </tr>
        @empty
            @for($i = 0; $i < 3; $i++)
                <tr><td colspan="7">&nbsp;</td></tr>
            @endfor
        @endforelse
        </tbody>
    </table>

    {{-- 13–19 --}}
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

    {{-- 22–24 Подписи --}}
    <div class="footer">
        <div class="block">
            <div class="label">22. Отправитель</div>
            <div class="small">Подпись / печать</div>
        </div>
        <div class="block">
            <div class="label">23. Перевозчик</div>
            <div class="small">Подпись / печать</div>
        </div>
        <div class="block">
            <div class="label">24. Получатель</div>
            <div class="small">Подпись / печать</div>
        </div>
    </div>

    <p class="meta">
        * Автоматически создано системой Fleet Manager ({{ date('d.m.Y') }})
    </p>
</div>
</body>
</html>

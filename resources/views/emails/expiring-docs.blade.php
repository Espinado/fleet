@component('mail::message')
@php use Illuminate\Support\Str; @endphp
# ⚠️ Документы, истекающие в ближайшие 30 дней

Здравствуйте!

Ниже приведён список документов, срок действия которых истекает в течение **30 дней**.
Пожалуйста, обновите их вовремя, чтобы избежать штрафов или простоев.

---

## 📊 Сводка по объектам

@php
    $grouped = collect($items)->groupBy('name');
@endphp

<table style="width:100%; border-collapse: collapse; margin-bottom: 20px;">
    <thead>
        <tr>
            <th style="text-align:left; border-bottom:1px solid #ddd; padding:5px;">Объект</th>
            <th style="text-align:center; border-bottom:1px solid #ddd; padding:5px;">Кол-во документов</th>
        </tr>
    </thead>
    <tbody>
    @foreach ($grouped as $name => $docs)
        <tr>
            <td style="padding:5px;">{{ $name }}</td>
            <td style="text-align:center; padding:5px;">{{ $docs->count() }} {{ Str::plural('документ', $docs->count()) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

---

## 📋 Подробный список

<table style="width:100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th style="border-bottom:1px solid #ddd; padding:5px;">Тип</th>
            <th style="border-bottom:1px solid #ddd; padding:5px;">Объект</th>
            <th style="border-bottom:1px solid #ddd; padding:5px;">Документ</th>
            <th style="border-bottom:1px solid #ddd; padding:5px;">Срок действия</th>
            <th style="border-bottom:1px solid #ddd; padding:5px; text-align:center;">Осталось</th>
        </tr>
    </thead>
    <tbody>
    @foreach ($items as $it)
        @php
            if ($it->days_left < 0) {
                $color = '#FF4C4C'; // красный
                $text = "❌ Просрочен ".abs($it->days_left)." дн.";
            } elseif ($it->days_left <= 7) {
                $color = '#FF7F50'; // ярко-оранжевый
                $text = $it->days_left." дн.";
            } elseif ($it->days_left <= 14) {
                $color = '#FFA500'; // оранжевый
                $text = $it->days_left." дн.";
            } else {
                $color = '#4CAF50'; // зелёный
                $text = $it->days_left." дн.";
            }
        @endphp
        <tr>
            <td style="padding:5px;">{{ $it->type }}</td>
            <td style="padding:5px;">{{ $it->name }}</td>
            <td style="padding:5px;"><strong>{{ $it->document }}</strong></td>
            <td style="padding:5px;">{{ $it->expiry_date->format('d.m.Y') }}</td>
            <td style="padding:5px; text-align:center; background-color:{{ $color }}; color:#fff; font-weight:bold;">
                {{ $text }}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

---

<div style="margin-top:20px; padding:10px; background-color:#f0f0f0; border-left:4px solid #4CAF50;">
    🕐 Проверено {{ now()->format('d.m.Y H:i') }}
    Всего найдено: <strong>{{ count($items) }}</strong> документов.
</div>

С уважением,
**Arguss LV**
@endcomponent

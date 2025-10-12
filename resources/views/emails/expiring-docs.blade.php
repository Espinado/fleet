@component('mail::message')
# ⚠️ Документы, истекающие в ближайшие 30 дней

Здравствуйте!

Ниже приведён список документов, срок действия которых истекает в течение **30 дней**.
Пожалуйста, обновите их вовремя, чтобы избежать штрафов или простоев.

---

## 📊 Сводка по объектам

@php
    $grouped = collect($items)->groupBy('name');
@endphp

@component('mail::table')
| Объект | Кол-во документов |
|:-------|:----------------|
@foreach ($grouped as $name => $docs)
| {{ $name }} | {{ $docs->count() }} {{ Str::plural('документ', $docs->count()) }} |
@endforeach
@endcomponent

---

## 📋 Подробный список

@component('mail::table')
| Тип | Объект | Документ | Срок действия | Осталось |
|:----|:--------|:----------|:---------------|:-----------:|
@foreach ($items as $it)
| {{ $it->type }} | {{ $it->name }} | **{{ $it->document }}** | {{ $it->expiry_date->format('d.m.Y') }} |
@if ($it->days_left < 0)
❌ Просрочен {{ abs($it->days_left) }} дн.
@elseif ($it->days_left <= 7)
🟥 {{ $it->days_left }} дн.
@elseif ($it->days_left <= 14)
🟧 {{ $it->days_left }} дн.
@else
🟩 {{ $it->days_left }} дн.
@endif |
@endforeach
@endcomponent

---

@component('mail::panel')
🕐 Проверено {{ now()->format('d.m.Y H:i') }}
Всего найдено: **{{ count($items) }}** документов.
@endcomponent

С уважением,
**Arguss LV**
@endcomponent

@component('mail::message')
# Ошибка в приложении Fleet

На почту отправлено уведомление об ошибке для проверки администратором.

**Сообщение:** {{ $exception->getMessage() }}

**Файл:** {{ $exception->getFile() }} (строка {{ $exception->getLine() }})

**Класс:** {{ get_class($exception) }}

@if($exception instanceof \Illuminate\Validation\ValidationException)
**Ошибки валидации:**
@foreach($exception->errors() as $field => $messages)
- **{{ $field }}:** {{ implode(', ', $messages) }}
@endforeach
@endif

@if($exception->getPrevious())
**Предыдущее исключение:** {{ $exception->getPrevious()->getMessage() }}
@endif

<details>
<summary>Трассировка (stack trace)</summary>
<pre style="font-size:11px; white-space: pre-wrap; word-break: break-all;">{{ $exception->getTraceAsString() }}</pre>
</details>

Время: {{ now()->toDateTimeString() }}
@endcomponent

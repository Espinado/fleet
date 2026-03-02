## Fleet Manager — ключевые потоки

### 1. Создание рейса в админке

- **UI / маршруты**
  - Route: `GET /trips/create` → `App\Livewire\Trips\CreateTrip`.
  - Шаблон: `resources/views/livewire/trips/create-trip.blade.php`.
- **Основные шаги**
  1. Менеджер выбирает **expeditor** (`Company`) и банк из `banks_json`.
  2. Определяет **carrier**:
     - либо сам экспедитор (`carrier_company_id = expeditor_id`),
     - либо отдельная внутренняя компания (`carrier_company_select`),
     - либо **third party** (`__third_party__` → создаётся новая `Company` и `Truck`/`Trailer`).
  3. Выбирает **транспорт** (driver, truck, trailer) или задаёт данные третьей стороны.
  4. Описывает **steps** (маршрут):
     - тип `loading` / `unloading`,
     - страна, город, адрес, дата/время,
     - порядок.
  5. Создаёт один или несколько **cargos**:
     - клиент (customer), грузоотправитель, грузополучатель,
     - цена + налог (`price`, `tax_percent`),
     - связанные **items** (описание, упаковки, вес, объём и т.д.).
  6. Выбирает глобальные шаги погрузки/разгрузки (`trip_loading_step_ids`, `trip_unloading_step_ids`), которые потом связываются с каждым грузом.
  7. При **third party**:
     - создаются `Company` / `Truck` / `Trailer`,
     - в рейс автоматически добавляется `TripExpense` категории `other` (оплата субперевозчику).
- **Сохранение**
  - Компонент `CreateTrip::save()`:
    - нормализует числовые поля,
    - валидирует перевозчика/шаги/грузы,
    - создаёт `Trip`, `TripStep`, `TripCargo`, `TripCargoItem`,
    - при необходимости — third‑party сущности и расход.

### 2. Прохождение шагов рейса

- **UI / просмотр рейса**
  - Route: `GET /trips/{trip}` → `App\Livewire\Trips\ViewTrip`.
  - Шаблон: `resources/views/livewire/trips/view-trip.blade.php`.
  - Отображаются:
    - агрегированные веса/объёмы,
    - группы грузов по клиентам,
    - шаги маршрута с ролями `loading`/`unloading`.
- **Редактирование порядка шагов**
  - Внутри `view-trip` подключается `TripRouteEditor`:
    - Livewire: `App\Livewire\Trips\TripRouteEditor`.
    - Шаблон: `resources/views/livewire/trips/trip-route-editor.blade.php`.
  - `TripRouteEditor`:
    - грузит шаги `TripStep` рейса,
    - при необходимости авто‑сортирует через `TripStepSorter`,
    - позволяет перетаскивать шаги и сохраняет новый `order`.
- **Статусы шагов / события одометра**
  - Отдельные сервисы и модели (не детализировано здесь) пишут:
    - `TruckOdometerEvent` с `type = TYPE_STEP` и `step_status`,
    - записи в `TripStepStatus` enum,
    - что отражается в таблице событий/статистики.

### 3. Добавление расхода водителем (Driver PWA)

- **UI / маршруты**
  - PWA‑роуты: `routes/driver.php`.
  - Основные экраны:
    - `/driver/dashboard` → `DriverApp\Dashboard`.
    - `/driver/trip/{trip}` → `DriverApp\TripDetails` (включает расходы).
  - Компонент расходов:
    - Livewire: `App\Livewire\DriverApp\DriverTripExpenses`.
    - Шаблон: `resources/views/livewire/driver-app/driver-trip-expenses.blade.php`.
- **Форма расхода**
  - Поля: `category`, `description`, `amount`, `expense_date`, файл, `liters`, `manualOdometerKm`.
  - Бизнес‑правило:
    - `manualOdometerKm` обязателен **только** для категорий `FUEL` и `ADBLUE`.
    - `liters` обязателен для `FUEL`, `ADBLUE`, `WASHER_FLUID`.
    - Для других категорий одометр и литры запрещены (`prohibited_unless` в валидации).
- **Сохранение**
  - `DriverTripExpenses::saveExpense()`:
    - создаёт `TripExpense` с нужной категорией, суммой, литрами и (опционально) одометром.
    - если категория "одометр‑обязательная":
      - рассчитывает `occurred_at` (обычно дата расхода),
      - пытается найти существующий `TruckOdometerEvent` с тем же `truck_id`/`driver_id`/`odometer_km` за последние 2 минуты,
      - иначе создаёт новый `TruckOdometerEvent` (TYPE_EXPENSE) и связывает его с расходом через `truck_odometer_event_id`.

### 4. Синхронизация TripExpense ↔ TruckOdometerEvent

- **Команда**
  - `php artisan expenses:sync-odometer-events [--apply]`
  - Класс: `App\Console\Commands\SyncTripExpensesWithOdometerEvents`.
- **Цель**
  - Массово пройтись по существующим `TripExpense` и убедиться, что:
    - для расходов с обязательным одометром (`FUEL`, `ADBLUE`) есть корректный `TruckOdometerEvent(TYPE_EXPENSE)`,
    - связь `trip_expense_id` и `truck_odometer_event_id` согласована.
- **Режимы**
  - Без `--apply`: **dry‑run** — никакие изменения не пишутся, только логируются.
  - С `--apply`: реальные обновления/создание событий.
- **Алгоритм (упрощённо)**
  1. Итерация по всем `TripExpense` с `trip_id` не `NULL`.
  2. Если категория **не требует одометр** → `skipped_not_required`.
  3. Если категория требует одометр, но `odometer_km == null` → `skipped_no_odometer`.
  4. Определяется источник одометра (`manual`/`can`/`mileage`).
  5. Пытается найти существующий `TruckOdometerEvent(TYPE_EXPENSE)` по:
     - `truck_odometer_event_id`, либо
     - `trip_expense_id`.
  6. Если найдено полностью совпадающее событие (trip/expense/odometer/source) → `noop_already_synced`.
  7. Иначе:
     - в режиме `--apply` вызывает `ExpenseEventService::record(...)`, который:
       - создаёт/обновляет `TruckOdometerEvent(TYPE_EXPENSE)`,
       - обновляет `TripExpense::truck_odometer_event_id` и snapshot одометра.
     - в dry‑run просто пишет в лог, какое действие было бы выполнено.
  8. В конце выводится статистика:
     - total, created, updated,
     - skipped_not_required, skipped_no_odometer, noop_already_synced, errors.

### 5. Логика departure/return и VehicleRun

- **Сущности и сервисы**
  - `VehicleRun` — сущность заезда тягача (см. `app/Models/VehicleRun.php`).
  - Сервисы одометра:
    - `App\Services\Services\Odometer\VehicleRunService` — открытие/закрытие заезда, работа со статусом `VehicleRun`.
    - `App\Services\Services\Odometer\GarageDepartureService` — удобные операции вокруг выезда/возврата.
    - `App\Models\OdometerEvent` — сырой поток данных от Mapon / CAN.
    - `App\Services\Services\Odometer\MaponOdometerFetcher` — интеграция с Mapon.
- **Поток (концептуально)**
  1. При выезде тягача создаётся/открывается `VehicleRun`:
     - фиксируются стартовые `start_can_odom_km`, `start_engine_hours`.
     - в `TruckOdometerEvent` пишется событие `TYPE_DEPARTURE` (через `OdometerEventType::RUN_START` в статистике).
  2. В процессе рейса:
     - по шагам `TripStep` и событиям Mapon/CAN могут создаваться дополнительные `TruckOdometerEvent` (TYPE_STEP).
  3. При возвращении:
     - `VehicleRun` закрывается,
     - проставляются `end_can_odom_km`, `end_engine_hours`, `status`, `close_reason`.
     - создаётся `TruckOdometerEvent` с `TYPE_RETURN` (в статистике — `Run ended`).


## Fleet Manager — доменная модель

### Компании и схема владения

- **Company**
  - Используется для экспедитора, перевозчика и третьих сторон.
  - Важные поля: `type` (`carrier`, `forwarder`, `mixed`, `expeditor`), `is_third_party`, `is_active`.
  - **Expeditor snapshot** пишется прямо в `trips.*` (имя, рег. номер, контакты, банковские реквизиты).
- **Мульти‑компания**
  - Один и тот же код работает для Lakna / Padex / Expeditor.
  - В `Trip::booted()` есть global scope, который ограничивает видимость рейсов по:
    - роли пользователя (`admin` видит всё),
    - `driver_id` (для водителей),
    - `carrier_company_id` (для других ролей).

### Trips, Cargos, Items

- **Trip** (`app/Models/Trip.php`)
  - Описывает рейс: транспорт, даты, валюту, статус, заметки.
  - Ключевые поля:
    - `driver_id`, `truck_id`, `trailer_id`, `vehicle_run_id`.
    - `start_date`, `end_date`, `started_at`, `ended_at`.
    - `currency` (практически всегда `EUR`).
    - `status` (`TripStatus` enum).
    - `cont_nr`, `seal_nr` для контейнерных рейсов.
    - `carrier_company_id`, `expeditor_id` и snapshot экспедитора.
  - Связи:
    - `driver()`, `truck()`, `trailer()`.
    - `steps()` → коллекция `TripStep` в порядке выполнения.
    - `cargos()` → коллекция `TripCargo`.
    - `expenses()` → коллекция `TripExpense`.
    - `odometerEvents()` → события одометра по рейсу (`TruckOdometerEvent`).
    - `vehicleRun()` → `VehicleRun` (CAN‑одометр и ресурсы по поездке тягача).

- **TripCargo** (`app/Models/TripCargo.php`)
  - Груз (или партия) внутри рейса.
  - Ключевые поля:
    - Клиентские связи: `customer_id`, `shipper_id`, `consignee_id`.
    - Документы: `order_file`, `cmr_file`, `inv_file` и связанные номера / даты.
    - Стоимость фрахта: `price`, `tax_percent`, `total_tax_amount`, `price_with_tax`, `currency`.
    - Дополнительно: `commercial_invoice_nr`, `commercial_invoice_amount`.
  - Связи:
    - `trip()` → родительский `Trip`.
    - `customer()`, `shipper()`, `consignee()` → `Client`.
    - `items()` → коллекция `TripCargoItem`.
    - `steps()` → many‑to‑many `TripStep` через pivot `trip_cargo_step` с ролью `loading`/`unloading`.
    - `invoice()` → связанный `Invoice` (когда фрахт выставлен).

- **TripCargoItem** (`app/Models/TripCargoItem.php`)
  - Строчка груза (товарная позиция) внутри `TripCargo`.
  - Ключевые поля:
    - Описание: `description`, `customs_code`.
    - Количества: `packages`, `pallets`, `units`.
    - Масса/объём: `net_weight`, `gross_weight`, `tonnes`, `volume`, `loading_meters`.
    - Опции: `hazmat`, `temperature`, `stackable`, `instructions`, `remarks`.
    - (опционально) Цена: `price`, `tax_percent`, `tax_amount`, `price_with_tax`.

### Оdometer, расходы и VehicleRun

- **TripExpense** (`app/Models/TripExpense.php`)
  - Основной источник расходов по рейсу (как для админки, так и для PWA водителя).
  - Ключевые поля:
    - `trip_id`, `supplier_company_id`.
    - `category` (`TripExpenseCategory` enum).
    - `description`, `amount`, `currency`, `file_path`, `expense_date`, `created_by`.
    - `liters` (для топлива/AdBlue/washer_fluid).
    - `odometer_km`, `odometer_source` (snapshot пробега на момент расхода).
    - `truck_odometer_event_id` (ссылка на `TruckOdometerEvent` для 1:1 связи).
  - Бизнес‑правило:
    - Одометр обязателен только для категорий `FUEL` и `ADBLUE`.
    - Для остальных категорий одометр может (и часто должен) быть `null`.

- **TruckOdometerEvent** (`app/Models/TruckOdometerEvent.php`)
  - Унифицированная таблица "событий одометра" для тягача:
    - выезд/возврат в гараж (RUN),
    - шаги рейса (STEP),
    - расходы водителя (EXPENSE).
  - Ключевые поля:
    - `truck_id`, `driver_id`, `trip_id`, `trip_step_id`, `trip_expense_id`.
    - `type` (см. ниже), `odometer_km`, `source` (CAN/мileage/manual/...).
    - `occurred_at`, `mapon_at`, `is_stale`, `stale_minutes`.
    - `step_status` (снимок статуса шага при TYPE_STEP).
    - `expense_category`, `expense_amount` (снимки при TYPE_EXPENSE).
    - `note`, `raw` (JSON с деталями).
  - Типы (`type`):
    - `TYPE_DEPARTURE = 1` — старт `VehicleRun` (выезд из гаража).
    - `TYPE_RETURN = 2` — завершение `VehicleRun` (возврат).
    - `TYPE_EXPENSE = 3` — расход водителя (одометр + категория + сумма).
    - `TYPE_STEP = 4` — изменение статуса шага рейса.
  - Источники (`source`):
    - `SOURCE_CAN`, `SOURCE_MILEAGE`, `SOURCE_MANUAL`, `SOURCE_FALLBACK_LOCAL`.

- **VehicleRun** (`app/Models/VehicleRun.php`)
  - "Заезд" для тягача: от выезда из гаража до возврата.
  - Ключевые поля:
    - `truck_id`, `driver_id`.
    - `started_at`, `ended_at`.
    - `start_can_odom_km`, `end_can_odom_km`.
    - `start_engine_hours`, `end_engine_hours`.
    - `status`, `close_reason`.
  - Связан с `Trip` через `trips.vehicle_run_id` (один run может покрывать несколько рейсов).

### Enums и их роли

- **TripExpenseCategory** (`app/Enums/TripExpenseCategory.php`)
  - Перечисление категорий расходов:
    - Топливо и жидкости: `FUEL`, `ADBLUE`, `WASHER_FLUID`.
    - Услуги/штрафы: `CAR_WASH`, `TOLL`, `PARKING`, `FINE`, `PERMIT`, `REPAIR`, `HOTEL`.
    - Финансовые: `SUBCONTRACTOR` (оплата субперевозчику), `OTHER`.
  - Методы:
    - `label()` — человекочитаемая метка на латышском (например, `Degviela`).
    - `options()` — массив для `<select>` в формах.

- **OdometerEventType** (`app/Enums/OdometerEventType.php`)
  - Абстрактные типы событий одометра для отображения в статистике:
    - `RUN_START`, `RUN_END` — выезд/возврат.
    - `STEP_ARRIVED`, `STEP_COMPLETED` — точки маршрута.
    - `MANUAL` — ручной ввод.
  - Методы:
    - `label()` — заголовок бейджа (`Garage departure`, `Run ended`, …).
    - `badgeClass()` — CSS‑класс Tailwind для цветного бейджа.
  - В статистике используется для строк `row_kind = 'event'` (не для расходов).

- **TripStatus / TripStepStatus** (`app/Enums/TripStatus.php`, `app/Enums/TripStepStatus.php`)
  - `TripStatus`:
    - Статус рейса: планируется/в пути/завершён/отменён и т.п.
    - Используется в `Trip::$casts['status']` и в UI (`status->label()`, `status->color()`).
  - `TripStepStatus`:
    - Статус шага маршрута: запланирован, в процессе, завершён и т.п.
    - Снимок статуса дублируется в `truck_odometer_events.step_status` для TYPE_STEP.

---

## Высокоуровневая картина

- **Trips** — каркас рейса: транспорт, даты, компания‑перевозчик, шаги и грузы.
- **Cargos/Items** — что везём, для кого и за сколько (стоимость фрахта и структуры груза).
- **Expenses** — все траты водителя/компании по рейсу, с категорией и (опциональным) одометром.
- **TruckOdometerEvent** — "шина" одометрических событий, в которую складываются:
  - выезды/возвраты (`VehicleRunService` / `GarageDepartureService`),
  - статусы шагов (`StepStatusService`),
  - расходы водителей (`ExpenseEventService`, команда `expenses:sync-odometer-events`).
- **VehicleRun** — агрегирует CAN‑одометр и ресурсы двигателя для серии рейсов.


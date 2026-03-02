## Fleet Manager — карта файлов по потокам

### Общая структура проекта

- **Маршруты**
  - `routes/web.php` — админка (guard `web`), дашборд, справочники, рейсы, статистика.
  - `routes/driver.php` — PWA водителя (guard `driver`), login/offline/dashboard/trip.
  - `routes/api.php` — API‑маршруты (если используются).
  - `routes/console.php` — консольные команды (регистрация через `Artisan::command`).
  - `routes/auth.php` — Laravel Breeze / Fortify аутентификация для админки.

- **Модели (ядро домена) — `app/Models`**
  - Рейсы и грузы:
    - `Trip` — рейс.
    - `TripCargo` — груз по рейсу.
    - `TripCargoItem` — товарная позиция.
    - `TripStep` — шаг маршрута.
    - `TripStatusHistory` — история статусов рейса.
    - `TripDocument` / `TripStepDocument` — документы по рейсу и шагам.
  - Транспорт и водители:
    - `Truck`, `Trailer`, `Driver`.
    - `VehicleRun` — заезд тягача.
    - `TruckOdometerEvent` — события одометра.
    - `OdometerEvent` — сырые данные одометра/Mapon.
  - Деньги/документы:
    - `TripExpense` — расходы по рейсу.
    - `Invoice`, `InvoicePayment`.
    - `DriverEvent` — отдельные события по водителю (напоминания/штрафы и т.п.).
  - Компании и клиенты:
    - `Company` — юр. лица (Lakna/Padex/expeditor/third party).
    - `Client` — клиенты для грузов/фрахтов.
  - Пользователи:
    - `User` — учётные записи админки.

- **Enums — `app/Enums`**
  - `TripStatus` — статусы рейса.
  - `TripStepStatus` — статусы шагов.
  - `TripExpenseCategory` — категории расходов.
  - `OdometerEventType` — абстрактные типы событий одометра для UI.
  - `DriverStatus`, `StepDocumentType`, `TripDocumentType` — статусы и типы документов.

- **Livewire‑компоненты — `app/Livewire`**
  - Админка:
    - Списки: `TripsTable`, `DriversTable`, `TrucksTable`, `TrailersTable`, `ClientsTable`, `InvoicesTable`, `ExpiringDocumentsTable`.
    - Рейсы: `Trips\CreateTrip`, `Trips\EditTrip`, `Trips\ViewTrip`, `Trips\TripRouteEditor`, `Trips\TripDocumentsSection`, `Trips\TripExpensesSection`, `Trips\TripStepDocumentUploader`.
    - Клиенты/водители/транспорт: `Drivers\*`, `Trucks\*`, `Trailers\*`, `Clients\*`.
    - Статистика: `Stats\TripsStatsTable`, `Stats\EventsTable`.
    - Аутентификация: `Auth\Login`, `Auth\Register`, `Forms\LoginForm`.
  - Driver PWA:
    - Страницы: `DriverApp\Login`, `DriverApp\Dashboard`, `DriverApp\TripDetails`, `DriverApp\ViewDocument`, `DriverApp\Profile`, `DriverApp\TripHistory`.
    - Операции: `DriverApp\DriverTripExpenses`, `DriverApp\UploadDocument`, `DriverApp\DriverStepDocumentUploader`.

- **Сервисы — `app/Services`**
  - Odometer / Mapon:
    - `Services/Odometer/VehicleRunService` — управление `VehicleRun`.
    - `Services/Odometer/GarageDepartureService` — логика выезда/возврата.
    - `Services/Odometer/MaponOdometerFetcher` — интеграция с Mapon.
  - Expenses:
    - `Expenses/ExpenseEventService` — запись/обновление `TruckOdometerEvent(TYPE_EXPENSE)` по `TripExpense`.
  - Другое:
    - `Services/Steps/StepStatusService` — изменение статусов шагов (и связанных событий).
    - `Services/ExpiringDocsNotifier` — напоминания по истекающим документам.

- **Views — `resources/views` (ключевые)**
  - Админка:
    - `layouts/app.blade.php` — основной layout.
    - `livewire/trips/*.blade.php` — формы/страницы рейсов (create/edit/view/route-editor/expenses/documents).
    - `livewire/stats/events-table.blade.php`, `livewire/stats/trips-stats-table.blade.php`.
    - `livewire/*-table.blade.php` — списки (водители, грузы, транспорт, клиенты).
  - Driver PWA:
    - `driver-app/layouts/*.blade.php` — layout для мобильного.
    - `driver-app/pages/*.blade.php` — основные экраны (login, dashboard, trip-history).
    - `livewire/driver-app/*.blade.php` — компоненты интерфейса (расходы, документы и т.п.).
  - PDF:
    - `pdf/cmr-template.blade.php`, `pdf/invoice-template.blade.php`, `pdf/transport-order.blade.php`.

- **Миграции — `database/migrations` (основные таблицы домена)**
  - `2025_10_29_213034_create_trips_table.php`
  - `2025_10_29_213139_create_trip_cargos_table.php`
  - `2025_11_01_183050_create_trip_cargo_items_table.php`
  - `2025_11_21_124106_create_trip_steps_table.php`
  - `2025_11_09_102728_create_trip_documents_table.php`
  - `2025_12_02_015625_create_trip_step_documents.php`
  - `2025_11_09_102737_create_trip_expenses_table.php`
  - `2026_01_13_005051_create_truck_odometer_events.php`
  - `2026_01_13_003137_create_vehicle_runs_table.php`
  - `2026_01_13_003237_create_odometer_events.php`
  - `2026_02_26_195904_create_companies_table.php`
  - `2026_02_25_130813_create_invoices_table.php`
  - `2026_02_25_130908_create_invoice_payments_table.php`

- **PWA assets — `public`**
  - Admin PWA:
    - `public/admin/manifest.webmanifest`
    - `public/admin/serviceworker.js`
  - Driver PWA:
    - `public/driver/manifest.webmanifest`
    - `public/driver/serviceworker.js`
    - `public/driver/icons/*`
  - Общие:
    - `public/sw-dev.js` — dev‑service worker.
    - `public/pwa/push.js` — скрипт для push‑уведомлений.

---

## Карта файлов по потокам

### Поток: Создание рейса в админке

- **Маршруты**
  - `routes/web.php` — маршруты `/trips`, `/trips/create`, `/trips/{trip}`, `/trips/{trip}/edit`.
- **Модели**
  - `app/Models/Trip.php`
  - `app/Models/TripCargo.php`
  - `app/Models/TripCargoItem.php`
  - `app/Models/TripStep.php`
  - `app/Models/Company.php`
  - `app/Models/Client.php`
- **Livewire**
  - `app/Livewire/Trips/CreateTrip.php`
  - `app/Livewire/Trips/EditTrip.php`
  - `app/Livewire/TripsTable.php`
- **Views**
  - `resources/views/livewire/trips/create-trip.blade.php`
  - `resources/views/livewire/trips/edit-trip.blade.php`
  - `resources/views/livewire/trips-table.blade.php`

### Поток: Прохождение шагов рейса

- **Модели**
  - `app/Models/Trip.php` (relation `steps()`).
  - `app/Models/TripStep.php`
  - `app/Models/TripStatusHistory.php`
- **Livewire**
  - `app/Livewire/Trips/ViewTrip.php`
  - `app/Livewire/Trips/TripRouteEditor.php`
  - `app/Livewire/Trips/TripStepDocumentUploader.php`
- **Views**
  - `resources/views/livewire/trips/view-trip.blade.php`
  - `resources/views/livewire/trips/trip-route-editor.blade.php`
  - `resources/views/livewire/trips/trip-step-document-uploader.blade.php`
- **Сервисы (статусы/одометр)**
  - `app/Services/Steps/StepStatusService.php`
  - `app/Models/TruckOdometerEvent.php` (TYPE_STEP, `step_status`).

### Поток: Добавление расхода водителем

- **Маршруты**
  - `routes/driver.php` — `/driver/dashboard`, `/driver/trip/{trip}`.
- **Модели**
  - `app/Models/Trip.php`
  - `app/Models/TripExpense.php`
  - `app/Models/TruckOdometerEvent.php`
- **Enums**
  - `app/Enums/TripExpenseCategory.php`
- **Livewire (Driver PWA)**
  - `app/Livewire/DriverApp/TripDetails.php`
  - `app/Livewire/DriverApp/DriverTripExpenses.php`
- **Views**
  - `resources/views/driver-app/pages/trip-history.blade.php`
  - `resources/views/livewire/driver-app/trip-details.blade.php`
  - `resources/views/livewire/driver-app/driver-trip-expenses.blade.php`

### Поток: Синхронизация расходов с одометр‑событиями

- **Команда**
  - `app\Console\Commands\SyncTripExpensesWithOdometerEvents.php`
- **Модели**
  - `app/Models/TripExpense.php`
  - `app/Models/TruckOdometerEvent.php`
- **Сервис**
  - `app/Services/Expenses/ExpenseEventService.php`
- **Enums**
  - `app/Enums/TripExpenseCategory.php`
  - (косвенно) `app/Enums/OdometerEventType.php`

### Поток: Departure / Return и VehicleRun

- **Модели**
  - `app/Models/VehicleRun.php`
  - `app/Models/Truck.php`
  - `app/Models/Trip.php` (поле `vehicle_run_id`)
  - `app/Models/OdometerEvent.php`
  - `app/Models/TruckOdometerEvent.php`
- **Сервисы**
  - `app/Services/Services/Odometer/VehicleRunService.php`
  - `app/Services/Services/Odometer/GarageDepartureService.php`
  - `app/Services/Services/Odometer/MaponOdometerFetcher.php`
- **Stats / UI**
  - `app/Livewire/Stats/EventsTable.php`
  - `resources/views/livewire/stats/events-table.blade.php`

---

## Как поддерживать документацию актуальной

- При изменении **структуры таблиц**:
  - Обновлять `docs/domain.md` (описание сущности + ключевые поля).
  - Если добавляется новый доменный поток — дописывать `docs/flows.md` и `docs/file-map.md`.
- При добавлении **нового потока** (например, новый тип документа/отчёта):
  - Добавить краткое описание шага в `docs/flows.md`.
  - В `docs/file-map.md` завести новый подраздел "Поток: …" и перечислить:
    - маршруты,
    - модели,
    - Livewire‑компоненты,
    - сервисы,
    - ключевые шаблоны.
- При изменении **enums** (новая категория расхода / статус):
  - Обновить список в `docs/domain.md` в соответствующем разделе Enums.
- Для AI‑агентов:
  - Использовать `docs/domain.md` для понимания сущностей и связей.
  - Использовать `docs/flows.md` и `docs/file-map.md` как входную точку перед редактированием бизнес‑логики.


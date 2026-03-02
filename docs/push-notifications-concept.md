# Push-уведомления (десктоп и мобильные)

## Цель

Показывать пуш-уведомления при событиях, например:
- **Выезд из гаража** — водитель нажал «Выезд», рейс стартовал → диспетчер/админ видит уведомление.
- Возврат в гараж, смена статуса шага и т.д. — по тому же принципу.

Один и тот же механизм работает **и на десктопе (Chrome, Firefox, Edge), и на мобильных** (Android Chrome, Safari iOS 16.4+).

---

## Что уже есть в проекте

| Компонент | Назначение |
|-----------|------------|
| `laravel-notification-channels/webpush` | Отправка Web Push с бэкенда |
| `config/webpush.php` | VAPID-ключи, таблица подписок |
| Миграция `push_subscriptions` | Хранение endpoint + ключи по пользователю |
| `User::HasPushSubscriptions` | Связь User ↔ подписки |
| `POST /push/subscribe` (web) | Сохранение подписки после разрешения в браузере |
| `public/pwa/push.js` | Запрос разрешения, `PushManager.subscribe()`, вызов API |
| `TestPushNotification` | Пример уведомления по каналу `webpush` |

Подписки привязаны к **User** (админ/диспетчер). Водитель логинится под guard `driver`; если у водителя нет записи User с подпиской, пуши ему не отправляются (при необходимости это можно расширить).

---

## Концепция потока

```
[Браузер админа/диспетчера]
    → Страница с кнопкой «Включить уведомления»
    → Notification.requestPermission()
    → Service Worker готов (navigator.serviceWorker.ready)
    → pushManager.subscribe({ applicationServerKey: VAPID_PUBLIC_KEY })
    → POST /api/push/subscribe { endpoint, keys }
    → Backend: user->updatePushSubscription(...)  // сохраняем в push_subscriptions

[Событие: водитель выехал из гаража]
    → Dashboard::saveManualOdo() или TripDetails::startTrip()
    → После успешного commit: «кто должен получить пуш?»
    → Например: User::whereHas('pushSubscriptions')->get() или по роли/компании
    → foreach: $user->notify(new DriverDepartureNotification($trip, $driver))
    → Канал webpush → WebPushMessage → VAPID → доставка в браузер

[Браузер]
    → Service Worker получает push-событие
    → event.waitUntil(showNotification(...))  // показ уведомления ОС
    → Клик по уведомлению → focus/открытие вкладки приложения (опционально)
```

---

## Кто получает уведомления

**Реализовано: один получатель** — пуш отправляется только пользователю с email из конфига.

- В `.env` задаётся **`PUSH_RECIPIENT_EMAIL=email@example.com`** (например, диспетчер).
- В `config/notifications.php`: **`push_recipient_email`** читает эту переменную.
- При событии (выезд из гаража) уведомление получает только этот пользователь, и только если у него есть активная подписка (`pushSubscriptions`).

По умолчанию используется **rvr@arguss.lv** (можно переопределить через `PUSH_RECIPIENT_EMAIL` в `.env`). Если переменная пуста — пуш никому не отправляется.

**Нужно ли создавать пользователя?** Да. В таблице `users` должен быть пользователь с этим email — иначе пуш некому отправлять. Создать его можно командой:

```bash
php artisan make:push-recipient
```

Будет создан пользователь с email из `config('notifications.push_recipient_email')` (по умолчанию rvr@arguss.lv), пароль выведется в консоль — сохраните его и смените после входа. Дальше войдите в админку под этим пользователем и один раз нажмите «Включить уведомления».

---

## Приходят ли пуши, когда приложение закрыто?

**Да.** Web Push как раз для этого: вкладку/приложение держать открытой не нужно.

- После того как пользователь один раз разрешил уведомления и подписка сохранена, сервер может отправить пуш в любой момент.
- Браузер (или ОС) получает push и показывает уведомление **даже при закрытой вкладке и закрытом браузере** (на мобильных — когда приложение свернуто или закрыто).
- Ограничение: на некоторых ОС «энергосбережение» может реже будить браузер; в целом пуши доставляются и при закрытом приложении.

---

## Десктоп vs мобильные

- **Единый Web Push API:** один и тот же `POST /api/push/subscribe` и одна и та же отправка через `webpush` — без разделения «десктоп/мобильный».
- **Подписка привязана к браузеру/устройству:** один User может иметь несколько записей в `push_subscriptions` (разные endpoint для телефона и ПК).
- **Ограничения:**
  - iOS: Web Push в PWA поддерживается с iOS 16.4+, только в добавленном на домашний экран PWA.
  - Android: Chrome поддерживает в браузере и в PWA.
  - Десктоп: Chrome, Firefox, Edge — полная поддержка.

Никакой отдельной «мобильной» логики на бэкенде не нужно — только корректно запрашивать разрешение и подписываться на фронте (один сценарий для всех).

---

## Настройка пушей (сделано в коде)

### 1. Сгенерировать VAPID-ключи

Выполнить в проекте:

```bash
php artisan webpush:vapid
```

Скопировать вывод в `.env`:

```
VAPID_SUBJECT=mailto:rvr@arguss.lv
VAPID_PUBLIC_KEY=...
VAPID_PRIVATE_KEY=...
```

Затем: `php artisan config:clear`.

### 2. Передача VAPID и кнопка в админке

В layout админки уже добавлено: `window.VAPID_PUBLIC_KEY`, подключение `push.js`, кнопка «🔔 Включить уведомления» в сайдбаре. Подписка отправляется на `POST /push/subscribe` (web, с сессией и CSRF).

### 3. Service Worker для push

Зарегистрирован корневой SW `public/serviceworker.js` (scope `/`), чтобы пуш работал на `/dashboard` и всех страницах админки. В нём обрабатываются `push` и `notificationclick`.

### 4. Класс уведомления (например, «Выезд из гаража»)

Отдельный класс, например `App\Notifications\DriverDepartureNotification`:

- `via($notifiable)` → `['webpush']`
- `toWebPush($notifiable, $notification)` → `WebPushMessage` с заголовком/телом, опционально `icon`, `badge`, `url` (открытие рейса при клике)

### 5. Вызов при событии «выезд из гаража»

Реализовано:

- **Dashboard::saveManualOdo()** — после успешного выезда (departure) пуш отправляется одному получателю (email из `config('notifications.push_recipient_email')`), если у него есть подписка.
- **TripDetails::startTrip()** — при старте рейса без модалки одометра пуш можно вызывать аналогично (скопировать логику из Dashboard).

Получатель: один `User` по email из `PUSH_RECIPIENT_EMAIL`. Уведомление: `DriverDepartureNotification`.

Для production уведомление лучше ставить в очередь: `implements ShouldQueue`, чтобы не блокировать ответ водителю.

### 6. Service Worker: показ уведомления при push

В корневом SW `public/serviceworker.js` (scope `/`) обработаны `push` и `notificationclick`:

```js
self.addEventListener('push', (e) => {
    const data = e.data?.json() || {};
    e.waitUntil(
        self.registration.showNotification(data.title || 'Fleet', {
            body: data.body || '',
            icon: data.icon || '/images/icons/icon-192.png',
            badge: data.badge || '/images/icons/icon-72.png',
            data: { url: data.url },
            tag: data.tag,
        })
    );
});
self.addEventListener('notificationclick', (e) => {
    e.notification.close();
    if (e.notification.data?.url)
        e.waitUntil(clients.openWindow(e.notification.data.url));
});
```

Пакет `webpush` сам формирует payload; важно, чтобы в `WebPushMessage` передавались `title` и `body`, которые попадют в `data` на фронте (формат зависит от канала).

---

## Краткий чеклист

1. [x] Проброс `VAPID_PUBLIC_KEY` в layout и кнопка «Включить уведомления» в сайдбаре.
2. [x] Класс `DriverDepartureNotification` (и при необходимости другие события).
3. [x] Вызов `notify(DriverDepartureNotification(...))` после выезда из гаража в Dashboard (в TripDetails — при необходимости).
4. [x] Обработка `push` и `notificationclick` в корневом SW (`public/serviceworker.js`, scope `/`).
5. [ ] Сгенерировать ключи: `php artisan webpush:vapid` и прописать в `.env`.
6. [ ] (Опционально) Очередь для уведомлений (`ShouldQueue`), настройка очередей в production.

После этого пуш «Водитель X выехал по рейсу #N» будет приходить на все устройства (десктоп и мобильные), где пользователь разрешил уведомления и подписался через приложение.

---

## Пуш в уже установленном приложении (десктоп)

1. **Сервер:** в `.env` должны быть заданы `VAPID_PUBLIC_KEY`, `VAPID_PRIVATE_KEY`, `VAPID_SUBJECT`, `PUSH_RECIPIENT_EMAIL=rvr@arguss.lv`. Выполнить `php artisan config:clear`.
2. **Открыть приложение** (установленное на рабочий стол или в браузере по адресу сайта).
3. **Войти** под пользователем rvr@arguss.lv (или под тем, кто будет получать пуши).
4. **В сайдбаре нажать** «🔔 Включить уведомления». На кнопке появятся подсказки: «Запрос разрешения…» → «Ожидание Service Worker…» → «Подписка…». В диалоге браузера выбрать «Разрешить».
5. Если в конце на кнопке написано **«🔔 Уведомления включены»** — подписка сохранена, пуши будут приходить (в т.ч. при закрытом окне).
6. **Проверка:** в приложении водителя войти по PIN — на почту/устройство rvr@arguss.lv должен прийти пуш «Водитель вошёл в систему».

Если на кнопке появляется ошибка (например «Сессия истекла…» или «Ошибка 419») — обнови страницу (F5), снова войди и нажми «Включить уведомления» ещё раз.

**Браузер не спрашивает разрешение (нет окошка «Разрешить уведомления»):** так бывает в установленном приложении (PWA). Открой тот же сайт **в обычной вкладке** браузера (по адресу https://…), войди в админку и нажми «Включить уведомления» там — запрос разрешения должен появиться. После сохранения подписки пуши будут приходить и в установленное приложение.

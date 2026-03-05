<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

use App\Models\Driver;

class DriverLoggedInNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Driver $driver
    ) {}

    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush(object $notifiable, $notification): WebPushMessage
    {
        $name = trim($this->driver->first_name . ' ' . $this->driver->last_name) ?: 'Водитель';
        $title = '🔐 Водитель вошёл в систему';
        $body = "{$name} авторизовался по PIN-коду.";

        $url = route('drivers.show', $this->driver);

        return (new WebPushMessage())
            ->title($title)
            ->body($body)
            ->icon(asset('images/icons/icon-192.png'))
            ->badge(asset('images/icons/icon-72.png'))
            ->data(['url' => $url])
            ->tag('driver-login-' . $this->driver->id);
    }
}

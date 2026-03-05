<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

use App\Models\Trip;
use App\Models\Driver;

class DriverDepartureNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Trip $trip,
        public ?Driver $driver = null
    ) {}

    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush(object $notifiable, $notification): WebPushMessage
    {
        $driverName = $this->driver
            ? trim($this->driver->first_name . ' ' . $this->driver->last_name)
            : 'Водитель';
        $truckPlate = $this->trip->truck?->plate ?? '—';
        $title = '🚛 Выезд из гаража';
        $body = "{$driverName}, рейс #{$this->trip->id} ({$truckPlate})";

        $url = route('trips.show', $this->trip);

        return (new WebPushMessage())
            ->title($title)
            ->body($body)
            ->icon(asset('images/icons/icon-192.png'))
            ->badge(asset('images/icons/icon-72.png'))
            ->data(['url' => $url])
            ->tag('driver-departure-' . $this->trip->id);
    }
}

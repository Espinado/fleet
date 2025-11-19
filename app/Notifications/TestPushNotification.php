<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use App\Notifications\TestPushNotification;
use Illuminate\Support\Facades\Auth;

class TestPushNotification extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return ['webpush'];
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage())
            ->title('🔔 Test Push from Laravel')
            ->body('If you see this on your phone — PUSH works!')
            ->icon('/images/icons/icon-192x192.png')
            ->badge('/images/icons/icon-72x72.png');
    }
}

<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\TestPushNotification;
use Illuminate\Console\Command;

class SendTestPushNotification extends Command
{
    protected $signature = 'webpush:test
                            {--email= : User email to send test push to (default: PUSH_RECIPIENT_EMAIL) }';
    protected $description = 'Check push settings and send a test Web Push notification';

    public function handle(): int
    {
        $this->info('Web Push — check settings and test notification');
        $this->newLine();

        $email = $this->option('email') ?: config('notifications.push_recipient_email');
        if (empty($email)) {
            $this->error('PUSH_RECIPIENT_EMAIL is not set in .env');
            $this->line('  Set it to the user who should receive push (e.g. rvr@arguss.lv)');
            return 1;
        }

        $this->line('  PUSH_RECIPIENT_EMAIL: ' . $email);
        $this->line('  VAPID public key: ' . (config('webpush.vapid.public_key') ? 'set' : 'missing'));
        $this->newLine();

        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("User with email [{$email}] not found.");
            $this->line('  Create user: php artisan make:push-recipient --email=' . $email);
            return 1;
        }

        $count = $user->pushSubscriptions()->count();
        if ($count === 0) {
            $this->warn('User has no push subscriptions.');
            $this->line('  1. Log in to the app as: ' . $email);
            $this->line('  2. Click "🔔 Включить уведомления" (or push button) in the sidebar');
            $this->line('  3. Allow notifications in the browser');
            $this->line('  4. Run this command again: php artisan webpush:test');
            return 1;
        }

        $this->line("  Subscriptions: {$count}");
        $this->newLine();

        try {
            $user->notify(new TestPushNotification());
            $this->info('Test push sent. Check the device/browser where you enabled notifications.');
            return 0;
        } catch (\Throwable $e) {
            $this->error('Send failed: ' . $e->getMessage());
            return 1;
        }
    }
}

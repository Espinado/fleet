<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\WebPush;

class VerifyVapidKeys extends Command
{
    protected $signature = 'webpush:verify-vapid
                            {--show : Show public key (safe to share) }';
    protected $description = 'Verify VAPID keys from .env (subject, public key, private key format and pair)';

    public function handle(): int
    {
        $subject = config('webpush.vapid.subject');
        $publicKey = config('webpush.vapid.public_key');
        $privateKey = config('webpush.vapid.private_key');

        $this->info('Checking VAPID configuration...');
        $this->newLine();

        $ok = true;

        // 1. Presence
        if (empty($subject)) {
            $this->error('VAPID_SUBJECT is missing or empty.');
            $ok = false;
        } else {
            $this->line('  VAPID_SUBJECT: ' . $subject);
        }

        if (empty($publicKey)) {
            $this->error('VAPID_PUBLIC_KEY is missing or empty.');
            $ok = false;
        } else {
            $len = strlen($publicKey);
            $this->line('  VAPID_PUBLIC_KEY: set (' . $len . ' chars)');
            if ($this->option('show')) {
                $this->line('    ' . $publicKey);
            }
        }

        if (empty($privateKey)) {
            $this->error('VAPID_PRIVATE_KEY is missing or empty.');
            $ok = false;
        } else {
            $this->line('  VAPID_PRIVATE_KEY: set (' . strlen($privateKey) . ' chars, hidden)');
        }

        if (!$ok) {
            $this->newLine();
            $this->warn('Fix .env and run: php artisan config:clear');
            return 1;
        }

        // 2. Format (base64url: A-Za-z0-9_-)
        $base64url = '/^[A-Za-z0-9_-]+$/';
        if (!preg_match($base64url, $publicKey)) {
            $this->error('VAPID_PUBLIC_KEY contains invalid characters (expected base64url: A-Za-z0-9_-).');
            $ok = false;
        }
        if (!preg_match($base64url, $privateKey)) {
            $this->error('VAPID_PRIVATE_KEY contains invalid characters (expected base64url: A-Za-z0-9_-).');
            $ok = false;
        }

        if (!$ok) {
            return 1;
        }

        // 3. Try to create WebPush instance (validates key pair when building auth)
        try {
            $auth = [
                'VAPID' => [
                    'subject'    => $subject,
                    'publicKey'  => $publicKey,
                    'privateKey' => $privateKey,
                ],
            ];
            new WebPush($auth);
            $this->info('  WebPush client accepted the keys.');
        } catch (\Throwable $e) {
            $this->error('  WebPush client rejected the keys: ' . $e->getMessage());
            $ok = false;
        }

        $this->newLine();
        if ($ok) {
            $this->info('VAPID keys look correct. You can send a test notification to confirm delivery.');
            return 0;
        }

        $this->warn('To generate new keys run: php artisan webpush:vapid');
        return 1;
    }
}

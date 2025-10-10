<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ExpiringDocsNotifier;

class SendExpiringDocsEmails extends Command
{
    protected $signature = 'expiring-docs:send';
    protected $description = 'Send notifications about expiring documents';

    public function handle()
    {
          $notifier = new ExpiringDocsNotifier();
        $notifier->send();  // твой метод отправки
        $this->info('Expiring documents notifications sent.');
    }
}

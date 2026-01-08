<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ExpiringDocumentsReport extends Mailable
{
    use Queueable, SerializesModels;

    public Collection $items;
    public array $company;

    public function __construct(Collection $items, array $company = [])
    {
        $this->items = $items;
        $this->company = $company;
    }

    public function build()
    {
        $subject = '⏰ Expiring documents'
            . (!empty($this->company['name']) ? ' — ' . $this->company['name'] : '');

        return $this
            ->subject($subject)
            ->cc('rvr@arguss.lv')
            ->view('emails.expiring-docs');
    }
}

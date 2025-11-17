<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExpiringDocumentsReport extends Mailable
{
    use Queueable, SerializesModels;

    public $items;

    public function __construct($items)
    {
        $this->items = $items;
    }

  public function build()
{
    return $this->subject('Dokumenti ar beigu termiņu (30 dienas)')
        ->from('no-reply@arguss.lv', 'Fleet Manager')
        ->markdown('emails.expiring-docs');
}
}

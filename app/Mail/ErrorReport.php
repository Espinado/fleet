<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ErrorReport extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Throwable $exception
    ) {}

    public function build()
    {
        $subject = '[Fleet] Ошибка: ' . \Str::limit($this->exception->getMessage(), 60);

        return $this
            ->to('rvr@arguss.lv')
            ->subject($subject)
            ->markdown('emails.error-report', [
                'exception' => $this->exception,
            ]);
    }
}

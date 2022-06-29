<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendBoughtTicketsToUser extends Mailable
{
    use Queueable, SerializesModels;

    public $codes;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $codes)
    {
        $this->codes = $code;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('nao-responder@zehticket.com.br', 'Ingressos comprados - Equipe ZehTicket')
        ->view('emails.bought-tickets');
    }
}
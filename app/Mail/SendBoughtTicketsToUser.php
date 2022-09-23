<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendBoughtTicketsToUser extends Mailable
{
    use Queueable, SerializesModels;

    public $codes;
    public $client_name;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $codes, string $client_name)
    {
        $this->codes = $codes;
        $this->client_name = $client_name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('equipe@zehticket.com.br', 'Equipe ZehTicket')
            ->subject('Ingressos Comprados com Sucesso!')
            ->view('emails.bought-tickets');
    }
}

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendBoughtTicketsToUser extends Mailable
{
    use Queueable, SerializesModels;

    public array $codes;
    public string $client_name;

    public function __construct(array $codes, string $client_name)
    {
        $this->codes = $codes;
        $this->client_name = $client_name;
    }

    public function build(): self
    {
        return $this->from('equipe@zehticket.com.br', 'Equipe ZehTicket')
            ->subject('Ingressos Comprados com Sucesso!')
            ->view('emails.bought-tickets');
    }
}

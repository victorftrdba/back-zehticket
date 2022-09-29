<?php

namespace App\Helpers;

use App\Models\Ticket;

class TicketHelper
{
    public Ticket $ticket;

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function formatTicket(array $ticket): array
    {
        $tickets = [];

        $_value = $this->ticket->find($ticket['id'])->value;

        $tickets[] = [
            'id' => (string)$ticket['id'],
            'title' => $ticket['description'],
            'unit_price' => ($_value * 100),
            'quantity' => 1,
            'tangible' => true,
            'venue' => $ticket['client_email']
        ];

        return $tickets;
    }
}

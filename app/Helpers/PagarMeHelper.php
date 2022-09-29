<?php

namespace App\Helpers;

use App\Models\Ticket;

class PagarMeHelper
{
    public Ticket $ticket;
    public TicketHelper $ticketHelper;

    public function __construct(
        Ticket       $ticket,
        TicketHelper $ticketHelper
    )
    {
        $this->ticket = $ticket;
        $this->ticketHelper = $ticketHelper;
    }

    public function formatData($user, $ticket, $card_info, $cpf, $address): array
    {
        $total = 0;
        $tickets = [];

        foreach ($ticket as $selectedTicket) {
            $total += $this->ticket->calculateTotalWithTax($selectedTicket['id']);
        }

        $expiration_year = substr($card_info['card_expiration_year'], -2);
        $expiration_month = strlen($card_info['card_expiration_month']) === 1 ? "0{$card_info['card_expiration_month']}" : $card_info['card_expiration_month'];

        $data = [
            'amount' => (string)$total,
            'card_holder_name' => $card_info['card_name'],
            'card_expiration_date' => "{$expiration_month}{$expiration_year}",
            'card_number' => $card_info['card_number'],
            'card_cvv' => $card_info['card_cvv'],
            'payment_method' => 'credit_card',
            'installments' => $card_info['installments'],
        ];

        $data['customer'] = [
            'external_id' => (string)$user->id,
            'name' => $user->name,
            'email' => $user->email,
            'type' => 'individual',
            'country' => 'br',
            'documents' => [
                [
                    'type' => 'cpf',
                    'number' => $cpf
                ]
            ],
            'phone_numbers' => ['+551199999999']
        ];

        $data['billing'] = [
            'name' => $user->name,
            'address' => $address
        ];

        foreach ($ticket as $_selectedTicket) {
            $tickets = $this->ticketHelper->formatTicket($_selectedTicket);
        }

        $data['items'] = $tickets;

        return $data;
    }
}

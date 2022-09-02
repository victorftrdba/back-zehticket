<?php

namespace App\Services;

use PagarMe;

class PagarMeService
{
    public $pagarme;

    public function __construct()
    {
        $this->pagarme = new PagarMe\Client('ak_test_EIMmChmhFVxRJ73ofZrzsKsx7Z7XXA');
    }

    public function payWithCreditCard($user, $ticket, $card_info, $cpf, $address)
    {
        $total = 0;
        $tickets = [];

        foreach ($ticket as $selectedTicket) {
            $total += (($selectedTicket['value'] * 100) * $selectedTicket['quantity']);
        }

        $expiration_year = substr($card_info['card_expiration_year'], -2);

        $expiration_month = strlen($card_info['card_expiration_month']) === 1 ? "0{$card_info['card_expiration_month']}" : $card_info['card_expiration_month'];

        $data = [
            'amount' => $total,
            'card_holder_name' => $card_info['card_name'],
            'card_expiration_date' => "{$expiration_month}{$expiration_year}", // MMAA
            'card_number' => (string) $card_info['card_number'],
            'card_cvv' => (string) $card_info['card_cvc'],
            'payment_method' => 'credit_card',
            // 'postback_url' => 'http://requestb.in/pkt7pgpk',
        ];

        $data['customer'] = [
            'external_id' => (string) $user->id,
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
            'phone_numbers' => [ '+551199999999' ]
        ];

        $data['billing'] = [
            'name' => $user->name,
            'address' => $address
        ];

        foreach ($ticket as $_selectedTicket) {
            if ($_selectedTicket['quantity'] > 0) {
                array_push($tickets, [
                    'id' => (string) $_selectedTicket['id'],
                    'title' => $_selectedTicket['description'],
                    'unit_price' => ($_selectedTicket['value'] * 100),
                    'quantity' => $_selectedTicket['quantity'],
                    'tangible' => true
                ]);
            }
        }

        $data['items'] = $tickets;

        $transaction = $this->pagarme->transactions()->create($data);

        return [
            'status' => $transaction->status,
            'amount' => ($transaction->amount / 100),
            'last_digits' => $transaction->card_last_digits,
            'transaction_id' => $transaction->id,
            'amount_in_cents' => $transaction->amount
        ];
    }

    public function payWithBillet($ticket)
    {
        $total = 0;
        $tickets = [];

        foreach ($ticket as $selectedTicket) {
            $total += (($selectedTicket['value'] * 100) * $selectedTicket['quantity']);
        }

        foreach ($ticket as $_selectedTicket) {
            if ($_selectedTicket['quantity'] > 0) {
                array_push($tickets, [
                    'id' => (string) $_selectedTicket['id'],
                    'title' => $_selectedTicket['description'],
                    'unit_price' => ($_selectedTicket['value'] * 100),
                    'quantity' => $_selectedTicket['quantity'],
                    'tangible' => true,
                ]);
            }
        }

        $transaction = $this->pagarme->paymentLinks()->create([
            "amount" => $total,
            "payment_method" => "boleto",
            "async" => false,
            'payment_config' => [
                'boleto' => [
                    'enabled' => true,
                    'expires_in' => 20
                ],
                'credit_card' => [
                    'enabled' => false,
                    'free_installments' => 4,
                    'interest_rate' => 25,
                    'max_installments' => 12
                ],
                'default_payment_method' => 'boleto'
            ],
            'items' => $tickets,
        ]);

        return $transaction;
    }

    public function captureTransaction($id)
    {
        return $this->pagarme->transactions()->get([
            'id' => (string) $id
        ]);
    }
}

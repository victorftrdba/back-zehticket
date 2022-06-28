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

    public function payWithCreditCard($user, $ticket, $card_info, $amount)
    {
        $expiration_year = substr($card_info['card_expiration_year'], -2);

        $expiration_month = strlen($card_info['card_expiration_month']) === 1 ? "0{$card_info['card_expiration_month']}" : $card_info['card_expiration_month'];

        $data = [
            'amount' => (($ticket->value * $amount) * 100),
            'card_holder_name' => $card_info['card_name'],
            'card_expiration_date' => "{$expiration_month}{$expiration_year}", // MMAA
            'card_number' => (string) $card_info['card_number'],
            'card_cvc' => $card_info['card_cvc'],
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
                    'number' => '67415765095'
                ]
            ],
            'phone_numbers' => [ '+551199999999' ]
        ];

        $data['billing'] = [
            'name' => 'Nome do pagador',
            'address' => [
                'country' => 'br',
                'street' => 'Avenida Brigadeiro Faria Lima',
                'street_number' => '1811',
                'state' => 'sp',
                'city' => 'Sao Paulo',
                'neighborhood' => 'Jardim Paulistano',
                'zipcode' => '01451001'
            ]
        ];

        $data['items'] = [
            [
                'id' => (string) $ticket->id,
                'title' => $ticket->description,
                'unit_price' => ($ticket->value * 100),
                'quantity' => $amount,
                'tangible' => true
            ]
        ];

        $transaction = $this->pagarme->transactions()->create($data);

        return [
            'status' => $transaction->status,
            'amount' => ($transaction->amount / 100),
            'last_digits' => $transaction->card_last_digits,
            'transaction_id' => $transaction->id,
            'amount_in_cents' => $transaction->amount
        ];
    }

    public function captureTransaction($id)
    {
        return $this->pagarme->transactions()->get([
            'id' => (string) $id
        ]);
    }
}
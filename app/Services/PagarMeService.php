<?php

namespace App\Services;

use App\Models\Ticket;
use ArrayObject;
use Exception;
use Illuminate\Support\Facades\Http;
use PagarMe;
use stdClass;

class PagarMeService
{
    public PagarMe\Client $pagarMe;
    public string $key;

    public function __construct()
    {
        if (env('APP_ENV') === 'production') {
            $this->key = 'ak_live_X4uQvjD8QWR1zLB1L7WwpwymUDzJmw';
        } else {
            $this->key = 'ak_test_EIMmChmhFVxRJ73ofZrzsKsx7Z7XXA';
        }

        $this->pagarMe = new PagarMe\Client($this->key);
    }

    public function payWithCreditCard($user, $ticket, $card_info, $cpf, $address): array
    {
        $total = 0;
        $tickets = [];

        foreach ($ticket as $selectedTicket) {
            $value = Ticket::find($selectedTicket['id'])->value;
            $total += (($value * 1.1) * 100);
        }

        $expiration_year = substr($card_info['card_expiration_year'], -2);

        $expiration_month = strlen($card_info['card_expiration_month']) === 1 ? "0{$card_info['card_expiration_month']}" : $card_info['card_expiration_month'];

        $data = [
            'amount' => strval($total),
            'card_holder_name' => $card_info['card_name'],
            'card_expiration_date' => "{$expiration_month}{$expiration_year}", // MMAA
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
            $_value = Ticket::find($selectedTicket['id'])->value;
            $tickets[] = [
                'id' => (string)$_selectedTicket['id'],
                'title' => $_selectedTicket['description'],
                'unit_price' => ($_value * 100),
                'quantity' => 1,
                'tangible' => true,
                'venue' => $_selectedTicket['client_email']
            ];
        }

        $data['items'] = $tickets;

        $transaction = $this->pagarMe->transactions()->create($data);

        return [
            'status' => $transaction->status,
            'amount' => ($transaction->amount / 100),
            'last_digits' => $transaction->card_last_digits,
            'transaction_id' => $transaction->id,
            'amount_in_cents' => $transaction->amount
        ];
    }

    public function payWithBillet($ticket): stdClass|ArrayObject
    {
        $total = 0;
        $tickets = [];

        foreach ($ticket as $selectedTicket) {
            $value = Ticket::find($selectedTicket['id'])->value;
            $total += (($value * 1.1) * 100);
        }

        foreach ($ticket as $_selectedTicket) {
            $_value = Ticket::find($_selectedTicket['id'])->value;
            $tickets[] = [
                'id' => (string)$_selectedTicket['id'],
                'title' => $_selectedTicket['description'],
                'unit_price' => ($_value * 100),
                'quantity' => 1,
                'tangible' => true,
                'venue' => $_selectedTicket['client_email']
            ];
        }

        return $this->pagarMe->paymentLinks()->create([
            "amount" => strval($total),
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
    }

    public function payWithPix($ticket): stdClass|ArrayObject
    {
        $total = 0;
        $tickets = [];

        foreach ($ticket as $selectedTicket) {
            $value = Ticket::find($selectedTicket['id'])->value;
            $total += (($value * 1.1) * 100);
        }

        foreach ($ticket as $_selectedTicket) {
            $_value = Ticket::find($_selectedTicket['id'])->value;
            $tickets[] = [
                'id' => (string)$_selectedTicket['id'],
                'title' => $_selectedTicket['description'],
                'unit_price' => ($_value * 100),
                'quantity' => 1,
                'tangible' => true,
                'venue' => $_selectedTicket['client_email']
            ];
        }

        return $this->pagarMe->paymentLinks()->create([
            "amount" => strval($total),
            "payment_method" => "pix",
            "async" => false,
            'payment_config' => [
                'pix' => [
                    'enabled' => true,
                    'expiration_date' => now()->addDay()
                ],
                'credit_card' => [
                    'enabled' => false,
                    'free_installments' => 4,
                    'interest_rate' => 25,
                    'max_installments' => 12
                ],
                'default_payment_method' => 'pix'
            ],
            'items' => $tickets,
        ]);
    }

    public function captureTransaction($id): stdClass|ArrayObject|null
    {
        try {
            return $this->pagarMe->transactions()->get([
                'id' => (string)$id
            ]);
        } catch (Exception) {
            return null;
        }
    }

    public function captureTransactionLink($id): ?array
    {
        $response = Http::get('https://api.pagar.me/1/orders', [
            'api_key' => $this->key,
            'payment_link_id' => $id,
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }
}

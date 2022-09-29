<?php

namespace App\Services;

use App\Helpers\PagarMeHelper;
use App\Helpers\TicketHelper;
use App\Models\Ticket;
use ArrayObject;
use Exception;
use Illuminate\Support\Facades\Http;
use PagarMe;
use stdClass;

class PagarMeService
{
    public PagarMe\Client $pagarMe;
    public Ticket $ticket;
    public TicketHelper $ticketHelper;
    public PagarMeHelper $pagarMeHelper;
    public string $key;

    public function __construct(
        Ticket        $ticket,
        TicketHelper  $ticketHelper,
        PagarMeHelper $pagarMeHelper
    )
    {
        if (env('APP_ENV') === 'production') {
            $this->key = 'ak_live_X4uQvjD8QWR1zLB1L7WwpwymUDzJmw';
        } else {
            $this->key = 'ak_test_EIMmChmhFVxRJ73ofZrzsKsx7Z7XXA';
        }

        $this->pagarMe = new PagarMe\Client($this->key);
        $this->ticket = $ticket;
        $this->ticketHelper = $ticketHelper;
        $this->pagarMeHelper = $pagarMeHelper;
    }

    public function payWithCreditCard($user, $ticket, $card_info, $cpf, $address): array
    {
        $data = $this->pagarMeHelper->formatData($user, $ticket, $card_info, $cpf, $address);

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
            $total += $this->ticket->calculateTotalWithTax($selectedTicket['id']);
        }

        foreach ($ticket as $_selectedTicket) {
            $tickets[] = $this->ticketHelper->formatTicket($_selectedTicket);
        }

        return $this->pagarMe->paymentLinks()->create([
            "amount" => (string)$total,
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
            $total += $this->ticket->calculateTotalWithTax($selectedTicket['id']);
        }

        foreach ($ticket as $_selectedTicket) {
            $tickets[] = $this->ticketHelper->formatTicket($_selectedTicket);
        }

        return $this->pagarMe->paymentLinks()->create([
            "amount" => (string)$total,
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

<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\Payment;
use App\Helpers\Constants;
use App\Models\PaidTicket;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendBoughtTicketsToUser;

class EventService {
    /**
     * Mostra todos os eventos registrados com paginação
     *
     * @return array
     */
    public function findAll()
    {
        return Event::with(['user', 'tickets'])->paginate(15)->toArray();
    }

    public function show($id)
    {
        return Event::with('tickets')->find($id);
    }

    /**
     * Faz a criação de um novo evento
     *
     * @return array
     */
    public function store($request)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'value' => 'required',
            'user_id' => 'required|integer',
            'image' => 'required',
            'date' => 'required|date',
            'expires' => 'required|date',
            'amount' => 'required|integer',
        ]);

        $event = Event::create($request->all());

        return [
            'success' => $event,
        ];
    }

    /**
     * Durante a compra do ticket é localizado a forma
     * de pagamento selecionado pelo cliente
     *
     * @return string[]
     */
    public function buyTicket($request): Payment|array
    {
        $request->validate([
            'payment_type' => 'required|integer',
            'card_number' => 'integer',
            'card_name' => 'string',
            'card_cvc' => 'integer',
            'card_expiration_month' => 'integer',
            'card_expiration_year' => 'integer',
            'tickets' => 'required|array',
            'tickets.*.id' => 'required|integer',
            'tickets.*.amount' => 'required|integer',
        ]);

        $pagarme = new PagarMeService;

        foreach ($request->tickets as $ticket) {
            if ($ticket['quantity'] > 0) {
                $infoTicket = Ticket::find($ticket['id']);

                if ($infoTicket->amount === 0 || $ticket['quantity'] > $infoTicket->amount) {
                    return [
                        'error' => true,
                        'message' => 'Ingressos esgotados ou insuficientes.'
                    ];
                }
            }
        }

        switch ($request->payment_type) {
            case Constants::CARTAO_CREDITO:
                $card_info = [
                    'card_number' => $request->card_number,
                    'card_name' => $request->card_name,
                    'card_cvc' => $request->card_cvc,
                    'card_expiration_month' => $request->card_expiration_month,
                    'card_expiration_year' => $request->card_expiration_year,
                ];

                $credit_card = $pagarme->payWithCreditCard($request->user(), $request->tickets, $card_info, $ticket['amount']);

                $payment = Payment::create([
                    'total' => $credit_card['amount'],
                    'payment_type' => Constants::CARTAO_CREDITO,
                    'card_number' => $credit_card['last_digits'],
                    'receipt' => $credit_card['transaction_id'],
                    'user_id' => $request->user()->id,
                    'event_id' => $infoTicket->event->id,
                ]);

                if ($pagarme->captureTransaction($credit_card['transaction_id'])->status === "paid") {
                    $codes = [];

                    foreach ($request->tickets as $selectedTicket) {
                        $boughtTicket = Ticket::find($selectedTicket['id']);
                        $boughtTicket->decrement('amount', $selectedTicket['quantity']);

                        for ($i = 0; $i < $selectedTicket['quantity']; $i++) {
                            $code = PaidTicket::create([
                                'code' => Str::uuid(),
                                'event_id' => $boughtTicket->event->id,
                                'ticket_id' => $boughtTicket->id,
                            ]);

                            array_push($codes, $code);
                        }
                    }

                    Mail::to($request->user())->send(new SendBoughtTicketsToUser($codes));
                };
                break;
            case Constants::BOLETO:
                $payment = Payment::create([
                    'total' => 500,
                    'payment_type' => Constants::BOLETO,
                    'receipt' => 'sem integração com api por ora',
                    'user_id' => $request->user_id,
                    'event_id' => $infoTicket->event->id,
                ]);
                break;
            case Constants::TRANSFERENCIA:
                $payment = Payment::create([
                    'total' => 500,
                    'payment_type' => Constants::TRANSFERENCIA,
                    'receipt' => 'sem integração com api por ora',
                    'user_id' => $request->user_id,
                    'event_id' => $infoTicket->event->id,
                ]);
                break;
            case Constants::PIX:
                $payment = Payment::create([
                    'total' => 500,
                    'payment_type' => Constants::PIX,
                    'receipt' => 'sem integração com api por ora',
                    'user_id' => $request->user_id,
                    'event_id' => $infoTicket->event->id,
                ]);
                break;
            default:
                $payment = [
                    'error' => 'Nenhuma opção selecionada!',
                ];
        }

        return $payment;
    }

    /**
     * Mostra eventos do cliente
     *
     * @return events
     */
    public function showUserEvents($request)
    {
        $user = $request->user();

        return Payment::whereId($user->id)->with(['event'])->get();
    }
}
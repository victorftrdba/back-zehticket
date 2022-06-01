<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Payment;
use App\Helpers\Constants;
use App\Models\Ticket;

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
        return Event::find($id);
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
            'card_cvv' => 'integer',
            'card_expiration_month' => 'integer',
            'card_expiration_year' => 'integer',
            'ticket_id' => 'required|integer',
            'amount' => 'required|integer',
        ]);

        $pagarme = new PagarMeService;

        $card_info = [
            'card_number' => $request->card_number,
            'card_name' => $request->card_name,
            'card_cvv' => $request->card_cvv,
            'card_expiration_month' => $request->card_expiration_month,
            'card_expiration_year' => $request->card_expiration_year,
        ];

        $ticket = Ticket::find($request->ticket_id);

        if ($ticket->amount === 0 || $request->amount > $ticket->amount) {
            return [
                'error' => true,
                'message' => 'Ingressos esgotados ou insuficientes.'
            ];
        }

        switch ($request->get('payment_type')) {
            case Constants::CARTAO_CREDITO:
                $credit_card = $pagarme->payWithCreditCard($request->user(), $ticket, $card_info, $request->amount);

                $payment = Payment::create([
                    'total' => $credit_card['amount'],
                    'payment_type' => Constants::CARTAO_CREDITO,
                    'card_number' => $credit_card['last_digits'],
                    'receipt' => $credit_card['transaction_id'],
                    'user_id' => $request->user()->id,
                    'event_id' => $ticket->event->id,
                ]);

                if ($pagarme->captureTransaction($credit_card['transaction_id'])->status === "paid") {
                    $ticket->decrement('amount', $request->amount);
                };
                break;
            case Constants::BOLETO:
                $payment = Payment::create([
                    'total' => 500,
                    'payment_type' => Constants::BOLETO,
                    'receipt' => 'sem integração com api por ora',
                    'user_id' => $request->user_id,
                    'event_id' => $ticket->event->id,
                ]);
                break;
            case Constants::TRANSFERENCIA:
                $payment = Payment::create([
                    'total' => 500,
                    'payment_type' => Constants::TRANSFERENCIA,
                    'receipt' => 'sem integração com api por ora',
                    'user_id' => $request->user_id,
                    'event_id' => $ticket->event->id,
                ]);
                break;
            case Constants::PIX:
                $payment = Payment::create([
                    'total' => 500,
                    'payment_type' => Constants::PIX,
                    'receipt' => 'sem integração com api por ora',
                    'user_id' => $request->user_id,
                    'event_id' => $ticket->event->id,
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

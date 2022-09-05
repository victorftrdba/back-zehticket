<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\Payment;
use App\Helpers\Constants;
use App\Models\PaidTicket;
use Illuminate\Support\Str;
use App\Services\PagarMeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendBoughtTicketsToUser;
use Illuminate\Http\JsonResponse;

class EventService {
    /**
     * Mostra todos os eventos registrados com paginação
     */
    public function findAll(): JsonResponse
    {
        $events = Event::with(['user', 'tickets'])->paginate(15);

        return response()->json($events);
    }

    /**
     * Mostra o evento selecionado pelo usuário
     * @param mixed $id
     */
    public function show($search, $id): JsonResponse
    {
        $event = Event::with('tickets')
            ->when($search, function ($query, $value) {
                return $query->where('title', 'LIKE', "%{$value}%");
            })
            ->find($id);

        return response()->json($event);
    }

    /**
     * Durante a compra do ticket é localizado a forma
     * de pagamento selecionado pelo cliente
     */
    public function buyTicket(array $data): JsonResponse
    {
        $pagarme = new PagarMeService;

        foreach ($data['tickets'] as $ticket) {
            if ($ticket['quantity'] > 0) {
                $infoTicket = Ticket::find($ticket['id']);

                if ($infoTicket->amount === 0 || $ticket['quantity'] > $infoTicket->amount) {
                    return response()->json([
                        'error' => true,
                        'message' => 'Ingressos esgotados ou insuficientes.'
                    ], 406);
                }
            }
        }

        switch ($data['payment_type']) {
            case Constants::CARTAO_CREDITO:
                $card_info = [
                    'card_number' => $data['card_number'],
                    'card_name' => $data['card_name'],
                    'card_cvc' => $data['card_cvc'],
                    'card_expiration_month' => $data['card_expiration_month'],
                    'card_expiration_year' => $data['card_expiration_year'],
                ];

                $credit_card = $pagarme->payWithCreditCard(Auth::user(), $data['tickets'], $card_info, (string) $data['cpf'], $data['address']);

                $payment = Payment::create([
                    'total' => $credit_card['amount'],
                    'payment_type' => Constants::CARTAO_CREDITO,
                    'card_number' => $credit_card['last_digits'],
                    'receipt' => $credit_card['transaction_id'],
                    'user_id' => Auth::user()->id,
                    'event_id' => $infoTicket->event->id,
                ]);
                break;
            case Constants::BOLETO:
                $billet = $pagarme->payWithBillet($data['tickets']);

                $payment = Payment::create([
                    'total' => 500,
                    'payment_type' => Constants::BOLETO,
                    'receipt' => $billet->id,
                    'user_id' => Auth::user()->id,
                    'event_id' => $infoTicket->event->id,
                ]);

                $payment = [
                    'total' => 500,
                    'payment_type' => Constants::BOLETO,
                    'receipt' => $billet->id,
                    'user_id' => Auth::user()->id,
                    'event_id' => $infoTicket->event->id,
                    'payment_id' => $payment->id,
                    'url' => $billet->url,
                ];
                break;
            case Constants::TRANSFERENCIA:
                $payment = Payment::create([
                    'total' => 500,
                    'payment_type' => Constants::TRANSFERENCIA,
                    'receipt' => 'sem integração com api por ora',
                    'user_id' => Auth::user()->id,
                    'event_id' => $infoTicket->event->id,
                ]);
                break;
            case Constants::PIX:
                $pix = $pagarme->payWithPix($data['tickets']);

                $payment = Payment::create([
                    'total' => 500,
                    'payment_type' => Constants::PIX,
                    'receipt' => $pix->id,
                    'user_id' => Auth::user()->id,
                    'event_id' => $infoTicket->event->id,
                ]);

                $payment = [
                    'total' => 500,
                    'payment_type' => Constants::PIX,
                    'receipt' => $pix->id,
                    'user_id' => Auth::user()->id,
                    'event_id' => $infoTicket->event->id,
                    'payment_id' => $payment->id,
                    'url' => $pix->url,
                ];
                break;
            default:
                $payment = [
                    'error' => 'Nenhuma opção selecionada!',
                ];
                break;
        }

        return response()->json($payment, 201);
    }

    /**
     * Mostra eventos do cliente
     */
    public function showUserEvents($request): JsonResponse
    {
        $paidEvents = Payment::whereId($request->user()->id)->with(['event'])->get();

        return response()->json($paidEvents);
    }
}
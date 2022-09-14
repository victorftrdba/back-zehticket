<?php

namespace App\Services;

use App\Helpers\Constants;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class EventService
{
    public function findAll(): JsonResponse
    {
        $events = Event::with(['user', 'tickets'])->paginate(15);

        return response()->json($events);
    }

    public function show(?string $search, int $id): JsonResponse
    {
        $event = Event::with('tickets')
            ->when($search, function ($query, $value) {
                return $query->where('title', 'LIKE', "%{$value}%");
            })
            ->find($id);

        return response()->json($event);
    }

    public function buyTicket(array $data): JsonResponse
    {
        $pagarme = new PagarMeService;

        foreach ($data['tickets'] as $ticket) {
            if ($ticket['quantity'] > 0) {
                $infoTicket = Ticket::findOrFail($ticket['id']);

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
                    'card_cvv' => $data['card_cvv'],
                    'card_expiration_month' => $data['card_expiration_month'],
                    'card_expiration_year' => $data['card_expiration_year'],
                    'installments' => $data['installments'],
                ];

                $credit_card = $pagarme->payWithCreditCard(Auth::user(), $data['tickets'], $card_info, $data['cpf'], $data['address']);

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

                $paymentInfo = Payment::create([
                    'total' => ($billet->amount / 100),
                    'payment_type' => Constants::BOLETO,
                    'receipt' => $billet->id,
                    'user_id' => Auth::user()->id,
                    'event_id' => $infoTicket->event->id,
                ])->toArray();

                $payment = [
                    ...$paymentInfo,
                    'payment_id' => $paymentInfo['id'],
                    'url' => $billet->url,
                ];
                break;
            case Constants::PIX:
                $pix = $pagarme->payWithPix($data['tickets']);

                $paymentInfo = Payment::create([
                    'total' => ($pix->amount / 100),
                    'payment_type' => Constants::PIX,
                    'receipt' => $pix->id,
                    'user_id' => Auth::user()->id,
                    'event_id' => $infoTicket->event->id,
                ])->toArray();

                $payment = [
                    ...$paymentInfo,
                    'payment_id' => $paymentInfo['id'],
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

    public function showUserEvents($user): JsonResponse
    {
        $paidEvents = $user->payments()->with('event')->get();

        return response()->json($paidEvents);
    }
}

<?php

namespace App\Services;

use App\Helpers\Constants;
use App\Helpers\PaymentHelper;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class EventService
{
    public PagarMeService $pagarMeService;
    public PaymentHelper $paymentHelper;
    public Ticket $ticket;

    public function __construct(
        PagarMeService $pagarMeService,
        PaymentHelper  $paymentHelper,
        Ticket         $ticket
    )
    {
        $this->pagarMeService = $pagarMeService;
        $this->paymentHelper = $paymentHelper;
        $this->ticket = $ticket;
    }

    public function findAll(): JsonResponse
    {
        $events = Event::with(['user', 'tickets'])->paginate(15);

        return response()->json($events);
    }

    public function show(?string $search, int $id): JsonResponse
    {
        $event = Event::searchEventWithTickets($search, $id);

        return response()->json($event);
    }

    public function buyTicket(array $data): JsonResponse
    {
        $payment = [];
        $infoTicket = [];

        foreach ($data['tickets'] as $ticket) {
            if (!$this->ticket->isAvailable($ticket['id'])) {
                return response()->json([
                    'error' => true,
                    'message' => 'Ingressos esgotados ou insuficientes.'
                ], 406);
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

                $credit_card = $this->pagarMeService->payWithCreditCard(Auth::user(), $data['tickets'], $card_info, $data['cpf'], $data['address']);

                $this->paymentHelper->formatPayment(
                    $data['tickets'],
                    $credit_card['amount'],
                    $credit_card['transaction_id'],
                    $infoTicket->event->id,
                    Constants::CARTAO_CREDITO,
                    $credit_card['last_digits']
                );
                break;
            case Constants::BOLETO:
                $billet = $this->pagarMeService->payWithBillet($data['tickets']);

                $paymentInfo = $this->paymentHelper->formatPayment(
                    $data['tickets'],
                    ($billet->amount / 100),
                    $billet->id,
                    $infoTicket->event->id,
                    Constants::BOLETO
                );

                $payment = [
                    ...$paymentInfo,
                    'payment_id' => $paymentInfo['id'],
                    'url' => $billet->url,
                ];
                break;
            case Constants::PIX:
                $pix = $this->pagarMeService->payWithPix($data['tickets']);

                $paymentInfo = $this->paymentHelper->formatPayment(
                    $data['tickets'],
                    ($pix->amount / 100),
                    $pix->id,
                    $infoTicket->event->id,
                    Constants::PIX
                );

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

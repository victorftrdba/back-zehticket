<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Payment;
use App\Helpers\Constants;

class EventService {
    public function findAll()
    {
        $events = Event::with('user')->paginate(15)->toArray();

        return $events;
    }

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

    public function buyTicket($request)
    {
        $request->validate([
            'total' => 'required',
            'payment_type' => 'required|integer',
            'card_number' => 'integer',
            'receipt' => 'string',
            'user_id' => 'required|integer',
            'event_id' => 'required|integer',
        ]);

        switch ($request->get('payment_type')) {
            case Constants::CARTAO_CREDITO:
                $card = substr($request->card_number, -4);

                return Payment::create([
                    'total' => $request->total,
                    'payment_type' => Constants::CARTAO_CREDITO,
                    'card_number' => $card,
                    'receipt' => 'sem integração com api por ora',
                    'user_id' => $request->user_id,
                    'event_id' => $request->event_id,
                ]);
            case Constants::BOLETO:
                return Payment::create([
                    'total' => $request->total,
                    'payment_type' => Constants::BOLETO,
                    'receipt' => 'sem integração com api por ora',
                    'user_id' => $request->user_id,
                    'event_id' => $request->event_id,
                ]);
            case Constants::TRANSFERENCIA:
                return Payment::create([
                    'total' => $request->total,
                    'payment_type' => Constants::TRANSFERENCIA,
                    'receipt' => 'sem integração com api por ora',
                    'user_id' => $request->user_id,
                    'event_id' => $request->event_id,
                ]);
            case Constants::PIX:
                return Payment::create([
                    'total' => $request->total,
                    'payment_type' => Constants::PIX,
                    'receipt' => 'sem integração com api por ora',
                    'user_id' => $request->user_id,
                    'event_id' => $request->event_id,
                ]);
            default:
                return [
                    'error' => 'Nenhuma opção selecionada!',
                ];
        }
    }

    public function showUserEvents($request)
    {
        $user = $request->user();

        $events = Payment::whereId($user->id)->with(['event'])->get();

        return $events;
    }
}
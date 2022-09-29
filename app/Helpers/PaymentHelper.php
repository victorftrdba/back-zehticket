<?php

namespace App\Helpers;

use App\Models\Payment;
use Illuminate\Support\Facades\Auth;

class PaymentHelper
{
    public function formatPayment($tickets, $amount, $receipt, $eventId, $type, ...$cardNumber): array
    {
        $paymentInfo = null;

        foreach ($tickets as $ticket) {
            $paymentInfo = Payment::create([
                'total' => $amount,
                'payment_type' => $type,
                'card_number' => $cardNumber,
                'receipt' => $receipt,
                'user_id' => Auth::user()->id,
                'event_id' => $eventId,
                'client_name' => $ticket['client_name'],
                'client_email' => $ticket['client_email'],
            ])->toArray();
        }

        return $paymentInfo;
    }
}

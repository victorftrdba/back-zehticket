<?php

namespace App\Console\Commands;

use App\Helpers\Constants;
use App\Mail\SendBoughtTicketsToUser;
use App\Models\PaidTicket;
use App\Models\Payment;
use App\Models\Ticket;
use App\Services\PagarMeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class VerifyPaidBilletAndPixCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verify:paid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command used to verify if the billet or pix is paid to send the tickets to the user e-mail.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Payment::with('event')->wherePaid(false)->whereNotNull('receipt')->orderBy('created_at', 'DESC')->get()->map(function ($payment) {
            $codes = [];
            $transaction = (new PagarMeService)->captureTransaction($payment->receipt);
            $transactionLink = (new PagarMeService)->captureTransactionLink($payment->receipt);

            if ($payment->payment_type === Constants::CARTAO_CREDITO && $transaction->status === 'paid') {
                collect($transaction->items)->map(function ($item) use ($codes, $payment) {
                    if ($item->venue === $payment->client_email) {
                        $boughtTicket = Ticket::find($item->id);
                        $boughtTicket->decrement('amount', $item->quantity);
                        $codes[] = PaidTicket::create([
                            'code' => Str::uuid(),
                            'event_id' => $boughtTicket->event->id,
                            'ticket_id' => $boughtTicket->id,
                        ]);
                        Mail::to(['address' => $payment->client_email])->send(new SendBoughtTicketsToUser($codes));
                    }
                });

                $payment->update([
                    'paid' => true
                ]);
            }

            if (in_array($payment->payment_type, [Constants::BOLETO, Constants::PIX], true)) {
                foreach ($transactionLink as $infoTransaction) {
                    if ($infoTransaction['status'] === 'paid') {
                        collect($infoTransaction['items'])->map(function ($infoTransactionItem) use ($codes, $payment) {
                            if (!PaidTicket::where('id', $infoTransactionItem['id'])->exists()) {
                                $boughtTicket = Ticket::find($infoTransactionItem['id']);
                                $boughtTicket->decrement('amount', $infoTransactionItem['quantity']);
                                $codes[] = PaidTicket::create([
                                    'code' => Str::uuid(),
                                    'event_id' => $boughtTicket->event->id,
                                    'ticket_id' => $boughtTicket->id,
                                ]);
                                Mail::to(['address' => $payment->client_email])->send(new SendBoughtTicketsToUser($codes));
                            }
                        });

                        $payment->update([
                            'paid' => true
                        ]);
                    }
                }
            }
        });
    }
}

<?php

namespace App\Console\Commands;

use App\Helpers\Constants;
use App\Models\Ticket;
use App\Models\Payment;
use App\Models\PaidTicket;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use App\Services\PagarMeService;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendBoughtTicketsToUser;
use App\Models\User;

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
            $user = User::find($payment->user_id);

            if ($payment->payment_type === Constants::CARTAO_CREDITO && $transaction->status === 'paid') {
                collect($transaction->items)->map(function ($item) use ($codes, $user) {
                    $boughtTicket = Ticket::find($item->id);
                    $boughtTicket->decrement('amount', $item->quantity);

                    for ($i = 0; $i < $item->quantity; $i++) {
                        $codes[] = PaidTicket::create([
                            'code' => Str::uuid(),
                            'event_id' => $boughtTicket->event->id,
                            'ticket_id' => $boughtTicket->id,
                        ]);
                    }

                    Mail::to($user)->send(new SendBoughtTicketsToUser($codes));
                });

                $payment->update([
                    'paid' => true
                ]);
            }

            if (in_array($payment->payment_type, [Constants::BOLETO, Constants::PIX])) {
                foreach ($transactionLink as $infoTransaction) {
                    if ($infoTransaction['status'] === 'paid') {
                        collect($infoTransaction['items'])->map(function ($infoTransactionItem) use ($codes, $user) {
                            $boughtTicket = Ticket::find($infoTransactionItem['id']);
                            $boughtTicket->decrement('amount', $infoTransactionItem['id']);

                            for ($i = 0; $i < $infoTransactionItem['quantity']; $i++) {
                                $codes[] = PaidTicket::create([
                                    'code' => Str::uuid(),
                                    'event_id' => $boughtTicket->event->id,
                                    'ticket_id' => $boughtTicket->id,
                                ]);
                            }

                            Mail::to($user)->send(new SendBoughtTicketsToUser($codes));
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

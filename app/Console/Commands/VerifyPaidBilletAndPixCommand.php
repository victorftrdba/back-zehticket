<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use App\Models\Payment;
use App\Models\PaidTicket;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use App\Services\PagarMeService;
use Illuminate\Support\Facades\Auth;
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
        Payment::with('event')->whereNotNull('receipt')->orderBy('created_at', 'DESC')->get()->map(function ($payment) {
            $codes = [];
            $transaction = (new PagarMeService)->captureTransaction($payment->receipt) ?? null;
            $user = User::find($payment->user_id);

            if (!is_null($transaction)) {
                if ($transaction->date_updated >= now() && $transaction->status === 'paid') {
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
                }
            }
        });
    }
}

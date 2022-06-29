<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaidTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'event_id',
        'ticket_id',
    ];

    public function event() {
        return $this->belongsTo(Event::class);
    }

    public function ticket() {
        return $this->belongsTo(Ticket::class);
    }
}
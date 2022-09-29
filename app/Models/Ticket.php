<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use HasFactory;

    public function isAvailable($id): bool
    {
        return self::findOrFail($id)->amount > 0;
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}

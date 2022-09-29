<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'value',
        'image',
        'address',
        'user_id',
        'date',
        'expires',
        'amount',
    ];

    protected $dates = [
        'date',
        'expires',
        'created_at',
        'updated_at',
    ];

    public static function searchEventWithTickets(?string $search, int $id): mixed
    {
        return self::with('tickets')
            ->when($search, function ($query, $value) {
                return $query->where('title', 'LIKE', "%{$value}%");
            })
            ->find($id);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}

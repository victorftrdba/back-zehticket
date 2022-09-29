<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public static function searchEventWithTickets(?string $search, $id)
    {
        return self::with('tickets')
            ->when($search, function ($query, $value) {
                return $query->where('title', 'LIKE', "%{$value}%");
            })
            ->find($id);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}

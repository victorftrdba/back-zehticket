<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'slug',
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
}

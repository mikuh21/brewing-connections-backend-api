<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoffeeTrailMarkerView extends Model
{
    protected $fillable = [
        'user_id',
        'establishment_id',
        'map_session_id',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function establishment()
    {
        return $this->belongsTo(Establishment::class);
    }
}

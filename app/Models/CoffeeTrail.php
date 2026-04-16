<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoffeeTrail extends Model
{
    protected $fillable = [
        'user_id',
        'origin_lat',
        'origin_lng',
        'preferences',
        'trail_data',
        'route_geometry',
        'total_distance_km',
        'total_duration_min',
    ];

    protected $casts = [
        'preferences' => 'array',
        'trail_data' => 'array',
        'origin_lat' => 'float',
        'origin_lng' => 'float',
        'total_distance_km' => 'float',
        'total_duration_min' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

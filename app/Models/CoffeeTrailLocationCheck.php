<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoffeeTrailLocationCheck extends Model
{
    protected $fillable = [
        'user_id',
        'coffee_trail_id',
        'establishment_id',
        'stop_sequence',
        'response',
        'establishment_name_snapshot',
        'establishment_address_snapshot',
        'review_status',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'stop_sequence' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function trail()
    {
        return $this->belongsTo(CoffeeTrail::class, 'coffee_trail_id');
    }

    public function establishment()
    {
        return $this->belongsTo(Establishment::class)->withTrashed();
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}

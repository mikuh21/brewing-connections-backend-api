<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstablishmentVariety extends Model
{
    protected $table = 'establishment_varieties';

    protected $fillable = [
        'establishment_id',
        'coffee_variety_id',
        'is_primary',
    ];

    /**
     * Establishment relationship.
     */
    public function establishment()
    {
        return $this->belongsTo(Establishment::class);
    }

    /**
     * Coffee variety relationship.
     */
    public function coffeeVariety()
    {
        return $this->belongsTo(CoffeeVariety::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoffeeVariety extends Model
{
    protected $table = 'coffee_varieties';

    protected $fillable = ['name', 'color', 'description'];

    /**
     * Establishments relationship (many-to-many).
     */
    public function establishments()
    {
        return $this->belongsToMany(Establishment::class, 'establishment_varieties', 'coffee_variety_id', 'establishment_id')->withPivot('is_primary');
    }

    /**
     * Establishment varieties relationship (has many).
     */
    public function establishmentVarieties()
    {
        return $this->hasMany(EstablishmentVariety::class);
    }

    public function resellers()
    {
        return $this->belongsToMany(User::class, 'reseller_varieties', 'coffee_variety_id', 'reseller_id')
            ->withPivot('is_primary')
            ->withTimestamps();
    }
}

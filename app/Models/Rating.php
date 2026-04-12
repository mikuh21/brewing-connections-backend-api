<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rating extends Model
{
    use SoftDeletes;

    protected $table = 'rating';

    protected $fillable = [
        'id',
        'user_id',
        'establishment_id',
        'taste_rating',
        'environment_rating',
        'cleanliness_rating',
        'service_rating',
        'overall_rating',
        'image',
        'owner_response',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'taste_rating' => 'integer',
        'environment_rating' => 'integer',
        'cleanliness_rating' => 'integer',
        'service_rating' => 'integer',
        'overall_rating' => 'decimal:2',
    ];

    /**
     * User relationship.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Establishment relationship.
     */
    public function establishment()
    {
        return $this->belongsTo(Establishment::class);
    }
}

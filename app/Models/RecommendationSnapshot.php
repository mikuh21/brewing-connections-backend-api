<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecommendationSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'establishment_id',
        'review_count',
        'generated_at',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
    ];

    public function establishment()
    {
        return $this->belongsTo(Establishment::class);
    }

    public function items()
    {
        return $this->hasMany(\App\Models\RecommendationSnapshotItem::class)->orderBy('category');
    }
}
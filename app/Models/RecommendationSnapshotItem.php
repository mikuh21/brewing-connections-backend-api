<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecommendationSnapshotItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'recommendation_snapshot_id',
        'category',
        'priority',
        'average_score',
        'insight',
        'suggested_action',
        'impact_score',
        'based_on_reviews',
        'generated_at',
    ];

    protected $casts = [
        'average_score' => 'float',
        'impact_score' => 'float',
        'generated_at' => 'datetime',
    ];

    public function snapshot()
    {
        return $this->belongsTo(\App\Models\RecommendationSnapshot::class, 'recommendation_snapshot_id');
    }
}
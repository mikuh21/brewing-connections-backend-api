<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponPromoRedemption extends Model
{
    protected $fillable = [
        'coupon_promo_id',
        'consumer_user_id',
        'establishment_id',
        'scanned_by_user_id',
        'redeemed_at',
    ];

    protected $casts = [
        'redeemed_at' => 'datetime',
    ];

    public function couponPromo()
    {
        return $this->belongsTo(CouponPromo::class);
    }

    public function consumer()
    {
        return $this->belongsTo(User::class, 'consumer_user_id');
    }

    public function establishment()
    {
        return $this->belongsTo(Establishment::class);
    }

    public function scanner()
    {
        return $this->belongsTo(User::class, 'scanned_by_user_id');
    }
}
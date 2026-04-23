<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class CouponPromo extends Model
{
    use SoftDeletes;

    protected $table = 'coupon_promos';

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'establishment_id',
        'title',
        'description',
        'discount_type',
        'discount_value',
        'qr_code_token',
        'valid_from',
        'valid_until',
        'max_usage',
        'used_count',
        'status'
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_until' => 'date',
        'discount_value' => 'decimal:2'
    ];

    public function establishment()
    {
        return $this->belongsTo(Establishment::class);
    }

    public function redemptions()
    {
        return $this->hasMany(CouponPromoRedemption::class);
    }

    public function getIsExpiredAttribute()
    {
        return $this->status === 'expired'
            || $this->valid_until < Carbon::today()
            || $this->used_count >= $this->max_usage;
    }

    public function getDisplayStatusAttribute()
    {
        return $this->is_expired ? 'expired' : $this->status;
    }

    public function getUsagePercentageAttribute()
    {
        return ($this->used_count / $this->max_usage) * 100;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->where('valid_from', '<=', Carbon::today())
                     ->where('valid_until', '>=', Carbon::today())
                     ->whereColumn('used_count', '<', 'max_usage')
                     ->whereNull('deleted_at');
    }

    public function scopeExpired($query)
    {
        return $query->where(function ($q) {
            $q->where('status', 'expired')
              ->orWhere('valid_until', '<', Carbon::today())
              ->orWhereColumn('used_count', '>=', 'max_usage');
        })->whereNull('deleted_at');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'category',
        'roast_level',
        'grind_type',
        'price_per_unit',
        'unit',
        'moq',
        'stock_quantity',
        'image_url',
        'seller_type',
        'seller_id',
        'establishment_id',
        'is_active'
    ];

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function establishment()
    {
        return $this->belongsTo(Establishment::class, 'establishment_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function bulkOrders()
    {
        return $this->hasMany(BulkOrder::class);
    }

    public function resellerProducts()
    {
        return $this->hasMany(ResellerProduct::class);
    }

    public function getSellerLabelAttribute()
    {
        return match($this->seller_type) {
            'farm_owner' => 'Farm Owner',
            'cafe_owner' => 'Café Owner',
            'reseller' => 'Reseller',
            default => 'Unknown'
        };
    }
}

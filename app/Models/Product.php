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

    protected static function booted(): void
    {
        static::saving(function (Product $product): void {
            $stock = max(0, (int) ($product->stock_quantity ?? 0));
            $product->stock_quantity = $stock;

            // Automatically mark products unavailable when stock is depleted.
            if ($stock === 0) {
                $product->is_active = false;
                return;
            }

            // If a previously out-of-stock product is replenished, bring it back.
            $originalStock = max(0, (int) ($product->getOriginal('stock_quantity') ?? 0));
            if ($originalStock === 0 && $product->isDirty('stock_quantity') && $product->is_active === false) {
                $product->is_active = true;
            }
        });
    }

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

    public function ratings()
    {
        return $this->hasMany(Rating::class, 'product_id');
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

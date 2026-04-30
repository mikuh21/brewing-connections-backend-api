<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
        'total_price',
        'status',
        'stock_reserved',
        'notes',
        'pickup_date',
        'pickup_time',
    ];

    protected $casts = [
        'pickup_date' => 'date',
        'stock_reserved' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productRating()
    {
        return $this->hasOne(Rating::class, 'order_id');
    }
}

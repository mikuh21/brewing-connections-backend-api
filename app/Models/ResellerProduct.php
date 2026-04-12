<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResellerProduct extends Model
{
    protected $fillable = [
        'product_id',
        'reseller_id',
        'reseller_price',
        'stock_quantity'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function reseller()
    {
        return $this->belongsTo(User::class, 'reseller_id');
    }
}

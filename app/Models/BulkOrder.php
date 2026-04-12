<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BulkOrder extends Model
{
    protected $fillable = [
        'reseller_id',
        'product_id',
        'quantity_kg',
        'total_price',
        'status',
        'delivery_date',
        'notes'
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'status' => 'string',
    ];

    public function reseller()
    {
        return $this->belongsTo(User::class, 'reseller_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

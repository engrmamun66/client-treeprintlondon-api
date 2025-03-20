<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $guarded = [];
    
    // An order item belongs to a product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // An order item belongs to an order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

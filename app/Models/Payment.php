<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'payment_id',
        'payer_id',
        'order_id',
        'amount',
        'currency',
        'payment_method',
        'status',
    ];
}

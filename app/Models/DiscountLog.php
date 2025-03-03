<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'category_id',
        'discount',
        'applied_to',
        'applied_at',
    ];

    protected $casts = [
        'applied_to' => 'array',
        'applied_at' => 'datetime',
    ];
}

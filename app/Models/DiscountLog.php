<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountLog extends Model
{
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
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $guarded = [];
    protected $appends = ['image_url'];
    public function getImageUrlAttribute()
    {
        return $this->image 
            ? asset('storage/' . $this->image)
            : null;
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

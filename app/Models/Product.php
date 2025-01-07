<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name', 'sku', 'category_id', 'brand_id', 'slug', 'original_image', 'thumbnail_image', 'status'
    ];
}

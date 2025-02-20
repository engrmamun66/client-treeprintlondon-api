<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; 
class Product extends Model
{
    protected $appends = ['thumbnail_image_url'];
    protected $fillable = [
        'name', 'sku', 'category_id', 'brand_id', 'slug', 'thumbnail_image', 'short_description', 'long_description', 'discount', 'status', 'min_unit_price', 'discounted_min_unit_price'
    ];
    protected static function booted()
    {
        static::creating(function ($product) {
            $product->sku = (string) Str::random(12); // Automatically generate a UUID
        });
    }
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
    public function colors()
    {
        return $this->hasMany(ProductColor::class, 'product_id');
    }
    public function sizes()
    {
        return $this->hasMany(ProductSize::class, 'product_id');
    }
    public function genders()
    {
        return $this->hasMany(ProductGender::class, 'product_id');
    }
   
    public function getThumbnailImageUrlAttribute()
    {
        return $this->thumbnail_image 
            ? asset('storage/' . $this->thumbnail_image)
            : null;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; 
class Product extends Model
{
    protected $appends = ['thumbnail_image_url'];
    protected $fillable = [
        'name', 'sku', 'category_id', 'brand_id', 'slug', 'thumbnail_image', 'short_description', 'long_description', 'status'
    ];
    protected static function booted()
    {
        static::creating(function ($product) {
            $product->sku = (string) Str::random(12); // Automatically generate a UUID
        });
    }
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
    public function colors()
    {
        return $this->hasMany(ProductColor::class);
    }
    public function sizes()
    {
        return $this->hasMany(ProductSize::class);
    }
    public function genders()
    {
        return $this->hasMany(ProductGender::class);
    }
   
    public function getThumbnailImageUrlAttribute()
    {
        return $this->thumbnail_image 
            ? asset('storage/' . $this->thumbnail_image)
            : null;
    }
}

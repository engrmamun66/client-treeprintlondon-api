<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'image', 'parent_id', 'status'];

    protected $appends = ['image_url'];

    // Define the parent category relationship
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // Define the child categories relationship
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function types()
    {
        return $this->belongsToMany(Type::class, 'category_types');
    }

    public function categoryTypes()
    {
        return $this->hasMany(CategoryType::class, 'category_id');
    }

    public function getImageUrlAttribute()
    {
        return $this->image 
            ? asset('storage/' . $this->image)
            : null;
    }
}

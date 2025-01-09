<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function getImageUrlAttribute()
    {
        return $this->image 
            ? Storage::disk('public')->url($this->image) 
            : null;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
class Post extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'featured_image',
        'is_published',
        'published_at',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'meta_image',
        'canonical_url'
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];


    public function scopePublished($query)
    {
        return $query->where('is_published', true)
                    ->where('published_at', '<=', now());
    }

    public function getExcerptAttribute($value)
    {
        return $value ?: Str::limit(strip_tags($this->content), 150);
    }

    public function getMetaDescriptionAttribute($value)
    {
        return $value ?: Str::limit(strip_tags($this->excerpt), 160);
    }

    public function getMetaTitleAttribute($value)
    {
        return $value ?: $this->title;
    }

    public function getMetaImageUrlAttribute()
    {
        if ($this->meta_image) {
            return $this->meta_image ? asset('storage/' . $this->meta_image) : null;
        }
         
        
        if ($this->featured_image) {
            return $this->featured_image ? asset('storage/' . $this->featured_image) : null;
        }
        
        return null;
    }
}

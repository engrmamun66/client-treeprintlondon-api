<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_types');
    }
}

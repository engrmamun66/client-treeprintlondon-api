<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; 

class Quotation extends Model
{
    protected $guarded = [];
    protected static function booted()
    {
        static::creating(function ($quotation) {
            $quotation->uuid = (string) Str::random(8); // Automatically generate a UUID
        });
    }
}

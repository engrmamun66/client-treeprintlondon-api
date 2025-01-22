<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationFile extends Model
{
    protected $guarded = [];
    protected $appends = ['file_url'];
    public function getFileUrlAttribute()
    {
        return $this->file 
            ? asset('storage/' . $this->file)
            : null;
    }
    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }
}

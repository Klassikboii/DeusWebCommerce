<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = ['id'];

    public function website()
    {
        return $this->belongsTo(Website::class);
    }
    
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
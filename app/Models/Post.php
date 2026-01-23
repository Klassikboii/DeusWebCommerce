<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $guarded = ['id'];

    public function website()
    {
        return $this->belongsTo(Website::class);
    }
    
}
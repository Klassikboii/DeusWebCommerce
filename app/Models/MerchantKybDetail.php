<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantKybDetail extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Relasi balik ke Website
    public function website()
    {
        return $this->belongsTo(Website::class);
    }
}
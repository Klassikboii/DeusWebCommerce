<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingMarkup extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_id',
        'city_id',
        'markup_type',
        'markup_value'
    ];

    public function website()
    {
        return $this->belongsTo(Website::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
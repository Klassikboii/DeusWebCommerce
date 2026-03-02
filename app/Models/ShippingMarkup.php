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
        'markup_type', // 'nominal' atau 'percent'
        'markup_value'
    ];

    // Relasi: Aturan ini milik 1 Toko/Website
    public function website()
    {
        return $this->belongsTo(Website::class);
    }

    // Relasi: Aturan ini berlaku untuk 1 Kota tujuan
    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
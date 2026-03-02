<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    // 🚨 PENTING: Matikan Auto-Increment
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'province_id',
        'type',
        'name',
        'postal_code'
    ];

    // Relasi: 1 Kota milik 1 Provinsi
    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    // Relasi: 1 Kota bisa punya banyak aturan Markup Ongkir dari berbagai toko
    public function shippingMarkups()
    {
        return $this->hasMany(ShippingMarkup::class);
    }
}
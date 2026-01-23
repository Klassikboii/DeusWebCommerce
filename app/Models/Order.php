<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = ['id'];

    // Relasi ke Item Belanja
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Relasi ke Toko
    public function website()
    {
        return $this->belongsTo(Website::class);
    }
}
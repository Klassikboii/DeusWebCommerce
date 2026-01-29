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
    public function histories()
    {
        return $this->hasMany(OrderHistory::class)->latest(); 
        // ->latest() agar saat dipanggil, urutannya dari yang terbaru (atas) ke terlama (bawah)
    }
}
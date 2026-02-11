<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = ['id'];

    public function website()
    {
        return $this->belongsTo(Website::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    // 1. Relasi ke Varian
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    // 2. Helper: Cek apakah produk ini punya variasi?
    // Berguna untuk logika di tampilan (Tampilkan tombol "Pilih Varian" atau langsung "Beli")
    public function hasVariants()
    {
        return $this->variants()->exists();
    }
    
    // 3. Helper: Ambil harga terendah (untuk display "Mulai dari Rp...")
    public function getMinPriceAttribute()
    {
        if ($this->hasVariants()) {
            return $this->variants()->min('price');
        }
        return $this->price;
    }
}
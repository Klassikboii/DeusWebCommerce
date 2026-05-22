<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WholesalePrice extends Model
{
    use HasFactory;

    protected $guarded = ['id']; // Memperbolehkan mass-assignment

    // Relasi balik ke Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Relasi balik ke Product Variant
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
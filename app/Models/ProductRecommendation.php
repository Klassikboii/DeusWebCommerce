<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductRecommendation extends Model
{
    protected $fillable = [
        'website_id',
        'product_id',
        'recommended_product_id',
        'support',
        'confidence',
        'lift'
    ];

    // Relasi ke Produk Asli (Yang sedang dilihat)
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // Relasi ke Produk yang Direkomendasikan
    public function recommendedProduct()
    {
        return $this->belongsTo(Product::class, 'recommended_product_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = ['id'];
    protected $fillable = [
        'website_id', 'category_id', 'name', 'slug', 'description', 
        'image', 'price', 'compare_price', 'stock', 'weight', 'sku', 
        'is_active', // <--- Tambahkan ini
        'velocity',
        'runway_days',
        'stock_status',
        'moving_class'
    ];
    // 🚨 TAMBAHKAN CASTS INI
    protected $casts = [
        'price_history' => 'array', 
    ];
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
    protected static function booted()
    {
        static::updating(function ($product) {
            // Mengecek apakah kolom 'price' sedang berusaha diubah
            if ($product->isDirty('price')) {
                
                $oldPrice = $product->getOriginal('price'); // Harga di DB sekarang
                $newPrice = $product->price;                // Harga baru yang mau di-save

                // Jika harga lama valid (bukan 0/baru dibuat) dan memang berbeda
                if ($oldPrice > 0 && $oldPrice != $newPrice) {
                    
                    // Tarik history yang ada, atau buat array kosong jika belum ada
                    $history = $product->price_history ?? [];
                    
                    // Masukkan harga lama dan waktu perubahannya ke URUTAN PALING ATAS
                    array_unshift($history, [
                        'price' => $oldPrice,
                        'changed_at' => now()->format('Y-m-d H:i:s'), // Format: 2026-04-28 14:00:00
                    ]);

                    // BATASI HISTORI: Ambil maksimal 5 histori saja agar database tidak bengkak
                    $history = array_slice($history, 0, 5);

                    // Simpan kembali ke kolom json
                    $product->price_history = $history;
                }
            }
        });
    }
}
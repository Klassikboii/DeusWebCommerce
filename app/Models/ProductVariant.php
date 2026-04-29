<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // PENTING: Mengubah kolom JSON 'options' menjadi array PHP otomatis
    // Jadi Anda bisa panggil: $variant->options['Warna']
    protected $fillable = [
        'product_id', 'name', 'sku', 'price', 'compare_price', 
        'stock', 'weight', 'options', 
        'is_active', // <--- Tambahkan ini
        'image'
    ];
    protected $casts = [
        'options' => 'array',
        'price' => 'decimal:2',
        'price_history' => 'array',
    ];

    // Relasi Kebalikannya: Varian ini milik Produk siapa?
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    // Helper Accessor: Untuk menampilkan nama lengkap
    // Contoh output: "Kaos Polos (Merah - L)"
    public function getFullNameAttribute()
    {
        // Gabungkan opsi menjadi string, misal "Merah - L"
        $optionsString = implode(' - ', array_values($this->options ?? []));
        
        return $this->name 
            ? $this->name 
            : ($this->product->name . ' (' . $optionsString . ')');
    }
    // 2. Tambahkan Booted Event (Jurus Ninja)
    protected static function booted()
    {
        static::updating(function ($variant) {
            if ($variant->isDirty('price')) {
                
                $oldPrice = $variant->getOriginal('price'); 
                $newPrice = $variant->price;                

                if ($oldPrice > 0 && $oldPrice != $newPrice) {
                    $history = $variant->price_history ?? [];
                    
                    array_unshift($history, [
                        'price' => $oldPrice,
                        'changed_at' => now()->format('Y-m-d H:i:s'),
                    ]);

                    $history = array_slice($history, 0, 5);

                    $variant->price_history = $history;
                }
            }
        });
    }
}
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
    protected $casts = [
        'options' => 'array',
        'price' => 'decimal:2',
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
}
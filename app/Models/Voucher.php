<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_id', 'code', 'discount_type', 'discount_value', 
        'max_discount_amount', 'min_purchase', 'max_uses', 'used_count',
        'valid_from', 'valid_until', 'target_rfm_segment', 'target_customer_id', 'is_active'
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Relasi ke Website (Toko)
    public function website()
    {
        return $this->belongsTo(Website::class);
    }

    // Relasi ke Pelanggan Spesifik (Jika ada)
    public function targetCustomer()
    {
        return $this->belongsTo(Customer::class, 'target_customer_id');
    }

    // Relasi ke Pesanan (Untuk melihat riwayat pemakaian)
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // --- Helper Function (Otomatis cek validitas voucher) ---
    public function isValid()
    {
        if (!$this->is_active) return false;
        
        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) return false;
        
        $now = now();
        if ($this->valid_from && $now->lt($this->valid_from)) return false;
        if ($this->valid_until && $now->gt($this->valid_until)) return false;
        
        return true;
    }
}
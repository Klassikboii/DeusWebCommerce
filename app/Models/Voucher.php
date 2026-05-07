<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
    use HasFactory;
    use SoftDeletes;

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
        // 1. Cek apakah statusnya nonaktif
        if (!$this->is_active) {
            return false;
        }

        $now = now(); // Mengambil waktu server saat ini

        // 2. Cek apakah waktu MULAI (valid_from) sudah terlewati
        if ($this->valid_from && $now->lessThan($this->valid_from)) {
            return false; // Ditolak: Event diskon belum mulai
        }

        // 3. Cek apakah waktu BERAKHIR (valid_until) sudah kelewat
        if ($this->valid_until && $now->greaterThan($this->valid_until)) {
            return false; // Ditolak: Sudah expired
        }

        // 4. Cek apakah kuota sudah habis
        // (Pastikan kolom database Anda bernama max_uses, bukan quota. Sesuaikan jika beda)
        if ($this->max_uses > 0 && $this->used_count >= $this->max_uses) {
            return false; // Ditolak: Kuota habis
        }

        return true; // Jika lolos semua, berarti valid!
    }
}
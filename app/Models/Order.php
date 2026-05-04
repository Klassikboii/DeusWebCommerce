<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = ['id'];
    // protected $fillable = ['payment_status', 'bank_name', 'payment_proof']; // Tambahkan fillable untuk payment_status agar bisa diupdate
    // protected $fillable = ['payment_url', 'snap_token', 'website_id'];
    protected $fillable = [
        'website_id',
        'order_number',
        'customer_name',
        'customer_whatsapp',
        'customer_address',
        'shipping_cost',
        'courier_name',
        'courier_service',
        'payment_status',
        'total_amount',
        'status',
        'payment_proof',
        'bank_name',
        'snap_token',
        'payment_url', // Kolom Pivot yang baru
        'customer_id',
        'accurate_customer_no',
    ];
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
    // 🚨 TAMBAHKAN RELASI BARU KE CUSTOMER
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
}
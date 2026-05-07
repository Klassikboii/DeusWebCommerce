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
        'payment_method',
        'voucher_id'
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
    /**
     * Cairkan dana ke dompet Klien
     */
    public function releaseFundToWallet()
    {
        // 1. Cek apakah ini transaksi Manual Transfer?
        // Jika Manual, uang sudah ditransfer langsung ke rekening Klien oleh pembeli. Jangan tambah saldo!
        if (strtolower($this->payment_method) === 'transfer' || strtolower($this->payment_method) === 'manual') {
            return false; 
        }

        // 2. Mencegah Uang Dobel (Cek apakah pesanan ini sudah pernah cair ke saldo?)
        $alreadyReleased = \App\Models\WalletMutation::where('order_id', $this->id)
                            ->where('type', 'credit')
                            ->exists();

        if ($alreadyReleased) {
            return false;
        }

        // 3. Hitung Nominal (Di sini Anda bisa menambahkan logika potongan Fee Deus jika mau)
        // Contoh: $feeDeus = $this->total_amount * 0.02; (Potong 2%)
        $amountToRelease = $this->total_amount;

        // 4. Catat di Buku Tabungan (Mutasi)
        \App\Models\WalletMutation::create([
            'website_id' => $this->website_id,
            'order_id' => $this->id,
            'type' => 'credit',
            'amount' => $amountToRelease,
            'description' => "Dana pesanan {$this->order_number} telah masuk ke saldo."
        ]);

        // 5. Tambah Saldo Tersedia milik Klien di tabel Websites
        $this->website->increment('wallet_balance', $amountToRelease);

        return true;
    }
}
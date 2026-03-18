<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Website extends Model
{
    // Agar kita bisa isi semua kolom secara massal (mass assignment)
    protected $guarded = ['id'];

   protected $fillable = [
        // 1. Identitas Utama
        'user_id', 
        'subdomain', 
        'custom_domain', 
        'site_name',
        'active_template',
        'is_open',
        
        // 2. Aset Gambar
        'logo', 
        'favicon', 
        'icon',      // <-- Untuk Settings

        // // 3. Tampilan & Builder (YANG BIKIN MASALAH BUILDER)
        // 'primary_color', 
        // 'secondary_color', 
        // 'hero_bg_color',       // <-- PENTING: Background Banner
        // 'font_family',         // <-- PENTING: Font
        // 'base_font_size',      // <-- PENTING: Ukuran Font
        // 'product_image_ratio', // <-- PENTING: Rasio Gambar

        // 4. Konten Hero / Banner
        'hero_image', 
        // 'hero_title', 
        // 'hero_subtitle', 
        // 'hero_btn_text', 
        // 'hero_btn_url',
        
        // 5. Data JSON (Menu & Section)
        'sections', 
        'navigation_menu',
        'theme_config',

        // 6. Kontak & Alamat (YANG BIKIN MASALAH SETTINGS)
        'whatsapp_number', 
        'email_contact', 
        'address',

        'courier_name',      // <--- TAMBAHKAN INI
        'courier_service',

        // 7. SEO
        'meta_title', 
        'meta_description', 
        'meta_keywords',

        'bank_name',
        'bank_account_number',
        'bank_account_holder',

        'city_id',

        'midtrans_client_key',
        'midtrans_server_key',
        'midtrans_is_production',
        'active_couriers', // 🚨 TAMBAHKAN BARIS INI
        
    ];

    protected $casts = [
        'sections' => 'array',
        'navigation_menu' => 'array', // <--- HAPUS KOMENTARNYA (Aktifkan)
        'is_open' => 'boolean',
        'theme_config' => 'array',
        'active_couriers' => 'array', // 🚨 WAJIB ADA AGAR BISA DIBACA SEBAGAI ARRAY
    ];

    // Relasi: Website milik satu User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // Relasi: Website menggunakan satu Template
    public function template()
    {
        return $this->belongsTo(WebsiteTemplate::class);
    }

    // ... di dalam class Website ...
    
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    // ... di dalam class Website ...

    public function activeSubscription()
    {
        // Ambil langganan terakhir yang statusnya active
        return $this->hasOne(Subscription::class)
        ->where('status', 'active')->where(function($q) {
                    $q->whereNull('ends_at')
                      ->orWhere('ends_at', '>', now());
                })->latest();
    }
    // Relasi ke Ongkir
    public function shippingRates()
    {
        return $this->hasMany(ShippingRate::class);
    }
    // Ambil 1 langganan yang saat ini sedang aktif
    public function subscription()
    {
        return $this->hasOne(Subscription::class)->latest(); 
        // (Atau sesuaikan jika Anda punya kolom status: ->where('status', 'active'))
    }

    // Helper: Ambil daftar kota unik yang dimiliki toko ini (Untuk Dropdown Checkout)
    public function getAvailableCitiesAttribute()
    {
        return $this->shippingRates()
                    ->select('destination_city')
                    ->distinct()
                    ->orderBy('destination_city', 'asc')
                    ->pluck('destination_city');
    }
    // ... relasi lain yang sudah ada ...

    // TAMBAHKAN INI: Relasi ke tabel integrasi Accurate
    public function accurateIntegration()
    {
        return $this->hasOne(AccurateIntegration::class);
    }
    // Relasi ke tabel pengaturan keuntungan ongkir
    public function shippingMarkups()
    {
        return $this->hasMany(ShippingMarkup::class);
    }
    // Tambahkan relasi ini
    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }
    // 🚨 Atribut Siluman: active_domain
    public function getActiveDomainAttribute()
    {
        // Logika: Jika custom_domain tidak kosong, kembalikan custom_domain.
        // Jika kosong, kembalikan subdomain.
        return !empty($this->custom_domain) ? $this->custom_domain : $this->subdomain;
    }
}
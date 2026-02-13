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
        
        // 2. Aset Gambar
        'logo', 
        'favicon', 
        'icon',      // <-- Untuk Settings

        // 3. Tampilan & Builder (YANG BIKIN MASALAH BUILDER)
        'primary_color', 
        'secondary_color', 
        'hero_bg_color',       // <-- PENTING: Background Banner
        'font_family',         // <-- PENTING: Font
        'base_font_size',      // <-- PENTING: Ukuran Font
        'product_image_ratio', // <-- PENTING: Rasio Gambar

        // 4. Konten Hero / Banner
        'hero_image', 
        'hero_title', 
        'hero_subtitle', 
        'hero_btn_text', 
        'hero_btn_url',
        
        // 5. Data JSON (Menu & Section)
        'sections', 
        'navigation_menu',

        // 6. Kontak & Alamat (YANG BIKIN MASALAH SETTINGS)
        'whatsapp_number', 
        'email_contact', 
        'address',

        // 7. SEO
        'meta_title', 
        'meta_description', 
        'meta_keywords',

        'latitude', 'longitude'
    ];

    protected $casts = [
        'sections' => 'array',
        'navigation_menu' => 'array', // <--- HAPUS KOMENTARNYA (Aktifkan)
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

    // Helper: Ambil daftar kota unik yang dimiliki toko ini (Untuk Dropdown Checkout)
    public function getAvailableCitiesAttribute()
    {
        return $this->shippingRates()
                    ->select('destination_city')
                    ->distinct()
                    ->orderBy('destination_city')
                    ->pluck('destination_city');
    }

    public function shippingRanges()
    {
        return $this->hasMany(ShippingRange::class)->orderBy('min_km', 'asc');
    }
}
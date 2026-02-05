<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Website extends Model
{
    // Agar kita bisa isi semua kolom secara massal (mass assignment)
    protected $guarded = ['id'];

    protected $fillable = [
        'user_id', 'subdomain', 'site_name', 
        'primary_color', 'secondary_color', 
        'active_template', 'sections',
        'meta_title', 'meta_description', 'meta_keywords',
        'custom_domain' // <--- TAMBAHKAN INI
    ];
    protected $casts = [
        'sections' => 'array', // <--- TAMBAHKAN INI
        'navigation_menu' => 'array', // <--- Otomatis ubah JSON jadi Array
        // casts lain jika ada...
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
}
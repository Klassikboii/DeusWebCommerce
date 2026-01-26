<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Website extends Model
{
    // Agar kita bisa isi semua kolom secara massal (mass assignment)
    protected $guarded = ['id'];

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
        return $this->hasOne(Subscription::class)->where('status', 'active')->latest();
    }
}
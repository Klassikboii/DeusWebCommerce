<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    // Tambahkan 'slug' ke dalam sini
    protected $fillable = [
        'name',
        'slug', // <--- WAJIB DITAMBAHKAN
        'price',
        'duration_days',
        'description',
         // <--- Pastikan ini ada
        'features',
        'max_products',
        'can_custom_domain',
        'remove_branding',
        'has_ai_insights', 'has_custom_dashboard', 'has_shipping_markup'
    ];

    // Casting features array agar otomatis jadi JSON saat diambil
    protected $casts = [
        'features' => 'array',
        'can_custom_domain' => 'boolean',
        'remove_branding' => 'boolean',
        'has_ai_insights' => 'boolean',
        'has_custom_dashboard' => 'boolean',
        'has_shipping_markup' => 'boolean',
    ];
}
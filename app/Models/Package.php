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
        'description', // <--- Pastikan ini ada
        'features',
        'max_products',
        'can_custom_domain',
        'remove_branding',
    ];

    // Casting features array agar otomatis jadi JSON saat diambil
    protected $casts = [
        'features' => 'array',
    ];
}
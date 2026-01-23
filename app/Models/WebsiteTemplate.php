<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteTemplate extends Model
{
    // Mengizinkan semua kolom diisi (mass assignment)
    protected $guarded = ['id'];

    // Relasi: Satu template bisa digunakan oleh banyak website
    public function websites()
    {
        return $this->hasMany(Website::class);
    }
}
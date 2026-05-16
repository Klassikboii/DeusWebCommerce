<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantKybDetail extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Relasi balik ke Website
   // Hapus public function website() dan ganti menjadi:
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
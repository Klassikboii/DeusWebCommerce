<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    use HasFactory;

    // 🚨 PENTING: Matikan Auto-Increment karena kita pakai ID dari RajaOngkir
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'id', 
        'name'
    ];

    // Relasi: 1 Provinsi punya Banyak Kota
    public function cities()
    {
        return $this->hasMany(City::class);
    }
}
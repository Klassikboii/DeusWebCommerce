<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PivotCountry extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database yang terhubung dengan model ini.
     * (Laravel otomatis menebak 'pivot_countries', tapi mendeklarasikannya secara eksplisit adalah best practice).
     */
    protected $table = 'pivot_countries';

    /**
     * Kolom-kolom yang TIDAK BOLEH diisi secara massal (Mass Assignment).
     * Dengan mengosongkan id di guarded, berarti kolom 'code' dan 'name' 
     * diizinkan untuk diisi otomatis oleh Seeder.
     */
    protected $guarded = ['id'];

    // ATAU jika Anda tim yang lebih suka memakai $fillable, gunakan ini:
    // protected $fillable = ['code', 'name'];
}
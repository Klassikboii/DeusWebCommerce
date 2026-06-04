<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Container;
use Illuminate\Support\Facades\DB;
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
    public function getMccNameAttribute()
    {
        // Jika belum ada data
        if (!$this->mcc) return '-';
        
        // Langsung gabungkan kolom yang sudah kita simpan tadi!
        if ($this->parent_industry && $this->child_industry) {
            return $this->parent_industry . ' > ' . $this->child_industry . ' (' . $this->mcc . ')';
        }

        return "MCC ({$this->mcc})";
    }

    /**
     * Menerjemahkan ID Kecamatan menjadi Nama Kecamatan Lengkap
     */
    public function getDistrictNameAttribute()
    {
        if (!$this->district_id) return '-';
        
        // Sesuaikan dengan tabel database kecamatan Anda
        $reference = DB::table('pivot_districts')
                        ->where('id', $this->district_id)
                        // ->where('type', 'district') // Hapus baris ini jika tidak membedakan tipe
                        ->first();
                        
        return $reference ? $reference->name : "Kecamatan ID: {$this->district_id}";
    }
    public function getCountryNameAttribute()
    {
        if (!$this->country_of_entity) return '-';
        
        $country = DB::table('pivot_countries')->where('code', $this->country_of_entity)->first();
        
        return $country ? $country->name . ' (' . $country->code . ')' : $this->country_of_entity;
    }
    
}
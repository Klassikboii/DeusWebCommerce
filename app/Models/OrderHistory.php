<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderHistory extends Model
{
    use HasFactory;

    // Pastikan kolom ini sesuai dengan migrasi yang kita buat tadi
    protected $fillable = [
        'order_id', 
        'status', 
        'note'
    ];

    // Relasi balik ke Order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
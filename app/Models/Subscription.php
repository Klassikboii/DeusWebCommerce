<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    // Relasi ke Paket (Agar kita tahu limitnya berapa)
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
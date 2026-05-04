<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable; // 🚨 UBAH EXTENDS-NYA
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'website_id',
        'name',
        'email',
        'whatsapp',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Relasi: Satu customer memiliki banyak order
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    public function targetedVouchers()
    {
        return $this->hasMany(Voucher::class, 'target_customer_id');
    }
}
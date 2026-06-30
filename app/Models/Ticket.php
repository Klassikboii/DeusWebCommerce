<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'user_id',
        'website_id',
        'subject',
        'description',
        'status',
        'admin_reply'
    ];

    // Relasi ke User (Klien yang komplain)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Website (Toko mana yang bermasalah)
    public function website()
    {
        return $this->belongsTo(Website::class);
    }
}
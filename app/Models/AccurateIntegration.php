<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccurateIntegration extends Model
{
    protected $fillable = [
        'website_id',
        'accurate_database_id',
        'access_token',
        'refresh_token',
        'token_expires_at',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
    ];

    public function website()
    {
        return $this->belongsTo(Website::class);
    }
}
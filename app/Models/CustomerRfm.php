<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerRfm extends Model
{
    protected $fillable = [
        'website_id', 'customer_id', 
        'recency_days', 'frequency_count', 'monetary_value',
        'r_score', 'f_score', 'm_score', 'segment'
    ];

    public function website()
    {
        return $this->belongsTo(Website::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletMutation extends Model
{
    protected $fillable = ['website_id', 'order_id', 'type', 'amount', 'description'];

    public function website() {
        return $this->belongsTo(Website::class);
    }

    public function order() {
        return $this->belongsTo(Order::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    protected $fillable = ['website_id', 'amount', 'bank_name', 'bank_account_number', 'bank_account_name', 'status', 'transfer_proof', 'admin_note'];

    public function website() {
        return $this->belongsTo(Website::class);
    }
}
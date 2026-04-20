<?php

namespace App\Services\Payment;

use App\Models\Website;
use Exception;

class PaymentFactory
{
    public static function make(Website $website)
    {
        // Karena bos meminta pindah total ke Pivot, kita arahkan langsung ke PivotService
        // Jika nanti ada banyak pilihan (Midtrans/Pivot/Xendit), kita bisa pakai if-else berdasarkan setting klien.
        
        return new PivotService($website);
    }
}
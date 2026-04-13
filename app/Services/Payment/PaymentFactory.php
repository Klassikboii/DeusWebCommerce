<?php

namespace App\Services\Payment;

use App\Models\Website;
use App\Contracts\PaymentGatewayInterface;

class PaymentFactory
{
    public static function make(Website $website): PaymentGatewayInterface
    {
        // Asumsikan Anda menambahkan kolom 'active_payment_gateway' di tabel websites
        $gateway = $website->active_payment_gateway ?? 'midtrans';

        if ($gateway === 'pivot') {
            return new PivotService($website);
        }

        // Default kembalikan Midtrans
        return new MidtransService($website);
    }
}
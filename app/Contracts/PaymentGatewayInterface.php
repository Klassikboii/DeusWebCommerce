<?php

namespace App\Contracts;

use App\Models\Order;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    /**
     * Fungsi untuk membuat tagihan/invoice ke payment gateway
     * Wajib mengembalikan array berisi URL pembayaran atau Token
     */
    public function createTransaction(Order $order): array;

    /**
     * Fungsi untuk memproses data webhook yang masuk
     * Wajib mengembalikan array berisi ID Order dan Status Pembayaran
     */
    public function handleWebhook(Request $request): array;
}
<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\Website;
use Illuminate\Http\Request;

class MidtransService implements PaymentGatewayInterface
{
    protected $website;

    public function __construct(Website $website)
    {
        $this->website = $website;

        // 🚨 TAMBAHAN WAJIB: Konfigurasi Midtrans Dinamis per Toko
        if (!empty($this->website->midtrans_server_key)) {
            \Midtrans\Config::$serverKey = $this->website->midtrans_server_key;
            
            // Asumsi Anda punya kolom midtrans_is_production (boolean). 
            // Jika tidak ada, bisa hardcode false dulu selama masa testing.
            \Midtrans\Config::$isProduction = $this->website->midtrans_is_production ?? false; 
            
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;
        }
    }

    public function createTransaction(Order $order): array
    {
        // 1. Cek Kredensial (Aman dari toko yang belum pasang key)
        if (empty($this->website->midtrans_server_key)) {
            \Illuminate\Support\Facades\Log::error("Toko {$this->website->site_name} tidak memiliki Server Key Midtrans.");
            return ['status' => 'error', 'token' => null, 'redirect_url' => null];
        }

        // 2. Siapkan Parameter Tagihan (Persis kode lama Anda)
        $params = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => (int) ($order->total_amount + $order->shipping_cost),
            ],
            'customer_details' => [
                'first_name' => $order->customer_name,
                'phone' => $order->customer_whatsapp,
            ],
            'callbacks' => [
                'finish' => url()->route('store.payment', ['order_number' => $order->order_number]),
                'error' => url()->route('store.payment', ['order_number' => $order->order_number]),
                'close' => url()->route('store.payment', ['order_number' => $order->order_number])
            ]
        ];

        // 3. Minta Token ke Server Midtrans
        try {
            $snapToken = \Midtrans\Snap::getSnapToken($params);
            
            // 🚨 KUNCI PERBAIKAN: Kembalikan array dengan 'status' => 'success'
            return [
                'status' => 'success', 
                'token' => $snapToken, 
                'redirect_url' => null // Midtrans Snap tidak butuh redirect_url, dia pakai JS pop-up
            ];
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gagal Generate Midtrans Snap: ' . $e->getMessage());
            
            // Kembalikan status error jika gagal
            return ['status' => 'error', 'token' => null, 'redirect_url' => null];
        }
    }

    public function handleWebhook(Request $request): array
    {
        $payload = $request->all();
        
        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $paymentType = $payload['payment_type'] ?? null;

        $serverKey = $this->website->midtrans_server_key;

        if (!$serverKey) {
            return ['is_valid' => false, 'message' => 'Server key missing'];
        }

        // Validasi Signature
        $calculatedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        if ($calculatedSignature !== $signatureKey) {
            return ['is_valid' => false, 'message' => 'Invalid signature'];
        }

        // Menerjemahkan Status Midtrans ke Status Universal
        $finalStatus = 'pending';
        if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
            $finalStatus = 'paid';
        } elseif ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
            $finalStatus = 'failed';
        }

        // Menerjemahkan Metode Pembayaran (Sesuai kode asli Anda)
        $metodePembayaran = 'Midtrans Otomatis'; 
        if ($paymentType == 'bank_transfer') {
            $vaNumber = $payload['va_numbers'][0]['bank'] ?? '';
            $metodePembayaran = 'Virtual Account ' . strtoupper($vaNumber);
        } elseif ($paymentType == 'echannel') {
            $metodePembayaran = 'Mandiri Bill Payment';
        } elseif ($paymentType == 'qris') {
            $metodePembayaran = 'QRIS (Gopay/OVO/Dana/LinkAja)';
        } elseif ($paymentType == 'gopay') {
            $metodePembayaran = 'GoPay';
        } elseif ($paymentType == 'shopeepay') {
            $metodePembayaran = 'ShopeePay';
        }

        // Kembalikan format standar yang seragam
        return [
            'is_valid' => true,
            'order_id' => $orderId,
            'status' => $finalStatus,
            'payment_method' => $metodePembayaran
        ];
    }
}
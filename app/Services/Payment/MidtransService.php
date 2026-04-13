<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransService implements PaymentGatewayInterface
{
    protected $website;

    public function __construct(Website $website)
    {
        $this->website = $website;
        
        if (!empty($this->website->midtrans_server_key)) {
            \Midtrans\Config::$serverKey = $this->website->midtrans_server_key;
            \Midtrans\Config::$isProduction = $this->website->midtrans_is_production ? true : false;
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;
        }
    }

    public function createTransaction(Order $order): array
    {
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

        try {
            $snapToken = \Midtrans\Snap::getSnapToken($params);
            return ['status' => 'success', 'token' => $snapToken, 'redirect_url' => null];
        } catch (\Exception $e) {
            Log::error('Gagal Generate Midtrans Snap: ' . $e->getMessage());
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

        // Validasi Signature
        $serverKey = $this->website->midtrans_server_key;
        $calculatedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        
        if ($calculatedSignature !== $signatureKey) {
            return ['is_valid' => false, 'message' => 'Invalid signature'];
        }

        // Tentukan Status Final
        $finalStatus = 'pending';
        if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
            $finalStatus = 'paid';
        } elseif ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
            $finalStatus = 'failed';
        }

        // Detektif Metode Pembayaran
        $metodePembayaran = 'Midtrans Otomatis';
        if ($paymentType == 'bank_transfer') {
            $vaNumber = $payload['va_numbers'][0]['bank'] ?? '';
            $metodePembayaran = 'Virtual Account ' . strtoupper($vaNumber);
        } elseif ($paymentType == 'echannel') {
            $metodePembayaran = 'Mandiri Bill Payment';
        } elseif ($paymentType == 'qris') {
            $metodePembayaran = 'QRIS';
        } elseif (in_array($paymentType, ['gopay', 'shopeepay'])) {
            $metodePembayaran = ucfirst($paymentType);
        }

        return [
            'is_valid' => true,
            'order_id' => $orderId,
            'status' => $finalStatus,
            'payment_method' => $metodePembayaran
        ];
    }
}
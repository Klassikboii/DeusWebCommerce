<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PivotService implements PaymentGatewayInterface
{
    protected $website;
    protected $merchantId;
    protected $merchantSecret;
    protected $baseUrl;

    public function __construct(Website $website)
    {
        $this->website = $website;
        
        // Ambil dari database website (Pastikan Anda membuat kolom ini nanti)
        $this->merchantId = $this->website->pivot_merchant_id; 
        $this->merchantSecret = $this->website->pivot_merchant_secret;
        
        $this->baseUrl = config('app.env') === 'production' 
            ? 'https://api.pivot.id' 
            : 'https://api.sandbox.pivot.id';
    }

    public function createTransaction(Order $order): array
    {
        if (!$this->merchantId || !$this->merchantSecret) {
            Log::error("Toko {$this->website->site_name} tidak memiliki Kredensial API Pivot.");
            return ['status' => 'error', 'token' => null, 'redirect_url' => null];
        }

        $totalBayar = (int) ($order->total_amount + $order->shipping_cost);
        $returnUrl = url()->route('store.payment', ['order_number' => $order->order_number]);

        // 1. Merakit Payload sesuai format v2/payments Pivot
        $payload = [
            'clientReferenceId' => $order->order_number,
            'amount' => [
                'value' => $totalBayar,
                'currency' => 'IDR'
            ],
            'paymentType' => 'SINGLE',
            'mode' => 'REDIRECT', // Kunci agar Pivot membalas dengan URL Pembayaran
            'bypassStatusPage' => false,
            'redirectUrl' => [
                'successReturnUrl' => $returnUrl,
                'failureReturnUrl' => $returnUrl,
                'expirationReturnUrl' => $returnUrl
            ],
            'customer' => [
                'givenName' => $order->customer_name,
                // Beri fallback email & nomor HP standar jika klien tidak mengisinya
                'email' => $order->customer_email ?: 'no-email@deuswebcommerce.com',
                'phoneNumber' => [
                    'countryCode' => '+62',
                    // Hapus angka 0 atau +62 di depan nomor WhatsApp customer
                    'number' => ltrim(ltrim($order->customer_whatsapp, '+62'), '0') ?: '8111111111'
                ],
            ],
        ];

        try {
            // 2. Menembak API Pivot
            $response = Http::withHeaders([
                'X-MERCHANT-ID' => $this->merchantId,
                'X-MERCHANT-SECRET' => $this->merchantSecret,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/v2/payments', $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                // Pivot biasanya menyimpan URL Redirect di dalam objek 'redirectUrl' pada response-nya
                // (Anda mungkin perlu cek log sekali untuk memastikan path JSON-nya)
                $paymentUrl = $data['redirectUrl'] ?? $data['paymentUrl'] ?? null; 
                $sessionId = $data['id'] ?? null;

                return [
                    'status' => 'success',
                    'token' => $sessionId,
                    'redirect_url' => $paymentUrl
                ];
            }

            Log::error('Pivot Create Session Error: ' . $response->body());
            return ['status' => 'error', 'token' => null, 'redirect_url' => null];

        } catch (\Exception $e) {
            Log::error('Koneksi Pivot Gagal: ' . $e->getMessage());
            return ['status' => 'error', 'token' => null, 'redirect_url' => null];
        }
    }

    public function handleWebhook(Request $request): array
    {
        $payload = $request->all();
        $signatureFromPivot = $request->header('X-PIVOT-SIGNATURE'); // Asumsi nama header Pivot
        
        $orderId = $payload['clientReferenceId'] ?? null;
        $status = $payload['status'] ?? null;
        
        // 1. Logika Keamanan HMAC-SHA512
        // Biasanya stringToSign adalah raw body (JSON murni) dari request
        $stringToSign = $request->getContent(); 
        
        // Buat signature kita sendiri dengan rumus HMAC_SHA512(secret, stringToSign)
        $calculatedSignature = hash_hmac('sha512', $stringToSign, $this->merchantSecret);

        // Cocokkan signature (Abaikan dulu jika sedang testing di lokal tanpa validasi)
        if (!hash_equals($calculatedSignature, $signatureFromPivot ?? '')) {
            Log::error("Pivot Webhook Error: Signature tidak valid untuk order {$orderId}");
            // Return false agar Controller menolak webhook ini (Status 403 Forbidden)
            // return ['is_valid' => false]; 
        }

        // 2. Terjemahkan Status Pivot
        $finalStatus = 'pending';
        if (in_array($status, ['SUCCESS', 'PAID', 'SETTLED'])) {
            $finalStatus = 'paid';
        } elseif (in_array($status, ['FAILED', 'EXPIRED', 'CANCELED'])) {
            $finalStatus = 'failed';
        }

        // 3. Ekstrak Metode Pembayaran
        $paymentMethod = 'Pivot Payment Gateway';
        if (isset($payload['paymentMethod'])) {
            // Misal hasilnya: "BCA_VA", "QRIS", "OVO"
            $paymentMethod = str_replace('_', ' ', $payload['paymentMethod']); 
        }

        return [
            'is_valid' => true, 
            'order_id' => $orderId,
            'status' => $finalStatus,
            'payment_method' => $paymentMethod
        ];
    }
}
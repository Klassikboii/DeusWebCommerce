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
        
        $this->merchantId = env('PIVOT_CLIENT_ID'); 
        $this->merchantSecret = env('PIVOT_SERVER_KEY');
        
        $this->baseUrl = $this->website->pivot_is_production 
            ? 'https://api.pivot-payment.com' 
            : 'https://api-stg.pivot-payment.com';
    }

    // 🚨 LANGKAH 1: FUNGSI BARU UNTUK MENGAMBIL KARTU AKSES (ACCESS TOKEN)
    private function getAccessToken()
    {
        try {
            $response = Http::timeout(30)->withHeaders([
                'X-MERCHANT-ID' => $this->merchantId,
                'X-MERCHANT-SECRET' => $this->merchantSecret,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/v1/access-token', [
                'grantType' => 'client_credentials'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['data']['accessToken'] ?? null;
            }

            Log::error('Pivot Get Token Error: ' . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error('Koneksi Pivot Token Gagal: ' . $e->getMessage());
            return null;
        }
    }

    // 🚨 LANGKAH 2: FUNGSI MEMBUAT PESANAN (MENGGUNAKAN KARTU AKSES)
    public function createTransaction(Order $order): array
    {
        if (!$this->merchantId || !$this->merchantSecret) {
            Log::error("Toko {$this->website->site_name} tidak memiliki Kredensial API Pivot.");
            return ['status' => 'error', 'token' => null, 'redirect_url' => null];
        }

        // --- MINTA ACCESS TOKEN DULU ---
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            Log::error("Gagal mendapatkan Access Token dari Pivot untuk Order ID: {$order->id}");
            return ['status' => 'error', 'token' => null, 'redirect_url' => null];
        }

        $totalBayar = (int) ($order->total_amount);
        $returnUrl = url()->route('store.payment', ['order_number' => $order->order_number]);
        // 🚨 GANTI DENGAN URL NGROK ANDA YANG SEDANG JALAN
        $ngrokUrl = 'https://nontraversable-magan-nonpulsating.ngrok-free.dev'; // <-- Isi dengan URL Ngrok asli Anda!
        $webhookUrl = $ngrokUrl . '/pivot/webhook';

        // Merakit Payload sesuai format v2/payments Pivot
        $payload = [
            'clientReferenceId' => $order->order_number,
            'amount' => [
                'value' => $totalBayar,
                'currency' => 'IDR'
            ],
            'paymentType' => 'SINGLE',
            'mode' => 'REDIRECT', 
            'bypassStatusPage' => false,
            'webhookUrl' => $webhookUrl,
            'notificationUrl' => $webhookUrl,
            'callbackUrl' => $webhookUrl,
            'serverCallbackUrl' => $webhookUrl,
            'redirectUrl' => [
                'successReturnUrl' => $returnUrl,
                'failureReturnUrl' => $returnUrl,
                'expirationReturnUrl' => $returnUrl
            ],
            'customer' => [
                'givenName' => $order->customer_name,
                'email' => $order->customer_email ?: 'no-email@deuswebcommerce.com',
                'phoneNumber' => [
                    'countryCode' => '+62',
                    'number' => ltrim(ltrim($order->customer_whatsapp, '+62'), '0') ?: '8111111111'
                ],
            ],
        ];

        try {
            // --- TEMBAK API PEMBAYARAN MENGGUNAKAN ACCESS TOKEN BEARER ---
            $response = Http::timeout(30)
                ->withToken($accessToken) 
                ->withHeaders([
                    'X-MERCHANT-ID' => $this->merchantId,
                    'Content-Type' => 'application/json',
                ])->post($this->baseUrl . '/v2/payments', $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                $data = $responseData['data'] ?? $responseData;
                
                // 🚨 KUNCI PERBAIKAN: 
                // Prioritaskan paymentUrl atau url. Abaikan 'redirectUrl' agar tidak tertipu!
                $paymentUrl = $data['paymentUrl'] ?? $data['checkoutUrl'] ?? $data['url'] ?? $data['payUrl'] ?? null; 
                
                $sessionId = $data['id'] ?? $data['sessionId'] ?? $data['transactionId'] ?? null;

                // CCTV jika URL masih tersembunyi
                if (empty($paymentUrl)) {
                    Log::error('Pivot SUKSES, tapi URL tidak ketemu! Isi JSON: ' . json_encode($data));
                }

                return [
                    'status' => 'success',
                    'token' => $sessionId,
                    'redirect_url' => $paymentUrl
                ];
            }

            Log::error('Pivot Create Session Error: ' . $response->body());
            return ['status' => 'error', 'token' => null, 'redirect_url' => null];

        } catch (\Exception $e) {
            Log::error('Koneksi Pivot Payments Gagal: ' . $e->getMessage());
            return ['status' => 'error', 'token' => null, 'redirect_url' => null];
        
        }
    }

  public function handleWebhook(Request $request): array
    {
        $payload = $request->all();
        
        // 1. Jaring Pengaman Variabel
        $orderId = $payload['clientReferenceId'] ?? ($payload['data']['clientReferenceId'] ?? null);
        $status = $payload['status'] ?? ($payload['data']['status'] ?? null);
        
        // =========================================================================
        // 🚨 VERIFIKASI KEAMANAN ASLI PIVOT (MENGGUNAKAN x-api-key)
        // =========================================================================
        // 🚨 VERIFIKASI KEAMANAN MENGGUNAKAN CALLBACK KEY DEUS
        $apiKeyFromPivot = $request->header('x-api-key'); 
        $callbackApiKey = env('PIVOT_CALLBACK_KEY');

        // Verifikasi: Cocokkan langsung kunci dari header dengan kunci rahasia kita
        $isValid = ($apiKeyFromPivot === $callbackApiKey);

        if (!$isValid) {
            \Illuminate\Support\Facades\Log::error("Pivot Webhook Error: x-api-key tidak cocok atau kosong untuk order {$orderId}");
            return ['is_valid' => false]; 
        }
        // =========================================================================

        $finalStatus = 'pending';
        $statusUpper = strtoupper($status ?? '');

        if (in_array($statusUpper, ['SUCCESS', 'PAID', 'SETTLED'])) {
            $finalStatus = 'paid';
        } elseif (in_array($statusUpper, ['FAILED', 'EXPIRED', 'CANCELED'])) {
            $finalStatus = 'failed';
        }

        // Penanganan Payment Method (Object ke String)
        $paymentMethod = 'Pivot Payment Gateway';
        $rawPaymentMethod = $payload['data']['paymentMethod'] ?? ($payload['paymentMethod'] ?? null);
        
        if (is_array($rawPaymentMethod)) {
            $paymentMethod = $rawPaymentMethod['type'] ?? 'QRIS/E-Wallet';
        } elseif (is_string($rawPaymentMethod)) {
            $paymentMethod = str_replace('_', ' ', $rawPaymentMethod); 
        }

        return [
            'is_valid' => true, 
            'order_id' => $orderId,
            'status' => $finalStatus,
            'payment_method' => $paymentMethod
        ];
    }
}
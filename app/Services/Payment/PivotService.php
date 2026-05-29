<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use URL;

class PivotService implements PaymentGatewayInterface
{
    protected $website;
    // Kunci Level Toko (Sub-Account)
    protected $subMerchantId;
    protected $subMerchantSecret;
    
    // Kunci Level Platform (Master)
    protected $masterClientId;
    protected $masterSecretKey;
    protected $baseUrl;

   public function __construct(Website $website)
    {
        $this->website = $website;
        // $this->baseUrl = 'https://api.pivot-payment.com';
        // 🚨 FIX: Wajib menggunakan $this-> agar bisa dibaca oleh fungsi lain!
        // $this->baseUrl = 'https://api-stg.pivot-payment.com';
        $this->baseUrl = env('PIVOT_BASE_URL', 'https://api.pivot-payment.com');

        // 1. Simpan Kunci Master secara permanen dari .env
        $this->masterClientId = env('PIVOT_CLIENT_KEY');
        $this->masterSecretKey = env('PIVOT_SERVER_KEY');

        // 2. Tarik Kunci Sub-Akun dari Klien (jika sudah approved)
        $kybDetail = $website->user->kybDetail ?? null;

        if ($kybDetail && $kybDetail->status === 'approved') {
            $this->subMerchantId = $kybDetail->merchant_id;
            $this->subMerchantSecret = $kybDetail->merchant_secret;
        } else {
            $this->subMerchantId = null;
            $this->subMerchantSecret = null;
        }
    }

   // 🚨 UBAH FUNGSI INI: Beri parameter penentu (Apakah ini Master?)
 // Tidak perlu lagi parameter $useMasterKey
    public function getAccessToken()
    {
        try {
            // 🚨 PALU GODAM: Hardcode URL di sini agar mustahil dibaca kosong
            // $baseUrl = 'https://api.pivot-payment.com';
            $url = $this->baseUrl . '/v1/access-token';
            
            $response = Http::timeout(30)->withHeaders([
                'X-MERCHANT-ID' => $this->masterClientId,
                'X-MERCHANT-SECRET' => $this->masterSecretKey,
                'Content-Type' => 'application/json',
            ])->post($url, [
                'grantType' => 'client_credentials'
            ]);

            if ($response->successful()) {
                return $response->json()['data']['accessToken'] ?? null;
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
        // 🚨 FIX: Hapus pengecekan subMerchantSecret, karena OBO cuma butuh ID!
        if (!$this->subMerchantId) {
            Log::error("Toko {$this->website->site_name} tidak memiliki Sub-Account ID Pivot.");
            return ['status' => 'error', 'token' => null, 'redirect_url' => null];
        }

        // --- MINTA ACCESS TOKEN DULU (Otomatis akan menggunakan ID & Secret Master) ---
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            Log::error("Gagal mendapatkan Access Token dari Pivot untuk Order ID: {$order->id}");
            return ['status' => 'error', 'token' => null, 'redirect_url' => null];
        }

        $totalBayar = (int) ($order->total_amount);
        $returnUrl = url()->route('store.payment', ['order_number' => $order->order_number]);
        // 🚨 GANTI DENGAN URL NGROK ANDA YANG SEDANG JALAN
        // $ngrokUrl = 'https://nontraversable-magan-nonpulsating.ngrok-free.dev'; // <-- Isi dengan URL Ngrok asli Anda!
        // $webhookUrl = $ngrokUrl . '/pivot/webhook';
        $webhookUrl = config('app.url') . '/pivot/webhook';
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
            Log::info("Mencoba membuat transaksi OBO (On-Behalf-Of) untuk Sub-Account: " . $this->subMerchantId);
            
            // --- TEMBAK API PEMBAYARAN MENGGUNAKAN ACCESS TOKEN BEARER ---
            $response = Http::timeout(30)
                ->withToken($accessToken) 
                ->withHeaders([
                    // 1. Identitas pintu utama (Selalu Master)
                    'X-MERCHANT-ID' => $this->masterClientId, 
                    
                    // 2. 🚨 HEADER SAKTI PIVOT UNTUK SUB-ACCOUNT 🚨
                    'x-submerchant-id' => trim($this->subMerchantId), 
                    
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
   public function createSubAccount($kybDetail)
    {
        // Siapkan URL logo cadangan jika klien belum punya logo di database
        $logoUrl = $kybDetail->logo 
            ? asset('storage/' . $kybDetail->logo) // Sesuaikan dengan path penyimpanan gambar Anda
            : 'https://ui-avatars.com/api/?name=' . urlencode($kybDetail->short_name) . '&background=random'; // Logo dummy dinamis

        // Memetakan data dari database ke format payload API Pivot
        $payload = [
            'name' => $kybDetail->name,
            'shortName' => $kybDetail->short_name,
            'description' => $kybDetail->description ?? 'Bisnis ' . $kybDetail->name,
            'website' => $kybDetail->website,
            'merchantEmail' => $kybDetail->merchant_email,
            'merchantPhone' => $kybDetail->merchant_phone,
            
            // 🚨 TAMBAHAN: Parameter LOGO yang diminta Pivot
            'logo' => $logoUrl,

            'businessCountry' => $kybDetail->business_country ?? 'ID',
            'businessType' => $kybDetail->business_type,
            'businessStructure' => $kybDetail->business_structure,
            'parentIndustry' => $kybDetail->parent_industry,
            'childIndustry' => $kybDetail->child_industry,
            'mcc' => (string) $kybDetail->mcc,
            'countryOfEntity' => $kybDetail->country_of_entity ?? 'ID',
            'digitalStatus' => $kybDetail->digital_status ?? 'Digital',
            'picName' => $kybDetail->pic_name,
            'picEmail' => $kybDetail->pic_email,
            'picPhone' => $kybDetail->pic_phone,
            'picJobTitle' => $kybDetail->pic_job_title ?? 'Owner',
            'address' => $kybDetail->address,
            'districtId' => (int) $kybDetail->district_id,
            'postCode' => (string) $kybDetail->post_code,
            'autoWithdrawal' => $kybDetail->auto_withdrawal,
            
            // 🚨 FIX: Masukkan channelCode ke DALAM array bankAccount
            'bankAccount' => [
                'accountNumber' => (string) $kybDetail->bank_account_number,
                // 'bankName' => $kybDetail->bank_account_name,
                'channelCode' => $kybDetail->bank_channel_code ?? 'BRI', 
            ],
            
            'subAccountType' => 'KYC'
        ];

        // Membersihkan nilai NULL dari array
       // Membersihkan nilai NULL dari array
        $payload = array_filter($payload, function($value) {
            return !is_null($value);
        });

        // 🚨 1. MINTA ACCESS TOKEN DULU (Sama seperti di createTransaction)
       $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            throw new \Exception('Gagal mendapatkan Access Token dari Pivot sebelum membuat Sub-Akun.');
        }

        // 🚨 HARDCODE URL LANGSUNG DI SINI JUGA
        // $stagingUrl = 'https://api.pivot-payment.com';

        $response = Http::withToken($accessToken) 
            ->withHeaders([
                'X-MERCHANT-ID' => $this->masterClientId,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])
            ->post($this->baseUrl . '/v1/sub-merchants', $payload);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Pivot API Error: ' . $response->body());
    }
    /**
     * Mengambil Saldo (Balance) dari Sub-Account Pivot
     */
    public function getSubAccountBalance($merchantId)
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            \Illuminate\Support\Facades\Log::error("Gagal mendapatkan Access Token untuk Cek Saldo Merchant ID: {$merchantId}");
            return 0; // Kembalikan 0 jika gagal
        }

        try {
            // Gunakan Staging URL
            // $url = 'https://api.pivot-payment.com/v1/balances';
            $url = $this->baseUrl . '/v1/balances';
            
            $response = Http::withToken($accessToken)
                ->withHeaders([
                    'X-MERCHANT-ID' => $this->masterClientId,
                    'x-submerchant-id' => trim($merchantId), // 🚨 INI KUNCI UTAMANYA!
                    'Accept' => 'application/json',
                ])
                ->get($url, [
                    'usecase' => 'PAYMENT' // Fokus ke saldo hasil transaksi payment
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Pivot mengembalikan nilai dalam string (misal: "135775256.00")
                $balanceString = $data['data']['availableBalance']['value'] ?? '0';
                
                // Konversi ke integer/float
                return (float) $balanceString; 
            }

            \Illuminate\Support\Facades\Log::error("Pivot Get Balance Error: " . $response->body());
            return 0;

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Koneksi Pivot Balance Gagal: ' . $e->getMessage());
            return 0;
        }
    }
}
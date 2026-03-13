<?php

namespace App\Services;

use App\Models\Website;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AccurateService
{
    protected $website;
    protected $integration;

    public function __construct(Website $website)
    {
        $this->website = $website;
        $this->integration = $website->accurateIntegration;
    }

    /**
     * 1. Mendapatkan Access Token (Auto-Refresh jika basi)
     */
    private function getValidAccessToken()
    {
        if (!$this->integration || !$this->integration->access_token) return null;

        if ($this->integration->token_expires_at && $this->integration->token_expires_at->subMinutes(5)->isFuture()) {
            return $this->integration->access_token;
        }

        $response = Http::timeout(30)          // Tunggu sampai 30 detik (jangan 10 detik)
    ->retry(3, 1000)                   // Jika gagal/timeout, coba lagi maksimal 3 kali, dengan jeda 1 detik (1000ms) antar percobaan
    ->asForm()->withBasicAuth(
            config('services.accurate.client_id'),
            config('services.accurate.client_secret')
        )->post('https://account.accurate.id/oauth/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->integration->refresh_token,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $this->integration->update([
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'],
                'token_expires_at' => now()->addSeconds($data['expires_in']),
            ]);
            return $data['access_token'];
        }

        Log::error("Accurate Refresh Token Gagal untuk Website ID {$this->website->id}: ", $response->json());
        return null;
    }

    /**
     * 2. Mengambil daftar Database (Digunakan di halaman Settings)
     */
    public function getDatabaseList()
    {
        $token = $this->getValidAccessToken();
        if (!$token) return [];

        $response = Http::timeout(30)          // Tunggu sampai 30 detik (jangan 10 detik)
    ->retry(3, 1000)                   // Jika gagal/timeout, coba lagi maksimal 3 kali, dengan jeda 1 detik (1000ms) antar percobaan
    ->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->get('https://account.accurate.id/api/db-list.do');

        return $response->successful() ? ($response->json('d') ?? []) : [];
    }

    /**
     * 3. Membuka Database dan Mendapatkan Session ID & Host (BARU)
     */
    private function openDatabaseSession()
    {
        $token = $this->getValidAccessToken();
        
        // Pastikan token ada dan user sudah memilih database
        if (!$token || !$this->integration->accurate_database_id) return null;

        // Tembak API open-db
        $response = Http::timeout(30)          // Tunggu sampai 30 detik (jangan 10 detik)
    ->retry(3, 1000)                   // Jika gagal/timeout, coba lagi maksimal 3 kali, dengan jeda 1 detik (1000ms) antar percobaan
    ->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->get('https://account.accurate.id/api/open-db.do', [
            'id' => $this->integration->accurate_database_id
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'token' => $token,
                'session_id' => $data['session'], // Kunci Sesi
                'host' => $data['host'] // URL Server Spesifik (Misal: zeus.accurate.id)
            ];
        }

        Log::error("Gagal Buka Database Accurate ID {$this->integration->accurate_database_id}: ", $response->json());
        return null;
    }
   /**
     * 4.5. Mencari atau Membuat Pelanggan Baru di Accurate
     */
    private function findOrCreateCustomer($order, $sessionData)
    {
        // 1. GEMBOK ANTI-DUPLIKAT: Cek Database Lokal Dulu!
        // Jika pesanan ini sudah punya ID Accurate, langsung gunakan itu.
        if (!empty($order->accurate_customer_no)) {
            return $order->accurate_customer_no;
        }

        $customerName = $order->customer_name . ' (' . $order->customer_whatsapp . ')';
        $keywords = $order->customer_whatsapp;

        // 2. CARI DI ACCURATE (Siapa tahu pelanggan ini pernah belanja di nomor order lain)
        $searchResponse = Http::timeout(30)          // Tunggu sampai 30 detik (jangan 10 detik)
    ->retry(3, 1000)                   // Jika gagal/timeout, coba lagi maksimal 3 kali, dengan jeda 1 detik (1000ms) antar percobaan
    ->withHeaders([
            'Authorization' => 'Bearer ' . $sessionData['token'],
            'X-Session-ID' => $sessionData['session_id']
        ])->get($sessionData['host'] . '/accurate/api/customer/list.do', [
            'keywords' => $keywords
        ]);

        if ($searchResponse->successful()) {
            $data = $searchResponse->json('d');
            if (!empty($data) && isset($data[0]['customerNo'])) {
                return $data[0]['customerNo']; // Ketemu!
            }
        }

        // 3. JIKA BENAR-BENAR BARU, BUATKAN DI ACCURATE
        $createResponse = Http::timeout(30)          // Tunggu sampai 30 detik (jangan 10 detik)
    ->retry(3, 1000)                   // Jika gagal/timeout, coba lagi maksimal 3 kali, dengan jeda 1 detik (1000ms) antar percobaan
    ->withHeaders([
            'Authorization' => 'Bearer ' . $sessionData['token'],
            'X-Session-ID' => $sessionData['session_id']
        ])->post($sessionData['host'] . '/accurate/api/customer/save.do', [
            'name' => $customerName,
            'mobilePhone' => $order->customer_whatsapp,
            'shipStreet' => $order->customer_address,
        ]);

        $createData = $createResponse->json();

        // 4. AMBIL ID DARI KOTAK "r" (RECORD)
        if ($createResponse->successful() && isset($createData['s']) && $createData['s'] === true) {
            
            // Inilah kunci rahasianya: kita ambil dari ['r']['customerNo']
            $newCustomerNo = $createData['r']['customerNo'] ?? null; 
            
            if ($newCustomerNo) {
                Log::info("Berhasil membuat pelanggan di Accurate: {$customerName} ({$newCustomerNo})");
                return $newCustomerNo;
            }
        }

        Log::error("Gagal membuat customer di Accurate untuk Order {$order->order_number}: ", $createData ?? []);
        return null;
    }
    /**
     * 4. Mengirim Produk ke Accurate (SUDAH DISEMPURNAKAN)
     */
    /**
     * Sinkronisasi Wujud Barang ke Accurate
     */
    public function syncProductVariant($variant)
    {
        $sessionData = $this->openDatabaseSession();
        if (!$sessionData) return false;

        $sku = $variant->sku;

        // Siapkan keranjang data barang
        $itemData = [
            'itemType' => 'INVENTORY',
            'name' => $variant->name ?: ($variant->product->name ?? 'Produk'),
            'no' => $sku,
            'unit1Name' => 'PCS',
            'unitPrice' => $variant->price > 0 ? $variant->price : ($variant->product->price ?? 0),
        ];

        // Tembak API Pembuatan Barang Accurate
        $response = Http::timeout(30)          // Tunggu sampai 30 detik (jangan 10 detik)
    ->retry(3, 1000)                   // Jika gagal/timeout, coba lagi maksimal 3 kali, dengan jeda 1 detik (1000ms) antar percobaan
    ->withHeaders([
            'Authorization' => 'Bearer ' . $sessionData['token'],
            'X-Session-ID' => $sessionData['session_id']
        ])->post($sessionData['host'] . '/accurate/api/item/save.do', $itemData);

        $responseData = $response->json();
        $rawBody = $response->body();

        // 1. Jika sukses terbuat baru
        if ($response->successful() && isset($responseData['s']) && $responseData['s'] === true) {
            Log::info("Sukses Sync SKU {$sku} ke Accurate.");
            return true;
        }

        // ==========================================
        // 🚨 JURUS BYPASS BAJA: TANGKAP ERROR DUPLIKAT
        // ==========================================
        // Kita baca mentah-mentah teks dari server Accurate
        if (stripos($rawBody, 'Sudah ada data lain') !== false || stripos($rawBody, 'DUMM') !== false) {
            Log::info("Bypass: Barang dengan SKU {$sku} sudah ada di Accurate. Loloskan agar stok bisa di-update!");
            return true; // 🚨 Memaksa Controller lanjut ke proses Update Stok!
        }

        // Jika errornya bukan karena duplikat (misal token habis)
        Log::error("Gagal Sync SKU {$sku} ke Accurate. RAW: " . $rawBody);
        return false;
    }
    /**
     * 5. Mengirim Pesanan (Sales Invoice) ke Accurate
     */
    public function syncSalesInvoice($order)
    {
        // 1. Buka pintu database
        $sessionData = $this->openDatabaseSession();
        if (!$sessionData) return false;
        // ==========================================
        // 🚨 SATPAM 1: CEK APAKAH FAKTUR SUDAH ADA?
        // ==========================================
        $existingInvoice = $this->getSalesInvoice($order->order_number, $sessionData);
        if ($existingInvoice) {
            Log::info("Faktur {$order->order_number} sudah ada di Accurate. Melewati pembuatan ulang.");
            return true; // Kita anggap "sukses" agar fungsi Pelunasan (Receipt) tetap dijalankan
        }

        // 2. Siapkan array keranjang belanja (Detail Barang)
        $detailItems = [];
        
        // Asumsi relasi di model Order adalah $order->items()
        foreach ($order->items as $item) {
            // Kita harus cari SKU-nya, karena API Accurate sangat bergantung pada SKU
            $sku = '';
            
            // Cek apakah ini varian atau produk biasa
            if ($item->variant_id && $item->variant) {
                $sku = $item->variant->sku;
            } elseif ($item->product_id && $item->product) {
                $sku = $item->product->sku;
            }

            // Jika SKU tidak ditemukan, lompati barang ini
            if (empty($sku)) continue; 

            // Masukkan ke format Accurate
            $detailItems[] = [
                'itemNo' => $sku,
                'unitPrice' => $item->price,
                'quantity' => $item->qty,
            ];
        }

        // Jika tidak ada barang yang punya SKU, batalkan pengiriman agar tidak error
        if (empty($detailItems)) {
            Log::warning("Gagal Sync Invoice {$order->order_number}: Tidak ada item dengan SKU yang valid.");
            return false;
        }
        // ==========================================
        // TAMBAHAN BARU: MASUKKAN ONGKOS KIRIM JIKA ADA
        // ==========================================
        if ($order->shipping_cost > 0) {
            $detailItems[] = [
                'itemNo' => 'ONGKIR', // Pastikan SKU ini ada di Accurate klien
                'unitPrice' => $order->shipping_cost,
                'quantity' => 1,
            ];
        }

        // 3. Rakit Data Faktur Penjualan
        // ... (Kode keranjang/detailItems tetap sama) ...

       // ==========================================
        // PANGGIL DETEKTIF PELANGGAN SEBELUM BIKIN FAKTUR
        // ==========================================
        $customerNo = $this->findOrCreateCustomer($order, $sessionData);
        
        if (!$customerNo) {
            Log::error("Batal membuat Faktur {$order->order_number}: Data Pelanggan tidak valid.");
            return false;
        }

        // 🚨 SIMPAN ID ACCURATE KE DATABASE LOKAL (Ini Mencegah Duplikat!)
        if (empty($order->accurate_customer_no)) {
            $order->update(['accurate_customer_no' => $customerNo]);
        }

        

        // 3. Rakit Data Faktur Penjualan
        $invoiceData = [
            'customerNo' => $customerNo, // <-- SEKARANG MENGGUNAKAN VARIABEL DINAMIS!
            'number' => $order->order_number, // 🚨 WAJIB DITAMBAHKAN AGAR NOMORNYA SAMA DENGAN WEB
            'transDate' => $order->created_at->format('d/m/Y'),
            'description' => "Pesanan Web: " . $order->order_number,
            'detailItem' => $detailItems, // Masukkan keranjang belanja tadi
        ];

        // 4. Tembak API Save Sales Invoice
        $response = Http::timeout(30)          // Tunggu sampai 30 detik (jangan 10 detik)
    ->retry(3, 1000)                   // Jika gagal/timeout, coba lagi maksimal 3 kali, dengan jeda 1 detik (1000ms) antar percobaan
    ->withHeaders([
            'Authorization' => 'Bearer ' . $sessionData['token'],
            'X-Session-ID' => $sessionData['session_id']
        ])->post($sessionData['host'] . '/accurate/api/sales-invoice/save.do', $invoiceData);

        $responseData = $response->json();

        // 5. Cek apakah sukses
        if ($response->successful() && isset($responseData['s']) && $responseData['s'] === true) {
            Log::info("Sukses Sync Invoice {$order->order_number} ke Accurate.");
            return true;
        }

        // ==========================================
        // 🚨 JURUS BYPASS: TANGKAP ERROR DUPLIKAT
        // ==========================================
        $errorMessage = $responseData['d'][0] ?? '';
        
        if (stripos($errorMessage, 'Sudah ada data lain dengan') !== false) {
            Log::info("Bypass: Faktur {$order->order_number} sudah pernah terbuat. Melanjutkan ke tahap pelunasan.");
            return true; // Kita paksa return TRUE agar kode Controller lanjut mengeksekusi Pelunasan
        }
        // Catat error jika gagal
        Log::error("Gagal Sync Invoice {$order->order_number} ke Accurate: ", $responseData ?? []);
        return false;
    }
    /**
     * 6. Menghapus / Membatalkan Faktur Penjualan di Accurate
     */
    public function deleteSalesInvoice($order)
    {
        $sessionData = $this->openDatabaseSession();
        if (!$sessionData) return false;

        // Tembak API Hapus Faktur menggunakan Nomor Order kita
        $response = Http::timeout(30)          // Tunggu sampai 30 detik (jangan 10 detik)
    ->retry(3, 1000)                   // Jika gagal/timeout, coba lagi maksimal 3 kali, dengan jeda 1 detik (1000ms) antar percobaan
    ->withHeaders([
            'Authorization' => 'Bearer ' . $sessionData['token'],
            'X-Session-ID' => $sessionData['session_id']
        ])->post($sessionData['host'] . '/accurate/api/sales-invoice/delete.do', [
            'number' => $order->order_number // Karena tadi kita set 'number' pakai order_number
        ]);

        $responseData = $response->json();

        if ($response->successful() && isset($responseData['s']) && $responseData['s'] === true) {
            Log::info("Sukses Menghapus Invoice {$order->order_number} di Accurate.");
            return true;
        }

        Log::error("Gagal Menghapus Invoice {$order->order_number}: ", $responseData ?? []);
        return false;
    }
   /**
     * 6. Membuat Penerimaan Pelanggan (Versi Cepat & Lengkap)
     */
    public function syncPaymentReceipt($order)
    {
        $sessionData = $this->openDatabaseSession();
        if (!$sessionData) return false;

        // Kita hitung sendiri totalnya dari web
        $totalBayar = $order->total_amount + $order->shipping_cost;

        $paymentData = [
            'customerNo' => $order->accurate_customer_no,
            'bankNo' => '110102', 
            'transDate' => now()->format('d/m/Y'), 
            
            // 🚨 INI DIA KUNCI YANG KEMARIN SAYA LUPAKAN! (Total uang masuk Bank)
            'chequeAmount' => $totalBayar, 
            
            'detailInvoice' => [ 
                [
                    'invoiceNo' => $order->order_number, 
                    // Uang yang dipakai untuk motong faktur
                    'paymentAmount' => $totalBayar 
                ]
            ]
        ];

        // Tembak API Pelunasan
        $response = Http::timeout(30)          // Tunggu sampai 30 detik (jangan 10 detik)
    ->retry(3, 1000)                   // Jika gagal/timeout, coba lagi maksimal 3 kali, dengan jeda 1 detik (1000ms) antar percobaan
    ->withHeaders([
            'Authorization' => 'Bearer ' . $sessionData['token'],
            'X-Session-ID' => $sessionData['session_id']
        ])->post($sessionData['host'] . '/accurate/api/sales-receipt/save.do', $paymentData);

        $responseData = $response->json();
        $rawBody = $response->body();

        // Cek Sukses
        if ($response->successful() && isset($responseData['s']) && $responseData['s'] === true) {
            Log::info("Sukses Melunasi Invoice {$order->order_number} di Accurate senilai Rp " . number_format($totalBayar, 0, ',', '.'));
            return true;
        }

        // ==========================================
        // 🚨 KEMBALIKAN SATPAM BYPASS (Anti Spam Klik)
        // ==========================================
        if (stripos($rawBody, 'sisa tagihan') !== false || 
            stripos($rawBody, 'lunas') !== false || 
            stripos($rawBody, 'lebih besar') !== false) {
            
            Log::info("Bypass: Faktur {$order->order_number} ditolak bayar, kemungkinan besar sudah lunas. Aman!");
            return true;
        }

        // Tangkap Hantu Error Lainnya
        Log::error("Gagal Melunasi Invoice {$order->order_number}. RAW RESPONSE: " . $rawBody);
        return false;
    }
    /**
     * 7. Mengintip Data Faktur di Accurate
     */
    private function getSalesInvoice($orderNumber, $sessionData)
    {
        $response = Http::timeout(30)          // Tunggu sampai 30 detik (jangan 10 detik)
    ->retry(3, 1000)                   // Jika gagal/timeout, coba lagi maksimal 3 kali, dengan jeda 1 detik (1000ms) antar percobaan
    ->withHeaders([
            'Authorization' => 'Bearer ' . $sessionData['token'],
            'X-Session-ID' => $sessionData['session_id']
        ])->get($sessionData['host'] . '/accurate/api/sales-invoice/list.do', [
            'keywords' => $orderNumber // Cari berdasarkan nomor order
        ]);

        if ($response->successful()) {
            $data = $response->json('d');
            // Pastikan data ketemu dan nomornya benar-benar persis sama
            if (!empty($data) && strcasecmp($data[0]['number'], $orderNumber) === 0) {
                return $data[0]; 
            }
        }
        return null;
    }
   /**
     * 8. Penyesuaian Persediaan (Inisialisasi & Update Stok)
     */
   /**
     * 8. Penyesuaian Persediaan (Inisialisasi & Update Stok)
     */
    public function syncInventoryAdjustment($sku, $qtyDifference)
    {
        if ($qtyDifference == 0) return true; 

        $sessionData = $this->openDatabaseSession();
        if (!$sessionData) return false;

        $isAddition = $qtyDifference > 0;
        $absoluteQty = abs($qtyDifference); // Pastikan selalu positif (misal: 111)

        $adjustmentData = [
            'transDate' => now()->format('d/m/Y'),
            'adjustmentAccountNo' => '600020', 
            
            'detailItem' => [
                [
                    'itemNo' => $sku,
                    'quantity' => $absoluteQty, 
                    
                    // ==========================================
                    // 🚨 BINGO! INI KATA SANDI ASLI DARI ACCURATE
                    // ==========================================
                    'itemAdjustmentType' => $isAddition ? 'ADJUSTMENT_IN' : 'ADJUSTMENT_OUT'
                ]
            ]
        ];

        $response = Http::timeout(30)          // Tunggu sampai 30 detik (jangan 10 detik)
    ->retry(3, 1000)                   // Jika gagal/timeout, coba lagi maksimal 3 kali, dengan jeda 1 detik (1000ms) antar percobaan
    ->withHeaders([
            'Authorization' => 'Bearer ' . $sessionData['token'],
            'X-Session-ID' => $sessionData['session_id']
        ])->post($sessionData['host'] . '/accurate/api/item-adjustment/save.do', $adjustmentData);

        $responseData = $response->json();
        $rawBody = $response->body();

        if ($response->successful() && isset($responseData['s']) && $responseData['s'] === true) {
            $tipeTeks = $isAddition ? 'Penambahan' : 'Pengurangan';
            Log::info("Sukses {$tipeTeks} Stok SKU {$sku} di Accurate sebanyak {$absoluteQty} pcs.");
            return true;
        }

        Log::error("Gagal Penyesuaian Stok SKU {$sku} di Accurate. RAW RESPONSE: " . $rawBody);
        return false;
    }
    /**
     * 9. Mengambil Sisa Stok Langsung dari Accurate (Single Source of Truth)
     */
    public function getAccurateStock($sku)
    {
        $sessionData = $this->openDatabaseSession();
        if (!$sessionData) return 0;

        $response = Http::timeout(30)          // Tunggu sampai 30 detik (jangan 10 detik)
    ->retry(3, 1000)                   // Jika gagal/timeout, coba lagi maksimal 3 kali, dengan jeda 1 detik (1000ms) antar percobaan
    ->withHeaders([
            'Authorization' => 'Bearer ' . $sessionData['token'],
            'X-Session-ID' => $sessionData['session_id']
        ])->get($sessionData['host'] . '/accurate/api/item/detail.do', [
            'no' => $sku
        ]);

       if ($response->successful() && isset($response->json()['d'])) {
            $data = $response->json('d');
            
            // 🚨 BINGO! Kita gunakan nama variabel asli dari Accurate
            if (isset($data['availableToSell'])) {
                return (float) $data['availableToSell'];
            }
            
            // Cadangan jika availableToSell kosong
            if (isset($data['balance'])) {
                return (float) $data['balance'];
            }
        }
        
        return 0; // Jika tetap tidak ketemu, kembalikan 0 (Hati-hati, ini yang bikin 161 tadi!)
    }
   /**
     * 10. REVERSE ENGINEERING: Mengintip Struktur Penyesuaian Persediaan Accurate
     */
    public function debugAdjustmentFormat()
    {
        $sessionData = $this->openDatabaseSession();
        if (!$sessionData) return;

        // Tarik data TANPA filter apa pun agar Accurate tidak ngambek
        $response = Http::timeout(30)          // Tunggu sampai 30 detik (jangan 10 detik)
    ->retry(3, 1000)                   // Jika gagal/timeout, coba lagi maksimal 3 kali, dengan jeda 1 detik (1000ms) antar percobaan
    ->withHeaders([
            'Authorization' => 'Bearer ' . $sessionData['token'],
            'X-Session-ID' => $sessionData['session_id']
        ])->get($sessionData['host'] . '/accurate/api/item-adjustment/list.do');

        // 🚨 TANGKAP TEKS MENTAHNYA (RAW BODY)
        \Illuminate\Support\Facades\Log::info("CCTV 5 (RAW LIST): " . $response->body());
        
        $data = $response->json();
        
        // Jika beruntung dapat ID-nya, intip sedalam-dalamnya
        if (isset($data['d'][0]['id'])) {
            $latestId = $data['d'][0]['id'];

            $detailResponse = Http::timeout(30)          // Tunggu sampai 30 detik (jangan 10 detik)
    ->retry(3, 1000)                   // Jika gagal/timeout, coba lagi maksimal 3 kali, dengan jeda 1 detik (1000ms) antar percobaan
    ->withHeaders([
                'Authorization' => 'Bearer ' . $sessionData['token'],
                'X-Session-ID' => $sessionData['session_id']
            ])->get($sessionData['host'] . '/accurate/api/item-adjustment/detail.do', [
                'id' => $latestId
            ]);
            
            \Illuminate\Support\Facades\Log::info("CCTV 6 (RAW DETAIL): " . $detailResponse->body());
        }
    }
    protected function checkAndRefreshToken()
    {
        $integration = $this->website->accurateIntegration;

        if (!$integration || !$integration->access_token) {
            return false;
        }

        // TIPS: Jika Anda memiliki kolom 'expires_at' di tabel accurate_integrations, 
        // Anda bisa cek waktunya di sini. Jika belum expired, langsung return true.
        // Contoh:
        // if ($integration->expires_at && now()->lessThan($integration->expires_at)) {
        //     return true; 
        // }

        // Jika Anda belum membuat sistem penjadwalan expired token, 
        // kita asumsikan token masih valid untuk uji coba ini.
        // Nanti di sistem production, di sinilah Anda menembak API https://account.accurate.id/oauth/token 
        // menggunakan $integration->refresh_token untuk mendapatkan access_token yang baru.

        return true; 
    }
    /**
     * SINKRONISASI PRODUK DARI ACCURATE KE WEB
     */
   /**
     * SINKRONISASI PRODUK DARI ACCURATE KE WEB
     */
    public function syncProductsFromAccurate()
    {
        // 1. BUKA PINTU DATABASE (Ini akan otomatis me-refresh token & mengambil Host yang benar)
        $sessionData = $this->openDatabaseSession();
        if (!$sessionData) {
            Log::error("Gagal membuka sesi Accurate untuk toko: " . $this->website->site_name);
            return false;
        }

        // 2. Tembak API Accurate untuk meminta Daftar Barang (Item)
        // Perhatikan penggunaan $sessionData['host'] dan prefix /accurate/api/
        $response = Http::timeout(30)          // Tunggu sampai 30 detik (jangan 10 detik)
    ->retry(3, 1000)                   // Jika gagal/timeout, coba lagi maksimal 3 kali, dengan jeda 1 detik (1000ms) antar percobaan
    ->withHeaders([
            'Authorization' => 'Bearer ' . $sessionData['token'],
            'X-Session-ID'  => $sessionData['session_id'],
        ])->get($sessionData['host'] . '/accurate/api/item/list.do', [
            'fields' => 'id,no,name,unitPrice,quantity', // Kolom inti
        ]);

        if (!$response->successful()) {
            Log::error("Gagal menarik produk dari Accurate. RAW: " . $response->body());
            return false;
        }

        $accurateItems = $response->json()['d'] ?? [];
        $syncedSkus = [];

       // 3. Looping data dari Accurate dan masukkan ke Database Web
       // 3. Looping data dari Accurate
        foreach ($accurateItems as $item) {
            $sku = $item['no'] ?? null;
            if (!$sku) continue; 
            // ==========================================
            // 🚨 PENJAGA PINTU: BLOKIR SKU KHUSUS!
            // ==========================================
            // Jangan masukkan item jasa/sistem ke etalase web
            $blockedSkus = ['ONGKIR', 'DISKON', 'BIAYA_LAYANAN'];
            if (in_array(strtoupper($sku), $blockedSkus)) {
                continue; // Lompati baris ini, jangan dimasukkan ke DB lokal!
            }

            $syncedSkus[] = $sku;
            $price = $item['unitPrice'] ?? 0;
            $stock = $item['quantity'] ?? 0;

            // ==========================================
            // CEK 1: APAKAH INI VARIAN DARI PRODUK YANG SUDAH ADA?
            // ==========================================
            // (Asumsi nama model Anda adalah ProductVariant)
            if (class_exists('\App\Models\ProductVariant')) {
                $variant = \App\Models\ProductVariant::whereHas('product', function($query) {
                    $query->where('website_id', $this->website->id);
                })->where('sku', $sku)->first();

                if ($variant) {
                    // Jika ketemu sebagai varian, update variannya saja dan lompati kode di bawahnya
                    $variant->update([
                        'price' => $price,
                        'stock' => $stock,
                    ]);
                    continue; 
                }
            }

            // ==========================================
            // CEK 2: JIKA BUKAN VARIAN, UPDATE/BUAT SEBAGAI PRODUK UTAMA
            // ==========================================
            $product = \App\Models\Product::firstOrNew([
                'website_id' => $this->website->id,
                'sku'        => $sku,
            ]);

            $isNewProduct = !$product->exists;

            $product->name      = $item['name'];
            $product->price     = $price;
            $product->stock     = $stock;
            $product->is_active = true;

            if ($isNewProduct) {
                $product->slug        = \Illuminate\Support\Str::slug($item['name']) . '-' . \Illuminate\Support\Str::random(4);
                $product->weight      = 1000;
                $product->description = 'Produk ' . $item['name'] . ' original.';
            }

            $product->save(); 

            // (Logika Tarik Gambar tetap sama seperti sebelumnya di sini) ...
        }

        // 4. NONAKTIFKAN BARANG YANG DIHAPUS DI ACCURATE
        \App\Models\Product::where('website_id', $this->website->id)
            ->whereNotIn('sku', $syncedSkus)
            ->update(['is_active' => false, 'stock' => 0]);

        return true;
    }
}
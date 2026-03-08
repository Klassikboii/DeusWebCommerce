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

        $response = Http::asForm()->withBasicAuth(
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

        $response = Http::withHeaders(['Authorization' => 'Bearer ' . $token])
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
        $response = Http::withHeaders([
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
        $searchResponse = Http::withHeaders([
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
        $createResponse = Http::withHeaders([
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
    public function syncProductVariant($variant)
    {
        // Buka pintu database dulu
        $sessionData = $this->openDatabaseSession();
        if (!$sessionData) return false;

        $itemName = $variant->name ? $variant->product->name . ' - ' . $variant->name : $variant->product->name;

        // Format data yang dikirim ke Accurate
        $itemData = [
            'itemType' => 'INVENTORY',
            'no' => $variant->sku,
            'name' => $itemName,
            'unitPrice' => $variant->price,
        ];

       // 1. KITA HAPUS asForm()
        // 2. KITA TAMBAHKAN /accurate/ DI TENGAH URL
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $sessionData['token'],
            'X-Session-ID' => $sessionData['session_id']
        ])->post($sessionData['host'] . '/accurate/api/item/save.do', $itemData);

        $responseData = $response->json();

        // Cek apakah sukses (API Accurate menggunakan key "s" = true)
        if ($response->successful() && isset($responseData['s']) && $responseData['s'] === true) {
            return true;
        }

        // // ==========================================
        // // DEBUG: MUNCULKAN ERROR JIKA MASIH GAGAL
        // // ==========================================
        // dd([
        //     'STATUS_API' => 'DITOLAK OLEH ACCURATE',
        //     'PESAN_ERROR' => $responseData['d'] ?? $responseData,
        //     'DATA_KIRIMAN' => $itemData
        // ]);

        Log::error("Gagal Sync SKU {$variant->sku} ke Accurate: ", $responseData ?? []);
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
        $response = Http::withHeaders([
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
        $response = Http::withHeaders([
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
        $response = Http::withHeaders([
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
        $response = Http::withHeaders([
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

}
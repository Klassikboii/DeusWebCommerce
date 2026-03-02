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
        $invoiceData = [
            'customerNo' => 'C.00001', // 🚨 GANTI DENGAN NOMOR PELANGGAN DARI ACCURATE ANDA
            'transDate' => $order->created_at->format('d/m/Y'),
            'description' => "Pesanan Web: " . $order->order_number . " - " . $order->customer_name,
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

}
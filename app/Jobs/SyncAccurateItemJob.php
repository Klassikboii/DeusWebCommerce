<?php

namespace App\Jobs;

use App\Models\Website;
use App\Services\AccurateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncAccurateItemJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $websiteId;
    public $sku;

    // Batas maksimal Job boleh dicoba ulang jika terjadi error (misal koneksi Accurate RTO)
    public $tries = 3;

    public function __construct($websiteId, $sku)
    {
        $this->websiteId = $websiteId;
        $this->sku = $sku;
    }

    public function handle()
    {
        $website = \App\Models\Website::find($this->websiteId);
        if (!$website) return;

        \Illuminate\Support\Facades\Log::info("🚀 [JOB SINKRONISASI] Mulai sinkronisasi SKU: {$this->sku} untuk Website: {$website->site_name}");

        $accurateService = new \App\Services\AccurateService($website);
        
        try {
            $itemDetail = $accurateService->getSingleItemDetail($this->sku);
            
            if ($itemDetail) {
                $price = $itemDetail['unitPrice'] ?? 0;
                $stock = $itemDetail['availableToSell'] ?? $itemDetail['balance'] ?? $itemDetail['quantity'] ?? 0;
                
                // 🚨 AMBIL NAMA PRODUK DARI ACCURATE
                $name = $itemDetail['name'] ?? null; 

                // Siapkan data yang akan ditimpa
                $updateData = [
                    'price' => $price, 
                    'stock' => $stock,
                    'accurate_stock' => $stock, // 🚨 FIX: Samakan bayangan stoknya agar peringatan merah hilang
                    'last_sync_at' => now()
                ];

                // Jika nama dikirimkan oleh Accurate, tambahkan ke data update
                if ($name) {
                    $updateData['name'] = $name;
                }

                // 1. Update ke Tabel Products (Induk)
                \App\Models\Product::where('website_id', $this->websiteId)
                       ->where('sku', $this->sku)
                       ->update($updateData);

                // 2. Update ke Tabel Varian (Jika dia anak varian)
                if (class_exists('\App\Models\ProductVariant')) {
                    \App\Models\ProductVariant::whereHas('product', function($q) {
                        $q->where('website_id', $this->websiteId);
                    })->where('sku', $this->sku)->update([
                        'price' => $price, 
                        'stock' => $stock,
                        'accurate_stock' => $stock, // 🚨 FIX: Samakan bayangan stoknya agar peringatan merah hilang
                    'last_sync_at' => now()
                    ]);
                }

                \Illuminate\Support\Facades\Log::info("✅ [JOB SINKRONISASI] Berhasil update SKU: {$this->sku} | Harga: {$price} | Stok: {$stock}");
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("❌ [JOB SINKRONISASI] Gagal update SKU: {$this->sku}. Error: " . $e->getMessage());
            throw $e; 
        }
    }
}
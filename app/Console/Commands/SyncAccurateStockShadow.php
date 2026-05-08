<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Website;
use App\Services\AccurateService;
use Illuminate\Support\Facades\Log;

class SyncAccurateStockShadow extends Command
{
    protected $signature = 'accurate:sync-shadow';
    protected $description = 'Menarik stok dari Accurate ke kolom bayangan (accurate_stock) secara diam-diam';

    public function handle()
    {
        $this->info('Memulai Proses Shadow Sync Accurate...');

        // Cari semua website yang integrasi Accurate-nya aktif
        $websites = Website::whereHas('accurateIntegration', function($q) {
            $q->whereNotNull('accurate_database_id');
        })->get();

        foreach ($websites as $website) {
            $this->info("Menyinkronkan Toko: {$website->site_name}");
            
            try {
                $service = new AccurateService($website);
                $status = $service->syncShadowStockFromAccurate();
                
                if ($status) {
                    $this->info("✅ Sukses untuk toko {$website->site_name}");
                } else {
                    $this->error("❌ Gagal untuk toko {$website->site_name}");
                }
            } catch (\Exception $e) {
                Log::error("Cron Shadow Sync Error [{$website->site_name}]: " . $e->getMessage());
                $this->error("Terjadi kesalahan sistem.");
            }
        }

        $this->info('Proses Selesai!');
    }
}
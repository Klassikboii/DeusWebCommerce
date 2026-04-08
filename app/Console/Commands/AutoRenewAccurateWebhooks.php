<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AccurateIntegration;
use App\Services\AccurateService;

class AutoRenewAccurateWebhooks extends Command
{
    // Ini nama perintah yang akan kita panggil nanti
    protected $signature = 'accurate:renew-webhooks';
    protected $description = 'Memperpanjang masa aktif webhook Accurate untuk semua klien secara otomatis';

    public function handle()
    {
        $this->info('Memulai proses perpanjangan Webhook Accurate...');

        // Cari semua toko yang punya integrasi Accurate
        $integrations = AccurateIntegration::with('website')->get();

        foreach ($integrations as $integration) {
            if ($integration->website) {
                $service = new AccurateService($integration->website);
                $service->renewWebhook();
                
                // Beri jeda 1 detik antar toko agar API Accurate tidak mengira kita melakukan Spam/DDoS
                sleep(1); 
            }
        }

        $this->info('Proses perpanjangan selesai!');
    }
}
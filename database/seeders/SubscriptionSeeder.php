<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Website;
use App\Models\Package;
use App\Models\Subscription;

class SubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ambil Paket Free (Asumsi ID 1 adalah Free, atau cari berdasarkan harga 0)
        $freePackage = Package::where('price', 0)->first();
        
        if (!$freePackage) {
            $this->command->error('Paket Free tidak ditemukan! Pastikan sudah run PlatformSeeder.');
            return;
        }

        // 2. Ambil SEMUA Website yang belum punya subscription
        $websites = Website::doesntHave('activeSubscription')->get();

        foreach ($websites as $website) {
            Subscription::create([
                'website_id' => $website->id,
                'package_id' => $freePackage->id,
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => now()->addDays(30), // Kasih trial 30 hari
            ]);
            
            $this->command->info("Website {$website->site_name} berhasil diberi paket Free.");
        }
    }
}
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Package;
use Illuminate\Support\Facades\Hash;

class PlatformSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Paket Langganan (SaaS)
      // 1. Buat Paket Langganan (SaaS)
        $packages = [
            [
                'name' => 'Free Starter',
                'slug' => 'free',
                'price' => 0,
                'duration_days' => 30,
                'description' => 'Paket gratis untuk pemula.',
                'features' => json_encode(['Max 10 Produk', 'Subdomain Only', 'Basic Support']),
                'max_products' => 10,
                'can_custom_domain' => false,
                'remove_branding' => false,
            ],
            [
                'name' => 'Pro Business',
                'slug' => 'pro',
                'price' => 150000,
                'duration_days' => 30,
                'description' => 'Untuk bisnis yang berkembang.',
                'features' => json_encode(['Max 1000 Produk', 'Custom Domain', 'Priority Support', 'No Watermark']),
                'max_products' => 100, //  INI DIA KUNCI JAWABANNYA!
                'can_custom_domain' => true,
                'remove_branding' => true,
            ]
        ];

        foreach ($packages as $pkg) {
            Package::updateOrCreate(['slug' => $pkg['slug']], $pkg);
        }

        foreach ($packages as $pkg) {
            Package::updateOrCreate(['slug' => $pkg['slug']], $pkg);
        }

        // 2. Buat Super Admin
        User::updateOrCreate(
            ['email' => 'admin@webcommerce.id'],
            [
                'name' => 'Reynard (Owner)',
                'password' => Hash::make('password'),
                'role' => 'admin', // Pastikan kolom role ada di tabel users
            ]
        );

        // 3. Buat Klien Contoh (John Doe)
        User::updateOrCreate(
            ['email' => 'klien@gmail.com'],
            [
                'name' => 'John Doe',
                'password' => Hash::make('password'),
                'role' => 'client',
            ]
        );
        
        $this->command->info('Platform Seeder Selesai: Admin & Paket dibuat.');
    }
}
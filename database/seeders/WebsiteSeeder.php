<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Website;

class WebsiteSeeder extends Seeder
{
    public function run(): void
    {
        $client = User::where('email', 'klien@gmail.com')->first();

        if (!$client) return;

        // Data JSON Builder Default (Agar editor tidak kosong)
        $defaultSections = [
            [
                "id" => "hero-1",
                "type" => "hero",
                "visible" => true,
                "data" => [
                    "title" => "Promo Elektronik Murah!",
                    "subtitle" => "Diskon 50% untuk pembelian hari ini.",
                    "button_text" => "Belanja Sekarang",
                    "button_link" => "#products"
                ]
            ],
            [
                "id" => "features",
                "type" => "features",
                "visible" => true,
                "data" => [
                    "title" => "Kenapa Memilih Kami?",
                    "f1_title" => "Garansi Resmi", "f1_desc" => "Jaminan uang kembali.", "f1_icon" => "bi-shield-check",
                    "f2_title" => "Pengiriman Cepat", "f2_desc" => "Sampai di hari yang sama.", "f2_icon" => "bi-lightning",
                    "f3_title" => "Produk Ori", "f3_desc" => "100% Original BNIB.", "f3_icon" => "bi-patch-check"
                ]
            ],
            [
                "id" => "products",
                "type" => "products",
                "visible" => true,
                "data" => [
                    "title" => "Produk Pilihan",
                    "limit" => 8
                ]
            ]
        ];

        // Buat Website
        Website::updateOrCreate(
            ['subdomain' => 'elecjos'], // Subdomain yang kita pakai test
            [
                'user_id' => $client->id,
                'site_name' => 'Toko Elektronik Jose',
                'primary_color' => '#0d6efd',
                'secondary_color' => '#6c757d',
                'sections' => $defaultSections, // Masukkan JSON default
                'active_template' => 'simple',
            ]
        );

        $this->command->info('Website Seeder Selesai: Toko ElecJos dibuat.');
    }
}
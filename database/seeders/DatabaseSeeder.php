<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PlatformSeeder::class,      // 1. Buat User Admin, User Klien, & Paket
            WebsiteSeeder::class,       // 2. Buat Toko untuk Klien
            SubscriptionSeeder::class,  // 3. Aktifkan Paket untuk Toko
            StoreContentSeeder::class,  // 4. Isi Produk, Kategori, & Artikel Dummy
        ]);
    }
}
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Website;
use App\Models\Category;
use App\Models\Product;
use App\Models\Post;

class StoreContentSeeder extends Seeder
{
    public function run(): void
    {
        // Cari Toko Target
        $website = Website::where('subdomain', 'elecjos')->first();
        if (!$website) return;

        // 1. Buat Kategori
        $cat1 = Category::create(['website_id' => $website->id, 'name' => 'Smartphone', 'slug' => 'smartphone']);
        $cat2 = Category::create(['website_id' => $website->id, 'name' => 'Laptop', 'slug' => 'laptop']);
        $cat3 = Category::create(['website_id' => $website->id, 'name' => 'Aksesoris', 'slug' => 'aksesoris']);

        // 2. Buat Produk Dummy
        $products = [
            ['name' => 'iPhone 15 Pro', 'price' => 20000000, 'category_id' => $cat1->id],
            ['name' => 'Samsung S24 Ultra', 'price' => 19000000, 'category_id' => $cat1->id],
            ['name' => 'MacBook Air M2', 'price' => 18500000, 'category_id' => $cat2->id],
            ['name' => 'Asus ROG Zephyrus', 'price' => 25000000, 'category_id' => $cat2->id],
            ['name' => 'Airpods Pro 2', 'price' => 3500000, 'category_id' => $cat3->id],
            ['name' => 'Logitech MX Master', 'price' => 1500000, 'category_id' => $cat3->id],
        ];

        foreach ($products as $prod) {
            Product::create([
                'website_id' => $website->id,
                'category_id' => $prod['category_id'],
                'name' => $prod['name'],
                'slug' => \Illuminate\Support\Str::slug($prod['name']),
                'price' => $prod['price'],
                'stock' => 10,
                'description' => 'Ini adalah deskripsi produk contoh untuk ' . $prod['name'],
                'is_active' => true,
                // Jika ingin gambar dummy, bisa pakai: 'image' => 'products/dummy.jpg'
            ]);
        }

        // 3. Buat Artikel Blog Dummy
        Post::create([
            'website_id' => $website->id,
            'title' => 'Cara Merawat Baterai Laptop',
            'slug' => 'cara-merawat-baterai',
            'content' => '<p>Tips agar baterai laptop awet...</p>',
            'status' => 'published',
        ]);

        Post::create([
            'website_id' => $website->id,
            'title' => '5 HP Gaming Terbaik 2026',
            'slug' => 'hp-gaming-terbaik-2026',
            'content' => '<p>Daftar HP gaming murah meriah...</p>',
            'status' => 'published',
        ]);

        $this->command->info('Store Content Seeder Selesai: Produk & Blog dibuat.');
    }
}
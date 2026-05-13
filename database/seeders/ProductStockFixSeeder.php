<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductVariant;

class ProductStockFixSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Memulai perbaikan stok produk...');

        // 1. Update Produk Induk yang stoknya di bawah 6
        $products = Product::where('stock', '<', 6)->get();
        $productCount = 0;
        foreach ($products as $product) {
            $product->update(['stock' => rand(15, 100)]);
            $productCount++;
        }

        // 2. Update Varian Produk yang stoknya di bawah 6 (Jika ada)
        $variants = ProductVariant::where('stock', '<', 6)->get();
        $variantCount = 0;
        foreach ($variants as $variant) {
            $variant->update(['stock' => rand(15, 100)]);
            $variantCount++;
        }

        $this->command->info("✅ Sukses! Diupdate: {$productCount} Produk Induk & {$variantCount} Varian.");
    }
}
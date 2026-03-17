<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\OrderItem;
use Carbon\Carbon;

class AnalyzeStockVelocity extends Command
{
    protected $signature = 'stock:analyze';
    protected $description = 'Menganalisis kecepatan penjualan (Velocity) dan prediksi habis stok (Runway).';

   public function handle()
    {
        $this->info('📦 Memulai Analisis Kecepatan Stok...');

        // 🚨 TERSANGKA 2: Ubah dari 30 menjadi 365 hari agar data lama terbaca
        $days = 365; 
        $startDate = \Carbon\Carbon::now()->subDays($days);
        $analyzedCount = 0;

        Product::chunk(100, function ($products) use ($days, $startDate, &$analyzedCount) {
            foreach ($products as $product) {
                
                $totalSold = OrderItem::where('product_id', $product->id)
                    ->whereHas('order', function ($q) use ($startDate) {
                        // 🚨 TERSANGKA 3: Pastikan ejaan status ini SAMA PERSIS dengan di database Anda
                        $q->whereIn('status', ['processing', 'shipped', 'completed']) 
                          ->where('created_at', '>=', $startDate);
                    })
                    // 🚨 TERSANGKA 1 (Bagian A): Pastikan nama kolom jumlah beli di order_items adalah 'quantity'
                    ->sum('qty'); 

                $velocity = $totalSold / $days;
                $runway = null;
                $status = 'Normal';

                // 🚨 TERSANGKA 1 (Bagian B): Ganti 'stock' dengan nama kolom yang benar di tabel products (misal: $product->qty)
                $currentStock = $product->stock; 
              // 🚨 STANDAR BARU: Minimal laku 3 barang per 30 hari (0.1 per hari)
                // Sesuaikan angka 3 ini dengan keinginan standar Klien Anda
                $minVelocityThreshold = 1 / 30; 

                if ($currentStock <= 0) {
                    $runway = 0;
                    $status = 'Empty';
                } else {
                    // 🚨 CEK STANDAR: Apakah kecepatannya memenuhi syarat minimal?
                    if ($velocity >= $minVelocityThreshold) {
                        $runway = (int) round($currentStock / $velocity);
                        $status = ($runway <= 7) ? 'Critical' : 'Safe';
                    } else {
                        // Jika kecepatan di bawah standar (walau laku 1-2 barang), tetep vonis Overstock!
                        $status = 'Overstock'; 
                    }
                }

                $product->update([
                    'velocity' => $velocity,
                    'runway_days' => $runway,
                    'stock_status' => $status
                ]);

                $analyzedCount++;
            }
        });

        $this->info("✅ Analisis selesai! Total {$analyzedCount} produk berhasil dianalisis.");
    }
}
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\ProductRecommendation;
use App\Models\Website;
use Illuminate\Support\Facades\DB;

class GenerateProductRecommendations extends Command
{
    protected $signature = 'recommend:generate';
    protected $description = 'Menghitung Market Basket Analysis (Support, Confidence, Lift) untuk rekomendasi produk AI.';

    public function handle()
    {
        $this->info('🚀 Memulai Proses Market Basket Analysis (MBA)...');

        $websites = Website::all();

        foreach ($websites as $website) {
            $this->info("Menganalisis Keranjang Belanja Toko: {$website->site_name}");

            // 1. Ambil semua pesanan yang valid beserta item di dalamnya
            // Pastikan relasi 'items' ada di model Order Anda (hasMany OrderItem)
            $orders = Order::where('website_id', $website->id)
                ->whereIn('status', ['processing', 'shipped', 'completed'])
                ->with('items') 
                ->get();

            $totalOrders = $orders->count();

            // Saringan (Threshold): Jika toko baru punya sedikit pesanan, lewati dulu
            if ($totalOrders < 5) {
                $this->warn("   -> Data transaksi terlalu sedikit (Kurang dari 5). Dilewati.");
                continue;
            }

            $itemFrequencies = []; // Menyimpan jumlah kemunculan barang individu
            $pairFrequencies = []; // Menyimpan jumlah kemunculan pasangan barang

            // 2. MENGHITUNG KEMUNCULAN (The Counting Process)
            foreach ($orders as $order) {
                // Ambil semua ID Produk unik di dalam satu keranjang pesanan ini
                $productIds = $order->items->pluck('product_id')->filter()->unique()->values()->toArray();
                
                // Urutkan ID agar konsisten saat membuat pasangan (Misal selalu 1_2, bukan 2_1)
                sort($productIds); 

                // A. Hitung kemunculan masing-masing barang
                foreach ($productIds as $pid) {
                    $itemFrequencies[$pid] = ($itemFrequencies[$pid] ?? 0) + 1;
                }

                // B. Hitung kemunculan PASANGAN barang di keranjang yang sama
                $count = count($productIds);
                for ($i = 0; $i < $count; $i++) {
                    for ($j = $i + 1; $j < $count; $j++) {
                        $p1 = $productIds[$i];
                        $p2 = $productIds[$j];
                        
                        // Buat kunci unik, contoh: "5_12" (Produk ID 5 dan 12)
                        $pairKey = $p1 . '_' . $p2; 
                        $pairFrequencies[$pairKey] = ($pairFrequencies[$pairKey] ?? 0) + 1;
                    }
                }
            }

            // 3. MENGHITUNG RUMUS & MENYIMPAN (The Math Process)
            // Bersihkan tabel rekomendasi lama untuk website ini agar datanya selalu segar
            ProductRecommendation::where('website_id', $website->id)->delete();

            $recommendationsSaved = 0;

            foreach ($pairFrequencies as $pairKey => $pairCount) {
                // Saringan: Pasangan harus dibeli bersamaan minimal 2 kali
                if ($pairCount < 2) continue; 

                // Pecah kunci "5_12" kembali menjadi angka 5 dan 12
                [$p1, $p2] = explode('_', $pairKey);

                // 🚨 GEMBOK EKSTRA: Jika salah satu ID kosong, lewati saja!
                if (empty($p1) || empty($p2)) continue;

                $freqA = $itemFrequencies[$p1];
                $freqB = $itemFrequencies[$p2];

                // --- MATEMATIKA ARAH 1: Jika beli Produk A, seberapa mungkin beli B? ---
                $supportAB = $pairCount / $totalOrders;
                $confAB    = $pairCount / $freqA;
                $supportB  = $freqB / $totalOrders;
                $liftAB    = $confAB / $supportB;

                // --- MATEMATIKA ARAH 2: Jika beli Produk B, seberapa mungkin beli A? ---
                $confBA    = $pairCount / $freqB;
                $supportA  = $freqA / $totalOrders;
                $liftBA    = $confBA / $supportA;

                // SIMPAN ARAH 1: A -> B (Syarat: Lift harus >= 1, artinya saling menarik)
                if ($liftAB >= 1) {
                    ProductRecommendation::create([
                        'website_id' => $website->id,
                        'product_id' => $p1,
                        'recommended_product_id' => $p2,
                        'support' => $supportAB,
                        'confidence' => $confAB,
                        'lift' => $liftAB
                    ]);
                    $recommendationsSaved++;
                }

                // SIMPAN ARAH 2: B -> A
                if ($liftBA >= 1) {
                    ProductRecommendation::create([
                        'website_id' => $website->id,
                        'product_id' => $p2,
                        'recommended_product_id' => $p1,
                        'support' => $supportAB, // Nilai support gabungan selalu sama
                        'confidence' => $confBA,
                        'lift' => $liftBA
                    ]);
                    $recommendationsSaved++;
                }
            }

            $this->info("   -> Berhasil membuat {$recommendationsSaved} aturan rekomendasi.");
        }

        $this->info('✅ Market Basket Analysis Selesai!');
    }
}
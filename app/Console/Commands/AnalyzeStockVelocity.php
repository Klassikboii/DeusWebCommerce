<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\OrderItem;
use Carbon\Carbon;

class AnalyzeStockVelocity extends Command
{
    protected $signature = 'stock:analyze';
    protected $description = 'Menganalisis kecepatan penjualan (Velocity) dan prediksi habis stok (Runway) berdasarkan Karakteristik Produk.';

    public function handle()
    {
        $this->info('📦 Memulai Otak Analisis Kecepatan Stok...');

        // Kita gunakan 30 hari agar tren yang ditangkap adalah tren terbaru (relevan untuk runway)
        $days = 30; 
        $startDate = Carbon::now()->subDays($days);
        $analyzedCount = 0;

        Product::chunk(100, function ($products) use ($days, $startDate, &$analyzedCount) {
            foreach ($products as $product) {
                
                // 1. Hitung total terjual 30 hari terakhir
                $totalSold = OrderItem::where('product_id', $product->id)
                    ->whereHas('order', function ($q) use ($startDate) {
                        $q->whereIn('status', ['processing', 'shipped', 'completed']) 
                          ->where('created_at', '>=', $startDate);
                    })
                    ->sum('qty'); 

                // 2. Hitung Velocity (Fakta Data)
                $velocity = $totalSold / $days;
                $currentStock = $product->stock;
                $runway = ($velocity > 0) ? (int) round($currentStock / $velocity) : null;
                $status = 'Safe';

                // 3. TENTUKAN AMBANG BATAS BERDASARKAN ATURAN KLIEN (Domain Knowledge)
                $movingClass = $product->moving_class ?? 'normal'; // Ambil data dari database

                if ($movingClass === 'fast') {
                    // Fast moving butuh nafas panjang. Sisa 14 hari sudah harus Kritis!
                    $criticalDays = 14; 
                    $warningDays  = 30;
                } elseif ($movingClass === 'slow') {
                    // Slow moving santai. Sisa 3 hari baru Kritis.
                    $criticalDays = 3;  
                    $warningDays  = 7;
                } else {
                    // Standar Normal
                    $criticalDays = 7;  
                    $warningDays  = 14;
                }

                // 4. EKSEKUSI PENENTUAN STATUS
                if ($currentStock <= 0) {
                    $status = 'Empty';
                } elseif ($runway !== null) {
                    if ($runway <= $criticalDays) {
                        $status = 'Critical';
                    } elseif ($runway <= $warningDays) {
                        $status = 'Warning';
                    }
                } else {
                    // Jika velocity 0 (tidak ada penjualan 30 hari terakhir)
                    $status = ($currentStock > 5) ? 'Dead Stock' : 'Safe';
                }

                // 5. Simpan Hasil Analisis
                $product->update([
                    'velocity'     => $velocity,
                    'runway_days'  => $runway,
                    'stock_status' => $status
                ]);

                $analyzedCount++;
                
                // CATATAN: Jika Anda ingin menghitung varian juga, Anda bisa melooping $product->variants() 
                // di sini dengan logika yang persis sama.
            }
        });

        $this->info("✅ Analisis cerdas selesai! Total {$analyzedCount} produk berhasil diupdate.");
    }
}
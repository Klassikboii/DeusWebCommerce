<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\CustomerRfm;
use App\Models\Website;
use Carbon\Carbon;

class CalculateRFM extends Command
{
    protected $signature = 'rfm:calculate';
    protected $description = 'Menghitung skor RFM dan mensegmentasi pelanggan untuk setiap toko (Data Science Engine)';

    public function handle()
    {
        $this->info('🚀 Memulai Proses Analisis RFM...');

        $websites = Website::all();
        $now = Carbon::now();

        foreach ($websites as $website) {
            $this->info("Menganalisis Toko: {$website->site_name}");

            // 1. Ambil semua order yang valid (Sudah dibayar/selesai)
            $orders = Order::where('website_id', $website->id)
                ->whereIn('status', ['processing', 'shipped', 'completed'])
                ->get();

            if ($orders->isEmpty()) continue;

            // 2. Kelompokkan berdasarkan Nomor WhatsApp (Grup Pelanggan)
            $customerGroups = $orders->groupBy('customer_whatsapp');
            $rawData = [];

            foreach ($customerGroups as $whatsapp => $custOrders) {
                $lastOrderDate = $custOrders->max('created_at');
                
                $rawData[] = [
                    'whatsapp' => $whatsapp,
                    'name'     => $custOrders->first()->customer_name,
                    'recency'  => $now->diffInDays($lastOrderDate), // Jarak hari
                    'frequency'=> $custOrders->count(),             // Jumlah transaksi
                    'monetary' => $custOrders->sum('total_amount')  // Total uang
                ];
            }

            // 3. Hitung Skor 1-5 menggunakan Distribusi Kuantil (Statistika Terapan)
            $this->scoreAndSegmentCustomers($website->id, $rawData);
        }

        $this->info('✅ Analisis RFM Selesai!');
    }

    private function scoreAndSegmentCustomers($websiteId, $rawData)
    {
        // Ekstrak array untuk mencari nilai Max & Min
        $recencies = array_column($rawData, 'recency');
        $frequencies = array_column($rawData, 'frequency');
        $monetaries = array_column($rawData, 'monetary');

        // Fungsi Helper untuk membagi ke dalam 5 level skor (1 terburuk, 5 terbaik)
        $getScore = function($val, $arr, $isRecency = false) {
            $min = min($arr);
            $max = max($arr);
            if ($max == $min) return 5; // Jika data terlalu sedikit, beri nilai maksimal
            
            // Rumus Normalisasi 1-5
            $score = ceil((($val - $min) / ($max - $min)) * 5);
            $score = max(1, min(5, $score)); // Pastikan tidak keluar dari 1-5

            // Untuk Recency (Keterbaruan), nilai terkecil (0 hari) adalah yang TERBAIK (Skor 5)
            if ($isRecency) {
                return 6 - $score; // Dibalik: 1 jadi 5, 5 jadi 1
            }
            return $score;
        };

        // 4. Hitung Skor dan Tentukan Segmen
        foreach ($rawData as $data) {
            $r = $getScore($data['recency'], $recencies, true);
            $f = $getScore($data['frequency'], $frequencies);
            $m = $getScore($data['monetary'], $monetaries);

            $segment = $this->determineSegment($r, $f, $m);

            // Simpan hasil ke Database
            CustomerRfm::updateOrCreate(
                ['website_id' => $websiteId, 'customer_whatsapp' => $data['whatsapp']],
                [
                    'customer_name'   => $data['name'],
                    'recency_days'    => $data['recency'],
                    'frequency_count' => $data['frequency'],
                    'monetary_value'  => $data['monetary'],
                    'r_score'         => $r,
                    'f_score'         => $f,
                    'm_score'         => $m,
                    'segment'         => $segment
                ]
            );
        }
    }

    private function determineSegment($r, $f, $m)
    {
        $rfmAverage = ($r + $f + $m) / 3;

        // Aturan Pohon Keputusan (Decision Tree Logic)
        if ($r >= 4 && $f >= 4 && $m >= 4) {
            return 'Champions'; // Pelanggan terbaik, baru belanja, sering, uang banyak
        } elseif ($r >= 3 && $f >= 3) {
            return 'Loyal Customers'; // Setia belanja
        } elseif ($r >= 4 && $f <= 2) {
            return 'New / Recent Customers'; // Pelanggan baru
        } elseif ($r <= 2 && $f >= 3) {
            return 'At Risk'; // Dulu sering belanja, sekarang menghilang (Butuh promo!)
        } elseif ($r <= 2 && $f <= 2) {
            return 'Hibernating'; // Belanja sesekali di masa lalu, lalu hilang
        } else {
            return 'Potential / Needs Attention'; // Rata-rata
        }
    }
}
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

            // 1. Ambil order yang valid DAN WAJIB punya customer_id (Abaikan Guest)
            $orders = Order::where('website_id', $website->id)
                ->whereNotNull('customer_id') // 🚨 PENTING: Hanya pelanggan terdaftar
                ->whereIn('status', ['processing', 'shipped', 'completed'])
                ->get();

            if ($orders->isEmpty()) continue;

            // 2. Kelompokkan berdasarkan Customer ID (Bukan WA lagi)
            $customerGroups = $orders->groupBy('customer_id');
            $rawData = [];

            foreach ($customerGroups as $customerId => $custOrders) {
                $lastOrderDate = Carbon::parse($custOrders->max('created_at'));
                
                $rawData[] = [
                    'customer_id' => $customerId,
                    // 🚨 Gunakan ABS() agar jika ada dummy order dari masa depan, angkanya tidak minus
                    'recency'  => abs($now->diffInDays($lastOrderDate)), 
                    'frequency'=> $custOrders->count(),
                    'monetary' => $custOrders->sum('total_amount')
                ];
            }

            $this->scoreAndSegmentCustomers($website->id, $rawData);
        }

        $this->info('✅ Analisis RFM Selesai!');
    }

    private function scoreAndSegmentCustomers($websiteId, $rawData)
    {
        $recencies = array_column($rawData, 'recency');
        $frequencies = array_column($rawData, 'frequency');
        $monetaries = array_column($rawData, 'monetary');

        $getScore = function($val, $arr, $isRecency = false) {
            $min = min($arr);
            $max = max($arr);
            if ($max == $min) return 5; 
            
            $score = ceil((($val - $min) / ($max - $min)) * 5);
            $score = max(1, min(5, $score)); 

            if ($isRecency) {
                return 6 - $score; 
            }
            return $score;
        };

        foreach ($rawData as $data) {
            $r = $getScore($data['recency'], $recencies, true);
            $f = $getScore($data['frequency'], $frequencies);
            $m = $getScore($data['monetary'], $monetaries);

            $segment = $this->determineSegment($r, $f, $m);

            // 🚨 Simpan menggunakan Customer ID
            CustomerRfm::updateOrCreate(
                ['website_id' => $websiteId, 'customer_id' => $data['customer_id']],
                [
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
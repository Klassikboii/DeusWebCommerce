<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Website;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderHistory;
use App\Services\AccurateService;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DummyOrderSeeder extends Seeder
{
    public function run()
    {
        // 1. Cari toko yang sudah terhubung ke Accurate
        // Ganti 1 dengan ID website yang sedang Anda buka di browser
            $website = Website::find(1);

        if (!$website) {
            $this->command->error('Gagal: Tidak ada toko yang terhubung ke Accurate!');
            return;
        }

        // // 1. Ambil hanya produk yang stoknya lebih dari 0
        //     $products = $website->products()->where('stock', '>', 0)->get();

        //     if ($products->count() == 0) {
        //         $this->command->error('Gagal: Toko ini tidak memiliki produk yang memiliki stok (Semua Stok 0)!');
        //         return;
        //     }

        $this->command->info("Memulai Seeding untuk Toko: {$website->site_name}");
        $this->command->warn("Mohon tunggu, proses ini butuh waktu karena menembak API Accurate...");
        
        $accurateService = new AccurateService($website);

        // 2. Kita buat 15 Transaksi Dummy (Jangan terlalu banyak agar API Accurate tidak limit)
        for ($i = 0; $i < 15; $i++) {
            $products = $website->products()->where('stock', '>', 0)->get();

                if ($products->isEmpty()) {
                    $this->command->warn("Stok habis, stop seeding.");
                    break;
                }
            // Putar waktu ke masa lalu (Random antara 1 sampai 70 hari yang lalu dari hari ini)
            $randomDate = Carbon::now()->subDays(rand(1, 70));

            // Buat Induk Pesanan
            $order = Order::create([
                'website_id'        => $website->id,
                'order_number'      => 'INV-DS-' . $randomDate->format('Ymd') . '-' . Str::random(4),
                'customer_name'     => 'Pelanggan Dummy ' . rand(1, 10), // Angka rand ini berguna untuk RFM Analysis nanti
                'customer_whatsapp' => '081234567' . rand(100, 999),
                'customer_address'  => 'Jl. Data Science No ' . rand(1, 99) . ', Surabaya',
                'shipping_cost'     => rand(10, 30) * 1000,
                'courier_name'      => 'JNE',
                'courier_service'   => 'REG',
                'total_amount'      => 0, // Diupdate di bawah
                'status'            => 'processing', // Langsung diproses agar masuk Accurate
                'created_at'        => $randomDate,
                'updated_at'        => $randomDate,
            ]);

            $totalAmount = 0;
            // Pilih 1 sampai 3 produk secara acak untuk dibeli di pesanan ini
            $countToTake = min($products->count(), rand(1, 100));
            $selectedProducts = $products->random($countToTake);

            foreach ($selectedProducts as $product) {
                        $product->refresh();

                if ($product->stock <= 0) continue;

                $qty = min(rand(1, 3), $product->stock);
                if ($qty <= 0) continue;

                $price = $product->price > 0 ? $product->price : rand(50000, 150000);
                $subtotal = $qty * $price;

                OrderItem::create([
                    'order_id'      => $order->id,
                    'product_id'    => $product->id,
                    'product_name'  => $product->name,
                    'price'         => $price,
                    'qty'           => $qty,
                    'subtotal'      => $subtotal,
                    'created_at'    => $randomDate,
                    'updated_at'    => $randomDate,
                ]);

                $totalAmount += $subtotal;
                $product->decrement('stock', $qty);
            }

            // Update Total Belanja
            $order->update(['total_amount' => $totalAmount]);

            // Riwayat
            OrderHistory::create([
                'order_id'   => $order->id,
                'status'     => 'processing',
                'note'       => 'Pesanan dummy untuk Data Science generated.',
                'created_at' => $randomDate,
                'updated_at' => $randomDate,
            ]);

            $this->command->info("Order {$order->order_number} dibuat tanggal {$randomDate->format('d M Y')}. Sinkronisasi Accurate...");

            // 3. Tembak API Accurate
            try {
                $invoiceCreated = $accurateService->syncSalesInvoice($order);
                if ($invoiceCreated) {
                    $accurateService->syncPaymentReceipt($order);
                    $this->command->info("   -> [SUKSES] Masuk Accurate!");
                }
            } catch (\Exception $e) {
                $this->command->error("   -> [GAGAL] Accurate Error: " . $e->getMessage());
            }
        }

        $this->command->info("🎉 SEEDING SELESAI! Silakan refresh Dashboard Admin & Accurate Anda!");
    }
}
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Website;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderHistory;
use Carbon\Carbon;
use Illuminate\Support\Str;

class LocalDummyOrderSeeder extends Seeder
{
    public function run()
    {
        // 1. Cari toko
        // Ganti 1 dengan ID website yang sedang Anda jadikan target eksperimen
        $website = Website::find(1);

        if (!$website) {
            $this->command->error('Gagal: Tidak ada website dengan ID tersebut!');
            return;
        }

        $this->command->info("Memulai Local Seeding (Tanpa Accurate) untuk Toko: {$website->site_name}");
        
        // Karena tidak ada limit API, kita bisa buat lebih banyak data untuk testing Data Science
        $totalOrders = 50; 
        $this->command->warn("Akan membuat {$totalOrders} pesanan dummy...");

        // 2. Looping Pembuatan Transaksi Dummy
        for ($i = 0; $i < $totalOrders; $i++) {
            $products = $website->products()->where('stock', '>', 0)->get();

            if ($products->isEmpty()) {
                $this->command->warn("Stok semua produk habis, stop seeding pada iterasi ke-{$i}.");
                break;
            }

            // Putar waktu ke masa lalu (Random antara 1 sampai 70 hari yang lalu dari hari ini)
            $randomDate = Carbon::now()->subDays(rand(1, 70));

            // Buat Induk Pesanan
            $order = Order::create([
                'website_id'        => $website->id,
                'order_number'      => 'INV-LOCAL-' . $randomDate->format('Ymd') . '-' . Str::random(4),
                'customer_name'     => 'Pelanggan Dummy ' . rand(1, 20), 
                'customer_whatsapp' => '081234567' . rand(100, 999),
                'customer_address'  => 'Jl. Data Science No ' . rand(1, 99) . ', Surabaya',
                'shipping_cost'     => rand(10, 30) * 1000,
                'courier_name'      => 'JNE',
                'courier_service'   => 'REG',
                'total_amount'      => 0, // Diupdate di bawah
                'status'            => 'completed', // Langsung selesai agar masuk hitungan Velocity
                'created_at'        => $randomDate,
                'updated_at'        => $randomDate,
            ]);

            $totalAmount = 0;
            
            // Beli 1 sampai 3 macam produk berbeda per pesanan
            $countToTake = min($products->count(), rand(1, 3));
            $selectedProducts = $products->random($countToTake);

            foreach ($selectedProducts as $product) {
                $product->refresh();

                if ($product->stock <= 0) continue;

                // Beli 1 sampai 5 pcs per barang
                $qty = min(rand(1, 5), $product->stock);
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
                
                // Kurangi stok fisik di database
                $product->decrement('stock', $qty);
            }

            // Update Total Belanja Induk
            $order->update(['total_amount' => $totalAmount]);

            // Riwayat
            OrderHistory::create([
                'order_id'   => $order->id,
                'status'     => 'completed',
                'note'       => 'Pesanan lokal untuk Data Science generated.',
                'created_at' => $randomDate,
                'updated_at' => $randomDate,
            ]);

            $this->command->info("Order {$order->order_number} dibuat tanggal {$randomDate->format('d M Y')}.");
        }

        $this->command->info("🎉 LOCAL SEEDING SELESAI! Data Science Engine siap diuji coba.");
    }
}
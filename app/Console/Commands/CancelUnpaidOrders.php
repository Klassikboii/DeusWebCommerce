<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Carbon\Carbon;

class CancelUnpaidOrders extends Command
{
    protected $signature = 'orders:cancel-unpaid';
    protected $description = 'Batalkan pesanan yang belum dibayar lebih dari 24 jam';

    public function handle()
    {
        // Cari order yang statusnya 'pending' DAN dibuat lebih dari 24 jam lalu
        $expiredOrders = Order::where('status', 'pending')
                              ->where('created_at', '<', Carbon::now()->subHours(1))
                              ->get();

        foreach ($expiredOrders as $order) {
            $this->info("Membatalkan Order: {$order->order_number}");

            // KEMBALIKAN STOK
            foreach ($order->items as $item) {
                // 1. Kembalikan Stok Varian
                if ($item->variant_id && $item->variant) {
                    $item->variant->increment('stock', $item->qty);
                }
                
                // 2. Kembalikan Stok Produk Utama
                if ($item->product) {
                    $item->product->increment('stock', $item->qty);
                }
            }

            // Ubah status jadi cancelled
            $order->update(['status' => 'cancelled']);
        }

        $this->info("Selesai. " . $expiredOrders->count() . " pesanan dibatalkan.");
    }
}
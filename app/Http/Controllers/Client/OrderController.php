<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Website;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // 1. Tampilkan Daftar Order Masuk
    public function index(Website $website)
    {
        $this->authorize('viewAny', $website);

        // Ambil order terbaru beserta item-nya
        $orders = $website->orders()->with('items')->latest()->paginate(10);
        
        return view('client.orders.index', compact('website', 'orders'));
    }

    // 2. Tampilkan Detail Order (Invoice)
    public function show(Website $website, Order $order)
    {
        $this->authorize('view', $website);

        // Load detail item produknya
        $order->load('items.product', 'histories');
        
        return view('client.orders.show', compact('website', 'order'));
    }

    // 3. Update Status Order (Terima/Kirim/Batal)
    public function update(Request $request, Website $website, Order $order)
    {
        $this->authorize('update', $website);

        $request->validate([
            'status' => 'required|in:pending,processing,shipped,completed,cancelled',
            'tracking_number' => 'required_if:status,shipped',
            'courier_name' => 'required_if:status,shipped',
        ]);

        // Simpan status lama untuk perbandingan
        $oldStatus = $order->status;
        $newStatus = $request->status;

        // --- LOGIKA MANAJEMEN STOK ---

        // SKENARIO 1: Order Dibatalkan (Active -> Cancelled)
        // Kita harus MENGEMBALIKAN (Increment) stok ke produk
        if ($newStatus == 'cancelled' && $oldStatus != 'cancelled') {
            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->qty);
                }
            }
        }

        // SKENARIO 2: Order Batal Dibatalkan / Diaktifkan Lagi (Cancelled -> Active)
        // Kita harus MENGURANGI (Decrement) stok lagi.
        // TAPI: Cek dulu apakah stoknya masih ada?
        if ($oldStatus == 'cancelled' && $newStatus != 'cancelled') {
            foreach ($order->items as $item) {
                // Cek stok saat ini
                if (!$item->product || $item->product->stock < $item->qty) {
                    return redirect()->back()->with('error', "Gagal mengubah status! Stok produk '{$item->product_name}' tidak mencukupi untuk mengaktifkan kembali pesanan ini.");
                }
            }

            // Jika semua stok aman, baru kurangi
            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->decrement('stock', $item->qty);
                }
            }
        }

        // --- UPDATE DATA ---
        
        $order->update([
            'status' => $request->status,
            'courier_name' => $request->courier_name,
            'tracking_number' => $request->tracking_number,
        ]);

        // --- SIMPAN HISTORY ---
        
        // Buat pesan otomatis berdasarkan perubahan
        $note = $request->note;
        if(!$note) {
            if($newStatus == 'shipped') {
                $note = "Pesanan dikirim via {$request->courier_name}. Resi: {$request->tracking_number}";
            } elseif ($newStatus == 'cancelled') {
                $note = "Pesanan dibatalkan oleh Admin. Stok dikembalikan.";
            } else {
                $note = "Status diperbarui menjadi " . ucfirst($request->status);
            }
        }

        \App\Models\OrderHistory::create([
            'order_id' => $order->id,
            'status' => $request->status,
            'note' => $note, 
        ]);

        return redirect()->back()->with('success', 'Status pesanan berhasil diperbarui!');
    }
    
}
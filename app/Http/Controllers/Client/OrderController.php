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
    // app/Http/Controllers/Client/OrderController.php

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

    // --- LOGIKA MANAJEMEN STOK (DIPERBARUI DENGAN VARIAN) ---

    // SKENARIO 1: Order Dibatalkan (Active -> Cancelled)
    // Kita harus MENGEMBALIKAN (Increment) stok
    if ($newStatus == 'cancelled' && $oldStatus != 'cancelled') {
        foreach ($order->items as $item) {
            
            // A. Cek apakah ini barang Varian?
            if ($item->variant_id && $item->variant) {
                // Kembalikan stok varian
                $item->variant->increment('stock', $item->qty);
                
                // Opsional: Kembalikan stok induk juga (jika Anda sync total stok)
                if($item->product) $item->product->increment('stock', $item->qty);
            } 
            
            // B. Barang Biasa (Non-Varian)
            elseif ($item->product) {
                $item->product->increment('stock', $item->qty);
            }
        }
    }

    // SKENARIO 2: Order Batal Dibatalkan / Diaktifkan Lagi (Cancelled -> Active)
    // Kita harus MENGURANGI (Decrement) stok lagi.
    if ($oldStatus == 'cancelled' && $newStatus != 'cancelled') {
        
        // LANGKAH A: Validasi Stok Dulu (Jangan kurangi kalau ada satu pun yang habis)
        foreach ($order->items as $item) {
            
            // Cek Stok Varian
            if ($item->variant_id && $item->variant) {
                if ($item->variant->stock < $item->qty) {
                    return redirect()->back()->with('error', "Gagal! Stok varian '{$item->product_name}' cuma sisa {$item->variant->stock}.");
                }
            } 
            // Cek Stok Produk Biasa
            elseif ($item->product) {
                if ($item->product->stock < $item->qty) {
                    return redirect()->back()->with('error', "Gagal! Stok produk '{$item->product_name}' cuma sisa {$item->product->stock}.");
                }
            }
        }

        // LANGKAH B: Jika semua aman, baru kurangi (Decrement)
        foreach ($order->items as $item) {
            if ($item->variant_id && $item->variant) {
                $item->variant->decrement('stock', $item->qty);
                if($item->product) $item->product->decrement('stock', $item->qty);
            } elseif ($item->product) {
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

    // --- SIMPAN HISTORY (LOGIKA CUSTOM ANDA) ---
    
    // Gunakan note dari request jika ada, jika tidak pakai template default
    $note = $request->note;
    
    if(!$note) {
        if($newStatus == 'shipped') {
            $note = "Pesanan dikirim via {$request->courier_name}. Resi: {$request->tracking_number}";
        } elseif ($newStatus == 'cancelled') {
            $note = "Pesanan dibatalkan oleh Admin. Stok dikembalikan.";
        } else {
            // Ubah 'pending' jadi 'Pending', dll
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
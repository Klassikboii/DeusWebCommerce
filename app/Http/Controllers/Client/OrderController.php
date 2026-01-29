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
        if ($website->user_id !== auth()->id()) abort(403);

        // Ambil order terbaru beserta item-nya
        $orders = $website->orders()->with('items')->latest()->paginate(10);
        
        return view('client.orders.index', compact('website', 'orders'));
    }

    // 2. Tampilkan Detail Order (Invoice)
    public function show(Website $website, Order $order)
    {
        if ($order->website_id !== $website->id) abort(403);

        // Load detail item produknya
        $order->load('items.product', 'histories');
        
        return view('client.orders.show', compact('website', 'order'));
    }

    // 3. Update Status Order (Terima/Kirim/Batal)
    public function update(Request $request, Website $website, Order $order)
    {
        if ($website->user_id !== auth()->id()) abort(403);

        $request->validate([
            'status' => 'required|in:pending,processing,shipped,completed,cancelled',
            // Resi wajib diisi HANYA jika status diubah jadi 'shipped'
            'tracking_number' => 'required_if:status,shipped',
            'courier_name' => 'required_if:status,shipped',
        ]);

        // 1. Update Order Utama
        $order->update([
            'status' => $request->status,
            'courier_name' => $request->courier_name,
            'tracking_number' => $request->tracking_number,
        ]);

        // 2. Simpan History / Catatan
        // Kita juga bisa tambahkan input 'note' di form view nanti
        \App\Models\OrderHistory::create([
            'order_id' => $order->id,
            'status' => $request->status,
            'note' => $request->note ?? 'Status diperbarui menjadi ' . $request->status, // Default message
        ]);

        return redirect()->back()->with('success', 'Status diperbarui!');
    
    }
}
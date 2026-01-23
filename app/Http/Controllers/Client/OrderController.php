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
        $order->load('items');
        
        return view('client.orders.show', compact('website', 'order'));
    }

    // 3. Update Status Order (Terima/Kirim/Batal)
    public function update(Request $request, Website $website, Order $order)
    {
        if ($order->website_id !== $website->id) abort(403);
        
        $request->validate(['status' => 'required|in:pending,processing,shipped,completed,cancelled']);

        $order->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Status pesanan diperbarui menjadi ' . ucfirst($request->status));
    }
}
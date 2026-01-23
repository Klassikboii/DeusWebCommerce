<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Website;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    // Fungsi Menambah Produk ke Session Cart
    public function addToCart(Request $request, $subdomain, $productId)
    {
        $product = Product::findOrFail($productId);
        
        // Ambil cart lama dari session (atau array kosong jika belum ada)
        $cart = session()->get('cart', []);

        // Jika produk sudah ada, tambah jumlahnya
        if(isset($cart[$productId])) {
            $cart[$productId]['qty']++;
        } else {
            // Jika belum ada, masukkan data baru
            $cart[$productId] = [
                "name" => $product->name,
                "qty" => 1,
                "price" => $product->price,
                "image" => $product->image
            ];
        }

        // Simpan kembali ke session
        session()->put('cart', $cart);

        // Redirect kembali dengan pesan sukses
        return redirect()->back()->with('success', 'Produk masuk keranjang!');
    }
    
    // Halaman Cart / Checkout
    public function cart(Request $request, $subdomain)
    {
        $website = Website::where('subdomain', $subdomain)->firstOrFail();
        return view('templates.modern.cart', compact('website'));
    }
    public function processCheckout(Request $request, $subdomain)
    {
        // 1. Cek Keranjang
        $cart = session()->get('cart');
        if(!$cart) {
            return redirect()->back()->with('error', 'Keranjang kosong!');
        }

        $website = Website::where('subdomain', $subdomain)->firstOrFail();

        // 2. Validasi Input
        $request->validate([
            'customer_name' => 'required|string|max:100',
            'customer_whatsapp' => 'required|numeric',
            'customer_address' => 'required|string',
        ]);

        // 3. Hitung Total
        $totalAmount = 0;
        foreach($cart as $item) {
            $totalAmount += $item['price'] * $item['qty'];
        }

        // 4. Simpan Header Order (Tabel orders)
        $order = \App\Models\Order::create([
            'website_id' => $website->id,
            'order_number' => 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
            'customer_name' => $request->customer_name,
            'customer_whatsapp' => $request->customer_whatsapp,
            'customer_address' => $request->customer_address,
            'total_amount' => $totalAmount,
            'status' => 'pending', // Status awal: Menunggu
        ]);

        // 5. Simpan Detail Item (Tabel order_items)
        foreach($cart as $productId => $item) {
            \App\Models\OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $productId,
                'product_name' => $item['name'], // Snapshot nama
                'product_image' => $item['image'],
                'price' => $item['price'],       // Snapshot harga
                'qty' => $item['qty'],
                'subtotal' => $item['price'] * $item['qty'],
            ]);
        }

        // 6. Hapus Keranjang & Redirect Sukses
        session()->forget('cart');

        // Untuk sementara kita return text dulu
        return redirect()->route('store.home', $subdomain)
            ->with('success', "Pesanan berhasil! No Invoice: " . $order->order_number . ". Silakan cek WhatsApp Anda untuk instruksi pembayaran.");
    }
    // UPDATE JUMLAH BARANG
    public function updateCart(Request $request, $subdomain)
    {
        $request->validate([
            'id' => 'required',
            'qty' => 'required|numeric|min:1'
        ]);

        $cart = session()->get('cart');

        if(isset($cart[$request->id])) {
            $cart[$request->id]['qty'] = $request->qty;
            session()->put('cart', $cart);
            return redirect()->back()->with('success', 'Keranjang diperbarui!');
        }
    }

    // HAPUS BARANG
    public function removeFromCart($subdomain, $id)
    {
        $cart = session()->get('cart');

        if(isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        }

        return redirect()->back()->with('success', 'Produk dihapus dari keranjang.');
    }
}
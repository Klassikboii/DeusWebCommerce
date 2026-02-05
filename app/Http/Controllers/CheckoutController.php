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
    // 1. ADD TO CART (Perbaikan: Pakai 'quantity')
    public function addToCart(Request $request, $id)
    {
        $website = $request->attributes->get('website');
        $product = Product::where('website_id', $website->id)
                          ->where('id', $id)
                          ->firstOrFail();

        $cartKey = 'cart_' . $website->id;
        $cart = session()->get($cartKey, []);

        // --- CEK APAKAH PRODUK SUDAH ADA? ---
        if (isset($cart[$id])) {
            
            // FIX: Self-Healing (Perbaikan Otomatis Data Lama)
            // Jika di dalam session adanya 'qty' (lama), kita migrasi ke 'quantity' (baru)
            if (!isset($cart[$id]['quantity']) && isset($cart[$id]['qty'])) {
                $cart[$id]['quantity'] = $cart[$id]['qty']; // Pindahkan nilai
                unset($cart[$id]['qty']); // Hapus key lama
            }
            
            // Jaga-jaga jika benar-benar kosong
            if (!isset($cart[$id]['quantity'])) {
                $cart[$id]['quantity'] = 0;
            }

            // Sekarang aman untuk di-increment
            $cart[$id]['quantity']++; 
            
        } else {
            // ITEM BARU
            $cart[$id] = [
                "name" => $product->name,
                "quantity" => 1,          // Konsisten pakai 'quantity'
                "price" => $product->price,
                "image" => $product->image,
                "weight" => $product->weight ?? 1000,
                "product_id" => $product->id
            ];
        }

        session()->put($cartKey, $cart);

        if (request()->wantsJson()) {
            return response()->json([
                'message' => 'Produk berhasil ditambahkan!', 
                'cart_count' => count($cart)
            ]);
        }

        return redirect()->back()->with('success', 'Produk masuk keranjang!');
    }
    
    // 2. HALAMAN CART
    public function cart(Request $request)
    {
        $website = $request->attributes->get('website');
        $cartKey = 'cart_' . $website->id;
        $cart = session()->get($cartKey, []);

        $total = 0;
        foreach($cart as $item) {
            // Sekarang aman karena addToCart sudah pakai 'quantity'
            $qty = $item['quantity'] ?? 1; 
            $total += $item['price'] * $qty;
        }

        return view('storefront.cart', compact('website', 'cart', 'total')); 
    }

    // 3. PROCESS CHECKOUT
    public function processCheckout(Request $request)
    {
        $website = $request->attributes->get('website');
        $cartKey = 'cart_' . $website->id;
        $cart = session()->get($cartKey);
        
        if(!$cart) return redirect()->back()->with('error', 'Keranjang kosong!');

        // 1. VALIDASI STOK DULU (PENTING!)
        // Jangan sampai orang checkout barang yang sudah habis
        foreach($cart as $productId => $item) {
            $product = Product::find($productId);
            $qtyRequested = $item['quantity'] ?? 1;

            // Jika produk tidak ditemukan atau stok kurang
            if (!$product || $product->stock < $qtyRequested) {
                return redirect()->back()->with('error', "Maaf, stok produk '{$item['name']}' tidak mencukupi. Sisa stok: " . ($product->stock ?? 0));
            }
        }

        $request->validate([
            'customer_name' => 'required|string|max:100',
            'customer_whatsapp' => 'required|numeric',
            'customer_address' => 'required|string',
        ]);

        $totalAmount = 0;
        foreach($cart as $item) {
            $qty = $item['quantity'] ?? 1;
            $totalAmount += $item['price'] * $qty;
        }

        // Mulai Database Transaction (Biar aman, kalau error di tengah, stok balik lagi)
        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            // 2. Simpan Header Order
            $order = Order::create([
                'website_id' => $website->id,
                'order_number' => 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                'customer_name' => $request->customer_name,
                'customer_whatsapp' => $request->customer_whatsapp, // Pastikan format 628...
                'customer_address' => $request->customer_address,
                'total_amount' => $totalAmount,
                'status' => 'pending',
            ]);

            // 3. Simpan Detail & KURANGI STOK
            foreach($cart as $productId => $item) {
                $qty = $item['quantity'] ?? 1;
                
                // Ambil Produk Asli untuk dikurangi stoknya
                $product = Product::find($productId);
                
                // KURANGI STOK DISINI
                $product->decrement('stock', $qty);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'product_name' => $item['name'],
                    'product_image' => $item['image'] ?? null,
                    'price' => $item['price'],
                    'qty' => $qty,
                    'subtotal' => $item['price'] * $qty,
                ]);
            }

            \Illuminate\Support\Facades\DB::commit(); // Simpan perubahan permanen
            
            session()->forget($cartKey); 

            // Redirect dengan pesan sukses + Link WA Konfirmasi
            return redirect()->route('store.home')
                ->with('success', "Pesanan berhasil! Stok produk telah diamankan untuk Anda. No Invoice: " . $order->order_number);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack(); // Batalkan semua jika error
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses pesanan. Silakan coba lagi.');
        }
    }

    // 4. UPDATE CART
    public function updateCart(Request $request)
    {
        $website = $request->attributes->get('website');
        $cartKey = 'cart_' . $website->id;
        
        $request->validate(['id' => 'required', 'qty' => 'required|numeric|min:1']);

        $cart = session()->get($cartKey);

        if(isset($cart[$request->id])) {
            // Input name dari form tetap 'qty', tapi simpan ke session sebagai 'quantity'
            $cart[$request->id]['quantity'] = $request->qty; 
            session()->put($cartKey, $cart);
            return redirect()->back()->with('success', 'Keranjang diperbarui!');
        }
    }

    // 5. REMOVE FROM CART
    public function removeFromCart(Request $request, $id)
    {
        $website = $request->attributes->get('website');
        $cartKey = 'cart_' . $website->id;
        $cart = session()->get($cartKey, []);

        if(isset($cart[$id])) {
            unset($cart[$id]);
            session()->put($cartKey, $cart);
        }

        return redirect()->back()->with('success', 'Produk dihapus dari keranjang.');
    }
}
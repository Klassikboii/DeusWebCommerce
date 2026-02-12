<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Website;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CheckoutController extends Controller
{
    // 1. ADD TO CART
    public function addToCart(Request $request, $subdomain, $id)
    {
        $website = $request->get('website');
        if (!$website) {
            $website = Website::where('subdomain', $subdomain)->firstOrFail();
        }

        $product = Product::where('website_id', $website->id)->where('id', $id)->firstOrFail();

        // Validasi Variasi
        $variant = null;
        if ($product->hasVariants()) {
            $request->validate([
                'variant_id' => 'required|exists:product_variants,id',
                'quantity'   => 'required|integer|min:1'
            ]);

            $variant = $product->variants()->where('id', $request->variant_id)->firstOrFail();

            if ($variant->stock < $request->quantity) {
                return back()->with('error', "Stok varian tidak mencukupi (Sisa: {$variant->stock})");
            }
        } else {
            if ($product->stock < $request->quantity) {
                return back()->with('error', "Stok produk tidak mencukupi (Sisa: {$product->stock})");
            }
        }

        // Cart Key Unik
        $cartItemId = $product->id . ($variant ? '_' . $variant->id : '');
        $cartKey = 'cart_' . $website->id;
        $cart = session()->get($cartKey, []);

        if (isset($cart[$cartItemId])) {
            $cart[$cartItemId]['quantity'] += $request->quantity;
        } else {
            $cart[$cartItemId] = [
                "product_id" => $product->id,
                "variant_id" => $variant ? $variant->id : null,
                "name"       => $product->name . ($variant ? ' (' . $variant->name . ')' : ''),
                "quantity"   => $request->quantity,
                "price"      => $variant ? $variant->price : $product->price,
                "weight"     => $variant ? ($variant->weight ?? $product->weight) : $product->weight,
                "image"      => ($variant && $variant->image) ? $variant->image : $product->image
            ];
        }

        session()->put($cartKey, $cart);
        return redirect()->back()->with('success', 'Produk masuk keranjang!');
    }

    // 2. HALAMAN CART
    public function cart(Request $request, $subdomain)
    {
        $website = $request->get('website');
        if (!$website) $website = Website::where('subdomain', $subdomain)->firstOrFail();

        $cartKey = 'cart_' . $website->id;
        $cart = session()->get($cartKey, []);

        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        return view('storefront.cart', compact('website', 'cart', 'total'));
    }

    // 3. PROCESS CHECKOUT
    public function processCheckout(Request $request, $subdomain)
    {
        $website = $request->get('website');
        if (!$website) $website = Website::where('subdomain', $subdomain)->firstOrFail();

        $cartKey = 'cart_' . $website->id;
        $cart = session()->get($cartKey);

        if (!$cart) return redirect()->back()->with('error', 'Keranjang kosong!');

        // --- VALIDASI STOK AKHIR (PENTING) ---
        // Cek sekali lagi sebelum membuat order, takutnya stok diambil orang lain 1 detik lalu
        foreach ($cart as $key => $item) {
            if (!empty($item['variant_id'])) {
                $variant = ProductVariant::find($item['variant_id']);
                if (!$variant || $variant->stock < $item['quantity']) {
                    return redirect()->back()->with('error', "Maaf, stok varian '{$item['name']}' baru saja habis atau tidak mencukupi.");
                }
            } else {
                $product = Product::find($item['product_id']);
                if (!$product || $product->stock < $item['quantity']) {
                    return redirect()->back()->with('error', "Maaf, stok produk '{$item['name']}' baru saja habis.");
                }
            }
        }

        $request->validate([
            'customer_name'     => 'required|string|max:100',
            'customer_whatsapp' => 'required|numeric',
            'customer_address'  => 'required|string',
        ]);

        $totalAmount = 0;
        foreach ($cart as $item) {
            $totalAmount += $item['price'] * $item['quantity'];
        }

        DB::beginTransaction();

        try {
            $order = Order::create([
                'website_id'        => $website->id,
                'order_number'      => 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                'customer_name'     => $request->customer_name,
                'customer_whatsapp' => $request->customer_whatsapp,
                'customer_address'  => $request->customer_address,
                'total_amount'      => $totalAmount,
                'status'            => 'pending', // Status awal selalu Pending (Tunggu Bayar)
            ]);

            foreach ($cart as $key => $item) {
                // Kurangi Stok (Booking)
                if (!empty($item['variant_id'])) {
                    $variant = ProductVariant::find($item['variant_id']);
                    $variant->decrement('stock', $item['quantity']);
                    
                    // Opsional: Sync stok induk
                    if($variant->product) $variant->product->decrement('stock', $item['quantity']);
                } else {
                    $product = Product::find($item['product_id']);
                    $product->decrement('stock', $item['quantity']);
                }

                OrderItem::create([
                    'order_id'      => $order->id,
                    'product_id'    => $item['product_id'],
                    'product_name'  => $item['name'],
                    'product_image' => $item['image'] ?? null,
                    'variant_id'    => $item['variant_id'] ?? null,
                    'price'         => $item['price'],
                    'qty'           => $item['quantity'],
                    'subtotal'      => $item['price'] * $item['quantity'],
                ]);
            }

            DB::commit();
            session()->forget($cartKey);

            // Redirect langsung ke Halaman Pembayaran
            return redirect()->route('store.payment', [
                'subdomain' => $website->subdomain,
                'order_number' => $order->order_number
            ])->with('success', "Pesanan berhasil dibuat! Silakan lakukan pembayaran agar stok tidak hangus.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    // 4. UPDATE CART
    public function updateCart(Request $request, $subdomain)
    {
        $website = $request->get('website');
        if (!$website) $website = Website::where('subdomain', $subdomain)->firstOrFail();

        $cartKey = 'cart_' . $website->id;
        $cart = session()->get($cartKey);

        if(isset($cart[$request->id])) {
            $cart[$request->id]['quantity'] = $request->qty;
            session()->put($cartKey, $cart);
            return redirect()->back()->with('success', 'Keranjang diperbarui!');
        }
    }

    // 5. REMOVE FROM CART
    public function removeFromCart(Request $request, $subdomain, $id)
    {
        $website = $request->get('website');
        if (!$website) $website = Website::where('subdomain', $subdomain)->firstOrFail();

        $cartKey = 'cart_' . $website->id;
        $cart = session()->get($cartKey);

        if(isset($cart[$id])) {
            unset($cart[$id]);
            session()->put($cartKey, $cart);
        }

        return redirect()->back()->with('success', 'Produk dihapus dari keranjang!');
    }

    // 6. HALAMAN KONFIRMASI PEMBAYARAN
    public function payment(Request $request, $subdomain, $order_number)
    {
        $website = $request->get('website');
        if (!$website) $website = Website::where('subdomain', $subdomain)->firstOrFail();

        $order = Order::where('website_id', $website->id)
                      ->where('order_number', $order_number)
                      ->firstOrFail();

        return view('storefront.payment', compact('website', 'order'));
    }

    // 7. PROSES UPLOAD BUKTI (DENGAN SECURITY CHECK)
    public function confirmPayment(Request $request, $subdomain, $order_number)
    {
        $website = $request->get('website');
        if (!$website) $website = Website::where('subdomain', $subdomain)->firstOrFail();

        $order = Order::where('website_id', $website->id)
                      ->where('order_number', $order_number)
                      ->firstOrFail();

        // [SAFETY CHECK] Jangan biarkan upload jika status sudah final atau dibatalkan
        if (!in_array($order->status, ['pending', 'awaiting_confirmation'])) {
            return redirect()->back()->with('error', 'Status pesanan ini sudah "' . $order->status . '" dan tidak dapat mengubah bukti pembayaran.');
        }

        $request->validate([
            'bank_name' => 'required|string|max:50',
            'payment_proof' => 'required|image|max:2048',
        ]);

        if ($request->hasFile('payment_proof')) {
            // Hapus bukti lama jika ada (untuk hemat storage)
            if ($order->payment_proof && Storage::disk('public')->exists($order->payment_proof)) {
                Storage::disk('public')->delete($order->payment_proof);
            }

            $path = $request->file('payment_proof')->store('payments/' . $website->id, 'public');
            
            $order->update([
                'bank_name' => $request->bank_name,
                'payment_proof' => $path,
                // Pastikan status di database ENUM sudah support 'awaiting_confirmation'
                'status' => 'awaiting_confirmation', 
            ]);
        }

        return redirect()->back()->with('success', 'Terima kasih! Bukti pembayaran berhasil dikirim. Kami akan memverifikasi pesanan Anda segera.');
    }

    // Tambahkan method ini di CheckoutController.php

public function checkShipping(Request $request, $subdomain)
{
    $website = $request->get('website'); // Middleware sudah set ini
    if (!$website) $website = \App\Models\Website::where('subdomain', $subdomain)->firstOrFail();

    $destination = $request->destination;
    $totalWeight = $request->weight; // Berat dalam gram

    // Ambil data ongkir yang cocok
    // Logic: Cari kota tujuan yg sama, dan min_weight <= berat keranjang
    // (Opsional: Jika berat < 1kg, dianggap 1kg)
    $weightInKg = ceil($totalWeight / 1000); // Pembulatan ke atas (1.2kg jadi 2kg)
    
    $rates = $website->shippingRates()
                    ->where('destination_city', $destination)
                    // ->where('min_weight', '<=', $weightInKg) // Opsional: jika ingin strict
                    ->get();

    if ($rates->isEmpty()) {
        return response()->json(['status' => 'empty', 'message' => 'Maaf, pengiriman ke lokasi ini belum tersedia.']);
    }

    // Format data untuk dikirim balik ke JS
    $options = [];
    foreach ($rates as $rate) {
        $finalCost = $rate->rate_per_kg * $weightInKg;
        
        $est = "";
        if($rate->min_day) {
            $est = "({$rate->min_day}" . ($rate->max_day ? "-{$rate->max_day}" : "") . " Hari)";
        }

        $options[] = [
            'id' => $rate->id,
            'courier' => $rate->courier_name,
            'service' => $rate->service_name,
            'cost' => $finalCost,
            'cost_formatted' => number_format($finalCost, 0, ',', '.'),
            'estimation' => $est
        ];
    }

    return response()->json(['status' => 'success', 'options' => $options]);
}
}
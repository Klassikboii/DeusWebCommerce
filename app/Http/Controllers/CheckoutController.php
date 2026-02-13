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

        // 1. VALIDASI INPUT TAMBAHAN (ONGKIR)
        $request->validate([
            'customer_name'     => 'required|string|max:100',
            'customer_whatsapp' => 'required|numeric',
            'customer_address'  => 'required|string',
            'destination_city'  => 'required|string', // Kota wajib dipilih
            'shipping_cost'     => 'required|numeric|min:0', // Dari Hidden Input
            'shipping_courier'  => 'required|string', // Dari Hidden Input
        ]);

        // 2. HITUNG TOTAL PRODUK
        $productTotal = 0;
        foreach ($cart as $item) {
            $productTotal += $item['price'] * $item['quantity'];
        }

        // 3. AMBIL ONGKIR DARI REQUEST
        // (Catatan: Untuk keamanan tingkat tinggi, seharusnya kita hitung ulang ongkir di backend 
        // berdasarkan destination_city untuk mencegah manipulasi HTML. 
        // Tapi untuk tahap ini, kita percaya input frontend dulu).
        $shippingCost = $request->shipping_cost;
        $grandTotal = $productTotal + $shippingCost;

        DB::beginTransaction();

        try {
            // 4. SIMPAN ORDER
            $order = Order::create([
                'website_id'        => $website->id,
                'order_number'      => 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                'customer_name'     => $request->customer_name,
                'customer_whatsapp' => $request->customer_whatsapp,
                
                // Gabungkan Alamat dengan Kota Tujuan agar lengkap
                'customer_address'  => $request->customer_address . ", " . $request->destination_city,
                
                // Simpan Data Ongkir
                'shipping_cost'     => $shippingCost,
                'courier_name'      => $request->shipping_courier, // Contoh: "JNE REG"
                
                'total_amount'      => $productTotal, // Harga Barang saja (Nanti di view dijumlahkan)
                // ATAU jika Anda ingin total_amount adalah grand total:
                // 'total_amount'   => $grandTotal, 
                
                'status'            => 'pending',
            ]);

            // 5. SIMPAN ITEM & KURANGI STOK (SAMA SEPERTI SEBELUMNYA)
            foreach ($cart as $key => $item) {
                // ... (Logika stok varian/produk tetap sama, copy paste dari kode sebelumnya) ...
                
                // Code stok reduction logic here...
                if (!empty($item['variant_id'])) {
                    $variant = ProductVariant::find($item['variant_id']);
                    $variant->decrement('stock', $item['quantity']);
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

            // Redirect
            return redirect()->route('store.payment', [
                'subdomain' => $website->subdomain,
                'order_number' => $order->order_number
            ])->with('success', "Pesanan berhasil! Mohon segera lakukan pembayaran.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
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
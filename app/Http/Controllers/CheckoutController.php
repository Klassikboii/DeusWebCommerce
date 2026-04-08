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
    public function addToCart(Request $request, $id)
    {
        $website = $request->get('website');
       

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
    public function cart(Request $request)
    {
        $website = $request->get('website');
        
        $cartKey = 'cart_' . $website->id;
        $cart = session()->get($cartKey, []);

        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        // Tarik semua data kota, sertakan nama provinsinya agar jelas
     $cities = \App\Models\City::with('province')->orderBy('name', 'asc')->get();

        return view('storefront.cart', compact('website', 'cart', 'total', 'cities'));
    }
// 3. PROCESS CHECKOUT
    public function processCheckout(Request $request)
    {
        $website = $request->get('website');
        
        $cartKey = 'cart_' . $website->id;
        $cart = session()->get($cartKey);

        if (!$cart) return redirect()->back()->with('error', 'Keranjang kosong!');

        // 1. VALIDASI INPUT FORM
        $request->validate([
            'customer_name'     => 'required|string|max:100',
            'customer_whatsapp' => 'required|numeric',
            'customer_address'  => 'required|string',
            'destination_city'  => 'required|integer', 
            'shipping_cost'     => 'required|numeric|min:0', 
            'shipping_courier'  => 'required|string', 
        ]);

        // =========================================================================
        // 🚨 SCRIPT DETEKTIF: VALIDASI HARGA & STOK TERBARU (ANTI RACE-CONDITION)
        // =========================================================================
        $isCartChanged = false;

        foreach ($cart as $key => $item) {
            $freshPrice = 0;
            $freshStock = 0;

            // Cek apakah ini Varian atau Produk Induk
            if (!empty($item['variant_id'])) {
                $variant = \App\Models\ProductVariant::find($item['variant_id']);
                if (!$variant) {
                    unset($cart[$key]); // Produk varian sudah dihapus dari database
                    $isCartChanged = true;
                    continue;
                }
                $freshPrice = $variant->price;
                $freshStock = $variant->stock;
            } else {
                $product = \App\Models\Product::find($item['product_id']);
                if (!$product) {
                    unset($cart[$key]); // Produk induk sudah dihapus dari database
                    $isCartChanged = true;
                    continue;
                }
                $freshPrice = $product->price;
                $freshStock = $product->stock;
            }

            // CEK 1: Apakah stoknya tiba-tiba kurang dari yang mau dibeli?
            if ($freshStock < $item['quantity']) {
                if ($freshStock <= 0) {
                    unset($cart[$key]); // Hapus dari keranjang jika stok habis total
                } else {
                    $cart[$key]['quantity'] = $freshStock; // Turunkan qty sesuai sisa stok asli
                }
                $isCartChanged = true;
            }

            // CEK 2: Apakah harganya tiba-tiba berubah oleh Webhook Accurate?
            if ($freshPrice != $item['price']) {
                $cart[$key]['price'] = $freshPrice; // Update ke harga terbaru
                $isCartChanged = true;
            }
        }

        // JIKA ADA YANG BERUBAH: Simpan keranjang yang sudah di-update dan batalkan checkout!
        if ($isCartChanged) {
            session()->put($cartKey, $cart);
            return redirect()->back()->with('error', 'Mohon maaf, terjadi perubahan harga atau stok dari pusat secara tiba-tiba. Kami telah memperbarui isi keranjang Anda. Silakan periksa kembali sebelum melanjutkan.');
        }
        // =========================================================================
        // =========================================================================

        // 2. TERJEMAHKAN ID KOTA MENJADI NAMA LENGKAP
        $city = \App\Models\City::with('province')->find($request->destination_city);
        $cityName = $city ? $city->type . ' ' . $city->name . ' - Prov. ' . $city->province->name : 'Kota Tidak Diketahui';

        // 3. HITUNG TOTAL PRODUK (Menggunakan harga yang sudah tervalidasi di atas)
        $productTotal = 0;
        foreach ($cart as $item) {
            $productTotal += $item['price'] * $item['quantity'];
        }

        $shippingCost = $request->shipping_cost;

        DB::beginTransaction();

        // Membelah "JNE REG" menjadi dua bagian
        $courierParts = explode(' ', $request->shipping_courier, 2);
        $courierName = $courierParts[0] ?? null; 
        $courierService = $courierParts[1] ?? null; 

        try {
           // 4. SIMPAN ORDER (DATA INDUK)
            $order = Order::create([
                'website_id'        => $website->id,
                'order_number'      => 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                'customer_name'     => $request->customer_name,
                'customer_whatsapp' => $request->customer_whatsapp,
                'customer_address'  => $request->customer_address . ", " . $cityName,
                'shipping_cost'     => $shippingCost,
                'courier_name'      => $courierName,
                'courier_service'   => $courierService,
                'total_amount'      => $productTotal, 
                'status'            => 'pending',
            ]);

            // 5. SIMPAN DETAIL ITEM & POTONG STOK
            foreach ($cart as $key => $item) {
                
                // Potong Stok
                if (!empty($item['variant_id'])) {
                    $variant = \App\Models\ProductVariant::find($item['variant_id']);
                    if($variant) {
                        $variant->decrement('stock', $item['quantity']);
                        if($variant->product) $variant->product->decrement('stock', $item['quantity']);
                    }
                } else {
                    $product = \App\Models\Product::find($item['product_id']);
                    if($product) {
                        $product->decrement('stock', $item['quantity']);
                    }
                }

                // Simpan Riwayat Item
                \App\Models\OrderItem::create([
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
            
            // Bersihkan Keranjang Setelah Berhasil Checkout
            session()->forget($cartKey);

            // Redirect ke Halaman Pembayaran
            return redirect()->route('store.payment', [
                'order_number' => $order->order_number
            ])->with('success', "Pesanan berhasil! Mohon segera lakukan pembayaran.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
    // 4. UPDATE CART
    public function updateCart(Request $request)
    {
        $website = $request->get('website');
        

        $cartKey = 'cart_' . $website->id;
        $cart = session()->get($cartKey);

        if(isset($cart[$request->id])) {
            $cart[$request->id]['quantity'] = $request->qty;
            session()->put($cartKey, $cart);
            return redirect()->back()->with('success', 'Keranjang diperbarui!');
        }
    }

    // 5. REMOVE FROM CART
    public function removeFromCart(Request $request, $id)
    {
        $website = $request->get('website');
        

        $cartKey = 'cart_' . $website->id;
        $cart = session()->get($cartKey);

        if(isset($cart[$id])) {
            unset($cart[$id]);
            session()->put($cartKey, $cart);
        }

        return redirect()->back()->with('success', 'Produk dihapus dari keranjang!');
    }

    // 6. HALAMAN KONFIRMASI PEMBAYARAN (DENGAN MIDTRANS)
    public function payment(Request $request, $order_number)
    {
        $website = $request->get('website');
        

        $order = Order::where('website_id', $website->id)
                      ->where('order_number', $order_number)
                      ->firstOrFail();

        $snapToken = null;

        // 🚨 SIHIR MIDTRANS (DESENTRALISASI) 🚨
        // Kita cek apakah pemilik toko ini sudah memasukkan Server Key Midtrans-nya
        if (!empty($website->midtrans_server_key)) {
            
            // Konfigurasi Midtrans secara dinamis menggunakan kunci milik toko ini
            \Midtrans\Config::$serverKey = $website->midtrans_server_key;
            \Midtrans\Config::$isProduction = $website->midtrans_is_production ? true : false;
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;

            // Jika pesanan ini belum punya token, kita mintakan ke Midtrans
            if (empty($order->snap_token)) {
                
                // Siapkan data tagihan pembeli
                $params = [
                    'transaction_details' => [
                        'order_id' => $order->order_number, // Harus sama dengan nomor order di database
                        'gross_amount' => (int) ($order->total_amount + $order->shipping_cost),
                    ],
                    'customer_details' => [
                        'first_name' => $order->customer_name,
                        'phone' => $order->customer_whatsapp,
                        // Email dikosongkan jika Anda tidak meminta email saat checkout
                    ],
                    // 🚨 TAMBAHKAN BLOK CALLBACKS INI:
                        'callbacks' => [
                            'finish' => url()->current(), // Otomatis kembali ke halaman ini
                            'error' => url()->current(),
                            'close' => url()->current()
                        ]
                ];

                try {
                    // Minta Token Snap dari Midtrans
                    $snapToken = \Midtrans\Snap::getSnapToken($params);
                    
                    // Simpan token ke database agar tidak double-generate jika pembeli merefresh halaman
                    $order->update(['snap_token' => $snapToken]); 
                    
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Gagal Generate Midtrans Snap: ' . $e->getMessage());
                }
            } else {
                // Jika sudah ada token (pembeli cuma refresh halaman), pakai token yang lama
                $snapToken = $order->snap_token;
            }
        }
        
        // Kirim $snapToken ke halaman view
        return view('storefront.payment', compact('website', 'order', 'snapToken'));
    }

    // 7. PROSES UPLOAD BUKTI (DENGAN SECURITY CHECK)
    public function confirmPayment(Request $request, $order_number)
    {
        $website = $request->get('website');
       

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

public function checkShipping(Request $request, )
    {
        $request->validate([
            'destination' => 'required|integer', 
            'weight' => 'required|numeric|min:1',
        ]);

        // TANGKAP DATA WEBSITE BERDASARKAN SUBDOMAIN
        $website = $request->get('website');
        

        $apiKey = env('RAJAONGKIR_API_KEY');
        
        // 1. Ambil data markup
        $markup = \App\Models\ShippingMarkup::where('website_id', $website->id)
                    ->where('city_id', $request->destination)
                    ->first();

        // 🚨 2. AMBIL PENGATURAN KURIR KLIEN
        // Jika klien belum mengatur (null), kita pakai default 3 kurir
        $activeCouriersArray = $website->active_couriers ?? ['jne', 'sicepat', 'jnt'];
        
        // Komerce API meminta format string dipisah titik dua (misal: "jne:sicepat")
        $courierString = implode(':', $activeCouriersArray);

        // Jika string kosong (Klien mematikan semua kurir)
        if (empty($courierString)) {
            return response()->json(['status' => 'error', 'message' => 'Toko ini belum mengaktifkan layanan kurir apapun.']);
        }

        $originCityId = $website->city_id ?? 152;
        $destinationCityId = $request->destination;

        // 3. Tembak API Komerce V2 dengan Kurir Dinamis
        $response = \Illuminate\Support\Facades\Http::asForm()
            ->withHeaders(['key' => $apiKey])
            ->timeout(30) // Anti timeout
            ->retry(3, 1000)
            ->post('https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost', [
                'origin'      => (string) $originCityId,
                'destination' => (string) $destinationCityId,
                'weight'      => (int) $request->weight,
                'courier'     => $courierString // 🚨 KURIR SEKARANG DINAMIS!
            ]);

        if (!$response->successful()) {
            return response()->json(['status' => 'error', 'message' => 'API Error: ' . $response->body()]);
        }

        $apiData = $response->json()['data'] ?? [];
        $options = [];

        // 4. Mapping Data dan Perbaiki Estimasi (ETD)
        foreach ($apiData as $service) {
            $courierCode = strtoupper($service['code'] ?? 'UNKNOWN'); 
            $originalCost = $service['cost'] ?? 0;
            $serviceName = $service['service'] ?? 'REG';
            
            // Gembok pengaman
            if ($originalCost <= 0) continue;

            // 🚨 LOGIKA PERBAIKAN ESTIMASI (ETD)
            $estimationRaw = trim($service['etd'] ?? '');
            $etdDisplay = '';

            if ($estimationRaw === '' || $estimationRaw === '0') {
                $etdDisplay = 'Estimasi menyesuaikan layanan';
            } elseif (stripos($estimationRaw, 'hari') !== false || stripos($estimationRaw, 'jam') !== false) {
                // Jika dari API sudah ada tulisan Hari/Jam, langsung pakai
                $etdDisplay = 'Estimasi: ' . $estimationRaw;
            } else {
                // Jika dari API cuma angka "1-2" atau "3", tambahkan kata "Hari"
                $etdDisplay = 'Estimasi: ' . $estimationRaw . ' Hari';
            }

            // Suntikkan Markup
            $finalCost = $originalCost;
            if ($markup) {
                if ($markup->markup_type == 'nominal') {
                    $finalCost += $markup->markup_value; 
                } elseif ($markup->markup_type == 'percent') {
                    $finalCost += ($originalCost * ($markup->markup_value / 100)); 
                }
            }
            
            $options[] = [
                'id' => $courierCode . '_' . str_replace(' ', '', $serviceName), 
                'courier' => $courierCode,
                'service' => $serviceName,
                'cost' => $finalCost, 
                'cost_formatted' => number_format($finalCost, 0, ',', '.'),
                
                // 🚨 MASUKKAN ESTIMASI YANG SUDAH RAPI
                'estimation' => $etdDisplay
            ];
        }

        if (empty($options)) {
            return response()->json(['status' => 'error', 'message' => 'Tidak ada layanan kurir yang tersedia untuk rute ini.']);
        }

        return response()->json(['status' => 'success', 'options' => $options]);
    }
}
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
                    unset($cart[$key]); 
                    $isCartChanged = true;
                    continue;
                }
                $freshPrice = $variant->price;
                $freshStock = $variant->stock;
            } else {
                $product = \App\Models\Product::find($item['product_id']);
                if (!$product) {
                    unset($cart[$key]); 
                    $isCartChanged = true;
                    continue;
                }
                $freshPrice = $product->price;
                $freshStock = $product->stock;
            }

            // CEK 1: Apakah stoknya tiba-tiba kurang dari yang mau dibeli?
            if ($freshStock < $item['quantity']) {
                if ($freshStock <= 0) {
                    unset($cart[$key]); 
                } else {
                    $cart[$key]['quantity'] = $freshStock; 
                }
                $isCartChanged = true;
            }

            // CEK 2: Apakah harganya tiba-tiba berubah?
            if ($freshPrice != $item['price']) {
                $cart[$key]['price'] = $freshPrice; 
                $isCartChanged = true;
            }
        }

        if ($isCartChanged) {
            session()->put($cartKey, $cart);
            return redirect()->back()->with('error', 'Mohon maaf, terjadi perubahan harga atau stok dari pusat secara tiba-tiba. Kami telah memperbarui isi keranjang Anda. Silakan periksa kembali sebelum melanjutkan.');
        }

        // 2. TERJEMAHKAN ID KOTA MENJADI NAMA LENGKAP
        $city = \App\Models\City::with('province')->find($request->destination_city);
        $cityName = $city ? $city->type . ' ' . $city->name . ' - Prov. ' . $city->province->name : 'Kota Tidak Diketahui';

        // 3. HITUNG TOTAL PRODUK
        $productTotal = 0;
        foreach ($cart as $item) {
            $productTotal += $item['price'] * $item['quantity'];
        }

        $shippingCost = $request->shipping_cost;

        // =========================================================================
        // 🚨 4. BACA & APLIKASIKAN VOUCHER DARI SESSION SEBELUM DISIMPAN
        // =========================================================================
        $appliedVoucher = session()->get("applied_voucher_{$website->id}");
        $voucherId = null;
        $discountAmount = 0;

        if ($appliedVoucher) {
            $dbVoucher = \App\Models\Voucher::find($appliedVoucher['id']);
            
            // 🚨 RE-VALIDASI KETAT (PINTU BELAKANG DIKUNCI)
            $customerId = auth('customer')->id();
            $isVoucherValid = true;
            $voucherErrorMessage = '';

            // Cek Eksistensi & Expired
            if (!$dbVoucher || !$dbVoucher->isValid()) {
                $isVoucherValid = false;
                $voucherErrorMessage = 'Voucher sudah tidak valid atau kedaluwarsa.';
            } 
            // Cek Minimal Belanja
            elseif ($productTotal < $dbVoucher->min_purchase) {
                $isVoucherValid = false;
                $voucherErrorMessage = 'Total belanja tidak memenuhi syarat minimal voucher.';
            } 
            // Cek RFM Privilege (Jika Ada)
            elseif ($dbVoucher->target_rfm_segment) {
                if (!$customerId) {
                    $isVoucherValid = false;
                    $voucherErrorMessage = 'Anda harus login untuk menggunakan voucher eksklusif ini.';
                } else {
                    $rfm = \App\Models\CustomerRfm::where('website_id', $website->id)
                        ->where('customer_whatsapp', auth('customer')->user()->whatsapp)
                        ->first();
                    if (!$rfm || $rfm->segment !== $dbVoucher->target_rfm_segment) {
                        $isVoucherValid = false;
                        $voucherErrorMessage = 'Voucher eksklusif tidak berlaku untuk akun Anda.';
                    }
                }
            }
            
            // 🚨 CEK PENGGUNAAN BERULANG (PROPOSAL 1)
            if ($isVoucherValid && $customerId) {
                $alreadyUsed = \App\Models\Order::where('customer_id', $customerId)
                                    ->where('voucher_id', $dbVoucher->id) 
                                    ->whereNotIn('status', ['canceled', 'failed']) 
                                    ->exists();
                if ($alreadyUsed) {
                    $isVoucherValid = false;
                    $voucherErrorMessage = 'Anda sudah pernah menggunakan voucher ini pada pesanan sebelumnya.';
                }
            }

            // JIKA SEMUA VALIDASI LOLOS, HITUNG DISKON
            if ($isVoucherValid) {
                $voucherId = $dbVoucher->id;
                
                if ($dbVoucher->discount_type === 'nominal') {
                    $discountAmount = $dbVoucher->discount_value;
                } else {
                    $discountAmount = $productTotal * ($dbVoucher->discount_value / 100);
                    if ($dbVoucher->max_discount_amount && $discountAmount > $dbVoucher->max_discount_amount) {
                        $discountAmount = $dbVoucher->max_discount_amount;
                    }
                }
                
                // Cegah diskon melebihi harga produk
                if ($discountAmount > $productTotal) {
                    $discountAmount = $productTotal;
                }
            } else {
                // 🚨 JIKA KETAHUAN CURANG, HAPUS DARI SESSION & BATALKAN CHECKOUT!
                session()->forget("applied_voucher_{$website->id}");
                return redirect()->back()->with('error', 'Gagal memproses pesanan: ' . $voucherErrorMessage);
            }
        }

        // KURANGI TOTAL DENGAN DISKON
        $finalTotal = $productTotal - $discountAmount;

        DB::beginTransaction();

        $courierParts = explode(' ', $request->shipping_courier, 2);
        $courierName = $courierParts[0] ?? null; 
        $courierService = $courierParts[1] ?? null; 

        try {
           // 5. SIMPAN ORDER (DATA INDUK)
            $orderData = [
                'website_id'        => $website->id,
                'order_number'      => 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                'customer_name'     => $request->customer_name,
                'customer_whatsapp' => $request->customer_whatsapp,
                'customer_address'  => $request->customer_address . ", " . $cityName,
                'shipping_cost'     => $shippingCost,
                'courier_name'      => $courierName,
                'courier_service'   => $courierService,
                'total_amount'      => $finalTotal + $shippingCost, // 🚨 SUDAH DIKURANGI DISKON
                'voucher_id'        => $voucherId, // 🚨 SIMPAN ID VOUCHER
                'discount_amount'   => $discountAmount, // 🚨 SIMPAN TOTAL DISKON
                'status'            => 'pending',
            ];

            if (auth('customer')->check()) {
                $orderData['customer_id'] = auth('customer')->id();
            }

            $order = Order::create($orderData);

            // 6. SIMPAN DETAIL ITEM & POTONG STOK
            foreach ($cart as $key => $item) {
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

            // 🚨 7. UPDATE KUOTA VOUCHER
            if ($voucherId) {
                \App\Models\Voucher::where('id', $voucherId)->increment('used_count');
            }

            DB::commit();
            
            // Bersihkan Keranjang & Session Voucher Setelah Berhasil Checkout
            session()->forget($cartKey);
            session()->forget("applied_voucher_{$website->id}"); // 🚨 BERSIHKAN SESSION VOUCHER

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

   // 6. HALAMAN KONFIRMASI PEMBAYARAN
    // 6. HALAMAN KONFIRMASI PEMBAYARAN
    public function payment(Request $request, $order_number)
    {
        $website = $request->get('website');
        
        $order = \App\Models\Order::where('website_id', $website->id)
                      ->where('order_number', $order_number)
                      ->firstOrFail();

        $snapToken = $order->snap_token;
        $paymentUrl = $order->payment_url; // 🚨 BACA URL PIVOT DARI DATABASE

        // SULAP TERJADI DI SINI: Jika pesanan belum punya token/url, minta ke Gateway!
        if (empty($snapToken) && empty($paymentUrl)) {
            $paymentGateway = \App\Services\Payment\PaymentFactory::make($website);
            $paymentData = $paymentGateway->createTransaction($order);

            // Cek apakah balasan dari Service memiliki status 'success'
            if (isset($paymentData['status']) && $paymentData['status'] === 'success') {
                $snapToken = $paymentData['token'];
                $paymentUrl = $paymentData['redirect_url']; // 🚨 TANGKAP URL PIVOT
                
                // Simpan token dan URL ke database agar tidak double-generate
                $order->update([
                    'snap_token' => $snapToken,
                    'payment_url' => $paymentUrl // 🚨 SIMPAN KE DATABASE
                ]);
            } else {
                \Illuminate\Support\Facades\Log::error('PIVOT REJECTED:', $paymentData);
                // Beri pesan error ke pembeli jika Gateway Pivot sedang gangguan
                \Illuminate\Support\Facades\Session::flash('error', 'Sistem pembayaran sedang sibuk atau kunci API toko salah. Silakan hubungi admin toko.');
            }
        }
        
        // 🚨 LEMPAR paymentUrl KE VIEW BLADE
        return view('storefront.payment', compact('website', 'order', 'snapToken', 'paymentUrl'));
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
   public function applyVoucher(\Illuminate\Http\Request $request)
    {
       try {
            // =========================================================
            // 1. DETEKSI WEBSITE (TENANT) DARI URL BROWSER SECARA PAKSA
            // =========================================================
            $host = $request->getHost(); // Contoh: 'elecjoss.deusserver.test' atau 'domain.com'
            $subdomain = explode('.', $host)[0]; // Mengambil kata pertama: 'elecjoss'

            // Cari website berdasarkan subdomain ATAU custom domain
            $website = $request->attributes->get('tenant_website') 
                       ?? \App\Models\Website::where('subdomain', $subdomain)->first()
                       ?? \App\Models\Website::where('custom_domain', $host)->first();

            if (!$website) {
                throw new \Exception("Website Tenant tidak ditemukan untuk host: " . $host);
            }

            $code = strtoupper($request->voucher_code);
            
            // ... (lanjutkan ke // 2. Ambil Total Keranjang) ...
            
            // 2. Ambil Total Keranjang (Sesuaikan key session-nya jika perlu)
            $cart = session()->get("cart_{$website->id}", []);
            $cartTotal = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);

            if ($cartTotal <= 0) {
                return response()->json(['success' => false, 'message' => 'Keranjang Anda masih kosong.']);
            }

            // 3. Cari Voucher
            $voucher = \App\Models\Voucher::where('website_id', $website->id)
                ->where('code', $code)
                ->first();

            if (!$voucher) {
                return response()->json(['success' => false, 'message' => 'Kode voucher tidak ditemukan.']);
            }

            // 4. Validasi Dasar (Aktif, Kuota, Expired)
            if (!$voucher->isValid()) {
                return response()->json(['success' => false, 'message' => 'Voucher tidak aktif, kuota habis, atau sudah kedaluwarsa.']);
            }

            // 5. Validasi Minimal Belanja
            if ($cartTotal < $voucher->min_purchase) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Minimal belanja untuk voucher ini adalah Rp ' . number_format($voucher->min_purchase, 0, ',', '.')
                ]);
            }

            // =========================================================
            // 6. VALIDASI PRIVILEGE (DATA SCIENCE RFM)
            // =========================================================
            if ($voucher->target_rfm_segment) {
                // Cek apakah pembeli sudah login
                $customer = \Illuminate\Support\Facades\Auth::guard('customer')->user();
                
                if (!$customer) {
                    return response()->json(['success' => false, 'message' => 'Anda harus login untuk menggunakan voucher eksklusif ini.']);
                }

                // Cek apakah Model CustomerRfm (atau CustomerRFM) benar-benar ada
                if (!class_exists(\App\Models\CustomerRfm::class)) {
                    throw new \Exception('Model CustomerRfm tidak ditemukan. Apakah namanya CustomerRFM?');
                }

                // Ambil data RFM pelanggan ini berdasarkan nomor WhatsApp-nya
                $rfm = \App\Models\CustomerRfm::where('website_id', $website->id)
                    ->where('customer_whatsapp', $customer->whatsapp)
                    ->first();

                // Tolak jika dia tidak punya data RFM atau segmennya tidak cocok
                if (!$rfm || $rfm->segment !== $voucher->target_rfm_segment) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'Maaf, voucher ini eksklusif hanya untuk pelanggan dengan status: ' . $voucher->target_rfm_segment
                    ]);
                }
            }
           // ====================================================================
            // 🚨 PROPOSAL 1: CEK APAKAH CUSTOMER SUDAH PERNAH PAKAI VOUCHER INI
            // ====================================================================
            
            $customerId = \Illuminate\Support\Facades\Auth::guard('customer')->id(); 

            // 1. Paksa Login (Voucher yang dibatasi penggunanya WAJIB mewajibkan user login)
            if (!$customerId) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Anda harus login terlebih dahulu untuk menggunakan kode voucher ini.'
                ]);
            }

            // 2. Cek Riwayat Pesanan
            $alreadyUsed = \App\Models\Order::where('customer_id', $customerId)
                                ->where('voucher_id', $voucher->id) 
                                ->whereNotIn('status', ['canceled', 'failed']) 
                                ->exists();

            if ($alreadyUsed) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Maaf, Anda sudah pernah menggunakan kode voucher ini pada pesanan sebelumnya.'
                ]);
            }
            // ====================================================================

            // 7. Hitung Diskon
            $discountAmount = 0;
            if ($voucher->discount_type === 'nominal') {
                $discountAmount = $voucher->discount_value;
            } else {
                $discountAmount = $cartTotal * ($voucher->discount_value / 100);
                if ($voucher->max_discount_amount && $discountAmount > $voucher->max_discount_amount) {
                    $discountAmount = $voucher->max_discount_amount;
                }
            }

            // Cegah diskon melebihi total belanja
            if ($discountAmount > $cartTotal) {
                $discountAmount = $cartTotal;
            }

            // 8. Simpan ke Session
            session()->put("applied_voucher_{$website->id}", [
                'id' => $voucher->id,
                'code' => $voucher->code,
                'discount_amount' => $discountAmount
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Voucher berhasil digunakan!',
                'discount_amount' => $discountAmount,
                'discount_formatted' => '-Rp ' . number_format($discountAmount, 0, ',', '.'),
                'final_total' => $cartTotal - $discountAmount,
                'final_total_formatted' => 'Rp ' . number_format($cartTotal - $discountAmount, 0, ',', '.')
            ]);

        } catch (\Exception $e) {
            // 🚨 TANGKAP ERROR 500 DAN TAMPILKAN KE LAYAR
            return response()->json([
                'success' => false,
                'message' => 'Error PHP: ' . $e->getMessage() . ' (Baris ' . $e->getLine() . ')'
            ]);
        }
    }
}
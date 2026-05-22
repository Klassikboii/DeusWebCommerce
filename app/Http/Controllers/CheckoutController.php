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
use Log;
use Session;

class CheckoutController extends Controller
{
    // =========================================================================
    // 🚨 FUNGSI HELPER: PENGHITUNG HARGA GROSIR DINAMIS 🚨
    // =========================================================================
    private function getApplicablePrice($productId, $variantId, $quantity)
    {
        $product = Product::find($productId);
        if (!$product) return 0;

        $basePrice = $product->price;
        if ($variantId) {
            $variant = ProductVariant::find($variantId);
            if ($variant) $basePrice = $variant->price;
        }

        // Cari harga grosir yang batas min_qty-nya terpenuhi (diurutkan dari yang paling besar)
        $wholesaleQuery = \App\Models\WholesalePrice::where('product_id', $productId)
            ->where('min_qty', '<=', $quantity)
            ->orderBy('min_qty', 'desc');

        if ($variantId) {
            // Jika bervarian: Cek grosir yang ID-nya spesifik, ATAU yang ID-nya null (Global)
            $wholesale = $wholesaleQuery->where(function($q) use ($variantId) {
                $q->where('product_variant_id', $variantId)
                  ->orWhereNull('product_variant_id');
            })->first();
        } else {
            // Jika single product: Cari yang ID-nya null
            $wholesale = $wholesaleQuery->whereNull('product_variant_id')->first();
        }

        // Kembalikan harga grosir jika ada, jika tidak, kembalikan harga normal (base price)
        return $wholesale ? $wholesale->price : $basePrice;
    }
    // =========================================================================

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

        // 🚨 HITUNG TOTAL QTY & CEK HARGA GROSIR 🚨
        $newQty = isset($cart[$cartItemId]) ? $cart[$cartItemId]['quantity'] + $request->quantity : $request->quantity;
        $dynamicPrice = $this->getApplicablePrice($product->id, $variant ? $variant->id : null, $newQty);

        if (isset($cart[$cartItemId])) {
            $cart[$cartItemId]['quantity'] = $newQty;
            $cart[$cartItemId]['price'] = $dynamicPrice; // Terapkan harga grosir
        } else {
            $cart[$cartItemId] = [
                "product_id" => $product->id,
                "variant_id" => $variant ? $variant->id : null,
                "name"       => $product->name . ($variant ? ' (' . $variant->name . ')' : ''),
                "quantity"   => $newQty,
                "price"      => $dynamicPrice, // Terapkan harga grosir
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

        // 1. Hitung Subtotal Produk Murni
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        // 2. Ambil Diskon Bundling (Jika Ada)
        $bundleDiscount = session()->get('bundle_discount_' . $website->id, null);
        $bundleDiscountAmount = $bundleDiscount ? $bundleDiscount['amount'] : 0;

        // 3. Ambil Diskon Voucher (Jika Ada)
        $appliedVoucher = session()->get("applied_voucher_{$website->id}", null);
        $voucherDiscountAmount = $appliedVoucher ? $appliedVoucher['discount_amount'] : 0;

        // 4. Hitung Grand Total (Subtotal - Semua Diskon)
        $grandTotal = $subtotal - $bundleDiscountAmount - $voucherDiscountAmount;
        if($grandTotal < 0) $grandTotal = 0;

        // Data Kota dan Voucher
        $cities = \App\Models\City::with('province')->orderBy('name', 'asc')->get();
        $now = now();
        $availableVouchers = \App\Models\Voucher::where('website_id', $website->id)
            ->where('is_active', true)
            ->where(function($q) use ($now) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
            })
            ->where(function($q) use ($now) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', $now);
            })
            ->get();
            
        // Kirim variabel yang konsisten ke View
        return view('storefront.cart', compact(
            'website', 'cart', 'subtotal', 'cities', 'availableVouchers', 
            'bundleDiscountAmount', 'voucherDiscountAmount', 'grandTotal'
        ));
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
        // 🚨 SCRIPT DETEKTIF: VALIDASI HARGA GROSIR & STOK TERBARU (ANTI RACE-CONDITION)
        // =========================================================================
        $isCartChanged = false;

        foreach ($cart as $key => $item) {
            $freshStock = 0;

            if (!empty($item['variant_id'])) {
                $variant = \App\Models\ProductVariant::find($item['variant_id']);
                if (!$variant) {
                    unset($cart[$key]); 
                    $isCartChanged = true;
                    continue;
                }
                $freshStock = $variant->stock;
            } else {
                $product = \App\Models\Product::find($item['product_id']);
                if (!$product) {
                    unset($cart[$key]); 
                    $isCartChanged = true;
                    continue;
                }
                $freshStock = $product->stock;
            }

            // CEK 1: Apakah stoknya tiba-tiba kurang?
            if ($freshStock < $item['quantity']) {
                if ($freshStock <= 0) {
                    unset($cart[$key]); 
                } else {
                    $cart[$key]['quantity'] = $freshStock; 
                }
                $isCartChanged = true;
            }

            // 🚨 CEK 2: KALKULASI ULANG HARGA GROSIR TERBARU 🚨
            // Memastikan admin tidak tiba-tiba mengganti aturan grosir saat user sedang checkout
            $expectedPrice = $this->getApplicablePrice($item['product_id'], $item['variant_id'] ?? null, $item['quantity']);

            if ($expectedPrice != $item['price']) {
                $cart[$key]['price'] = $expectedPrice; 
                $isCartChanged = true;
            }
        }

        if ($isCartChanged) {
            session()->put($cartKey, $cart);
            return redirect()->back()->with('error', 'Mohon maaf, terjadi perubahan harga grosir atau stok produk dari pusat. Keranjang Anda telah diperbarui secara otomatis. Silakan periksa kembali.');
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
            
            $customerId = auth('customer')->id();
            $isVoucherValid = true;
            $voucherErrorMessage = '';

            if (!$dbVoucher || !$dbVoucher->isValid()) {
                $isVoucherValid = false;
                $voucherErrorMessage = 'Voucher sudah tidak valid atau kedaluwarsa.';
            } 
            elseif ($productTotal < $dbVoucher->min_purchase) {
                $isVoucherValid = false;
                $voucherErrorMessage = 'Total belanja tidak memenuhi syarat minimal voucher.';
            } 
            elseif ($dbVoucher->target_rfm_segment) {
                if (!$customerId) {
                    $isVoucherValid = false;
                    $voucherErrorMessage = 'Anda harus login untuk menggunakan voucher eksklusif ini.';
                } else {
                $rfm = \App\Models\CustomerRfm::where('website_id', $website->id)
                    ->where('customer_id', $customerId) 
                    ->first();
                    if (!$rfm || $rfm->segment !== $dbVoucher->target_rfm_segment) {
                        $isVoucherValid = false;
                        $voucherErrorMessage = 'Voucher eksklusif tidak berlaku untuk akun Anda.';
                    }
                }
            }
            
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
                
                if ($discountAmount > $productTotal) {
                    $discountAmount = $productTotal;
                }
            } else {
                session()->forget("applied_voucher_{$website->id}");
                return redirect()->back()->with('error', 'Gagal memproses pesanan: ' . $voucherErrorMessage);
            }
        }
        $voucherAmount = $discountAmount; 

        // 5. AMBIL DISKON BUNDLING
        $bundleDiscount = session()->get('bundle_discount_' . $website->id);
        $bundleAmount = $bundleDiscount ? $bundleDiscount['amount'] : 0;

        $totalDiscount = $voucherAmount + $bundleAmount;
        $finalTotal = $productTotal - $totalDiscount;
        if ($finalTotal < 0) $finalTotal = 0;

        DB::beginTransaction();

        $courierParts = explode(' ', $request->shipping_courier, 2);
        $courierName = $courierParts[0] ?? null; 
        $courierService = $courierParts[1] ?? null; 

        try {
            $orderData = [
                'website_id'        => $website->id,
                'order_number'      => 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                'customer_name'     => $request->customer_name,
                'customer_whatsapp' => $request->customer_whatsapp,
                'customer_address'  => $request->customer_address . ", " . $cityName,
                'shipping_cost'     => $shippingCost,
                'courier_name'      => $courierName,
                'courier_service'   => $courierService,
                'total_amount'      => $finalTotal + $shippingCost, 
                'voucher_id'        => $voucherId, 
                'discount_amount'   => $totalDiscount, 
                'voucher_discount'  => $voucherAmount, 
                'bundle_discount'   => $bundleAmount,
                'status'            => 'pending',
                'admin_note'        => $bundleDiscount ? '🔥 Diskon Bundling AI: ' . $bundleDiscount['name'] : null,
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
                    'price'         => $item['price'], // Harga grosir tercatat dengan benar
                    'qty'           => $item['quantity'],
                    'subtotal'      => $item['price'] * $item['quantity'],
                ]);
            }

            if ($voucherId) {
                \App\Models\Voucher::where('id', $voucherId)->increment('used_count');
            }

            DB::commit();
            
            // Bersihkan Keranjang
            session()->forget($cartKey);
            session()->forget("applied_voucher_{$website->id}");
            session()->forget("bundle_discount_{$website->id}");

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
            $item = $cart[$request->id];
            $newQty = $request->qty;
            
            // 🚨 CEK HARGA GROSIR SAAT QTY DIUBAH 🚨
            $dynamicPrice = $this->getApplicablePrice($item['product_id'], $item['variant_id'] ?? null, $newQty);

            $cart[$request->id]['quantity'] = $newQty;
            $cart[$request->id]['price'] = $dynamicPrice; // Terapkan harga grosir di cart
            session()->put($cartKey, $cart);

            $bundleDiscount = session()->get('bundle_discount_' . $website->id);
            if ($bundleDiscount && in_array($request->id, $bundleDiscount['bundle_keys'] ?? [])) {
                session()->forget('bundle_discount_' . $website->id);
                return redirect()->back()->with('warning', 'Keranjang diperbarui! Promo Paket Bundling dibatalkan karena Anda mengubah jumlah barang paketan.');
            }
            
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

            $bundleDiscount = session()->get('bundle_discount_' . $website->id);
            if ($bundleDiscount && in_array($id, $bundleDiscount['bundle_keys'] ?? [])) {
                session()->forget('bundle_discount_' . $website->id);
                return redirect()->back()->with('warning', 'Produk dihapus! Promo Paket Bundling dibatalkan karena paket utama sudah tidak utuh.');
            }
        }

        return redirect()->back()->with('success', 'Produk dihapus dari keranjang!');
    }

    // 6. HALAMAN KONFIRMASI PEMBAYARAN
   public function payment(Request $request, $order_number)
    {
        $website = $request->get('website');
        
        $order = \App\Models\Order::where('website_id', $website->id)
                      ->where('order_number', $order_number)
                      ->firstOrFail();

        $kybDetail = $website->user->kybDetail ?? null;
        $isPivotActive = ($kybDetail && $kybDetail->status === 'approved' && !empty($kybDetail->merchant_id));

        $snapToken = $order->snap_token;
        $paymentUrl = $order->payment_url; 

        if (empty($snapToken) && empty($paymentUrl) && $isPivotActive) {
            $paymentGateway = \App\Services\Payment\PaymentFactory::make($website);
            $paymentData = $paymentGateway->createTransaction($order);

            if (isset($paymentData['status']) && $paymentData['status'] === 'success') {
                $snapToken = $paymentData['token'];
                $paymentUrl = $paymentData['redirect_url']; 
                
                $order->update([
                    'snap_token' => $snapToken,
                    'payment_url' => $paymentUrl 
                ]);
            } else {
                \Illuminate\Support\Facades\Log::error('PIVOT REJECTED:', $paymentData);
                $isPivotActive = false; 
                \Illuminate\Support\Facades\Session::flash('error', 'Koneksi ke sistem pembayaran otomatis gagal. Dialihkan ke pembayaran manual.');
            }
        }
        
        return view('storefront.payment', compact('website', 'order', 'snapToken', 'paymentUrl', 'isPivotActive'));
    }

    // 7. PROSES UPLOAD BUKTI (DENGAN SECURITY CHECK)
    public function confirmPayment(Request $request, $order_number)
    {
        $website = $request->get('website');
        $order = Order::where('website_id', $website->id)
                      ->where('order_number', $order_number)
                      ->firstOrFail();

        if (!in_array($order->status, ['pending', 'awaiting_confirmation'])) {
            return redirect()->back()->with('error', 'Status pesanan ini sudah "' . $order->status . '" dan tidak dapat mengubah bukti pembayaran.');
        }

        $request->validate([
            'bank_name' => 'required|string|max:50',
            'payment_proof' => 'required|image|max:2048',
        ]);

        if ($request->hasFile('payment_proof')) {
            if ($order->payment_proof && Storage::disk('public')->exists($order->payment_proof)) {
                Storage::disk('public')->delete($order->payment_proof);
            }

            $path = $request->file('payment_proof')->store('payments/' . $website->id, 'public');
            
            $order->update([
                'bank_name' => $request->bank_name,
                'payment_proof' => $path,
                'status' => 'awaiting_confirmation', 
            ]);
        }

        return redirect()->back()->with('success', 'Terima kasih! Bukti pembayaran berhasil dikirim. Kami akan memverifikasi pesanan Anda segera.');
    }

    public function checkShipping(Request $request)
    {
        $request->validate([
            'destination' => 'required|integer', 
            'weight' => 'required|numeric|min:1',
        ]);

        $website = $request->get('website');
        $apiKey = env('RAJAONGKIR_API_KEY');
        $options = []; 

        $destinationCity = \App\Models\City::find($request->destination);
        if (!$destinationCity) {
            return response()->json(['status' => 'error', 'message' => 'Kota tujuan tidak valid.']);
        }
        
        $destinationCityId = $destinationCity->id; 

        $activeCouriersArray = $website->active_couriers ?? [];
        $courierString = implode(':', $activeCouriersArray);

        if (!empty($courierString)) {
            $originCityId = $website->city_id ?? 152;
            $markup = \App\Models\ShippingMarkup::where('website_id', $website->id)
                        ->where('city_id', $request->destination)
                        ->first();

            $response = \Illuminate\Support\Facades\Http::asForm()
                ->withHeaders(['key' => $apiKey])
                ->timeout(30)
                ->retry(3, 1000)
                ->post('https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost', [
                    'origin'      => (string) $originCityId,
                    'destination' => (string) $destinationCityId,
                    'weight'      => (int) $request->weight,
                    'courier'     => $courierString
                ]);

            if ($response->successful()) {
                $apiData = $response->json()['data'] ?? [];
                
                foreach ($apiData as $service) {
                    $courierCode = strtoupper($service['code'] ?? 'UNKNOWN'); 
                    $originalCost = $service['cost'] ?? 0;
                    $serviceName = $service['service'] ?? 'REG';
                    
                    if ($originalCost <= 0) continue;

                    $estimationRaw = trim($service['etd'] ?? '');
                    $etdDisplay = '';
                    if ($estimationRaw === '' || $estimationRaw === '0') {
                        $etdDisplay = 'Estimasi menyesuaikan layanan';
                    } elseif (stripos($estimationRaw, 'hari') !== false || stripos($estimationRaw, 'jam') !== false) {
                        $etdDisplay = 'Estimasi: ' . $estimationRaw;
                    } else {
                        $etdDisplay = 'Estimasi: ' . $estimationRaw . ' Hari';
                    }

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
                        'estimation' => $etdDisplay
                    ];
                }
            }
        }

        $cleanCityName = str_replace(['Kabupaten', 'Kota', 'Kab.'], '', $destinationCity->name);
        $cleanCityName = trim($cleanCityName);

        $manualRates = \App\Models\ShippingRate::where('website_id', $website->id)
            ->where(function($query) use ($cleanCityName) {
                $query->where('destination_city', 'LIKE', '%' . $cleanCityName . '%');
            })
            ->get();

        if ($manualRates->count() > 0) {
            $weightInKg = ceil($request->weight / 1000);
            if ($weightInKg < 1) $weightInKg = 1;

            foreach ($manualRates as $rate) {
                $manualCost = $rate->rate_per_kg * $weightInKg;

                $etdDisplay = 'Estimasi: ';
                if ($rate->min_day) {
                    $etdDisplay .= $rate->min_day . ($rate->max_day ? ' - ' . $rate->max_day : '') . ' Hari';
                } else {
                    $etdDisplay .= 'Menyesuaikan';
                }

                $options[] = [
                    'id' => 'manual_' . $rate->id, 
                    'courier' => strtoupper($rate->courier_name),
                    'service' => strtoupper($rate->service_name),
                    'cost' => $manualCost,
                    'cost_formatted' => number_format($manualCost, 0, ',', '.'),
                    'estimation' => $etdDisplay
                ];
            }
        }

        if (empty($options)) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Tidak ada layanan ekspedisi yang tersedia untuk ' . $destinationCity->name
            ]);
        }

        return response()->json(['status' => 'success', 'options' => $options]);
    }

   public function applyVoucher(\Illuminate\Http\Request $request)
    {
       try {
            $host = $request->getHost(); 
            $subdomain = explode('.', $host)[0]; 

            $website = $request->attributes->get('tenant_website') 
                       ?? \App\Models\Website::where('subdomain', $subdomain)->first()
                       ?? \App\Models\Website::where('custom_domain', $host)->first();

            if (!$website) {
                throw new \Exception("Website Tenant tidak ditemukan untuk host: " . $host);
            }

            $code = strtoupper($request->voucher_code);
            
            $cart = session()->get("cart_{$website->id}", []);
            $cartTotal = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);

            if ($cartTotal <= 0) {
                return response()->json(['success' => false, 'message' => 'Keranjang Anda masih kosong.']);
            }

            $voucher = \App\Models\Voucher::where('website_id', $website->id)
                ->where('code', $code)
                ->first();

            if (!$voucher) {
                return response()->json(['success' => false, 'message' => 'Kode voucher tidak ditemukan.']);
            }

            if (!$voucher->isValid()) {
                return response()->json(['success' => false, 'message' => 'Voucher tidak aktif, kuota habis, atau sudah kedaluwarsa.']);
            }

            if ($cartTotal < $voucher->min_purchase) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Minimal belanja untuk voucher ini adalah Rp ' . number_format($voucher->min_purchase, 0, ',', '.')
                ]);
            }

            if ($voucher->target_rfm_segment) {
                $customer = \Illuminate\Support\Facades\Auth::guard('customer')->user();
                
                if (!$customer) {
                    return response()->json(['success' => false, 'message' => 'Anda harus login untuk menggunakan voucher eksklusif ini.']);
                }

                if (!class_exists(\App\Models\CustomerRfm::class)) {
                    throw new \Exception('Model CustomerRfm tidak ditemukan. Apakah namanya CustomerRFM?');
                }

                $rfm = \App\Models\CustomerRfm::where('website_id', $website->id)
                    ->where('customer_id', $customer->id) 
                    ->first();

                if (!$rfm || $rfm->segment !== $voucher->target_rfm_segment) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'Maaf, voucher ini eksklusif hanya untuk pelanggan dengan status: ' . $voucher->target_rfm_segment
                    ]);
                }
            }
            
            $customerId = \Illuminate\Support\Facades\Auth::guard('customer')->id(); 

            if (!$customerId) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Anda harus login terlebih dahulu untuk menggunakan kode voucher ini.'
                ]);
            }

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

            $discountAmount = 0;
            if ($voucher->discount_type === 'nominal') {
                $discountAmount = $voucher->discount_value;
            } else {
                $discountAmount = $cartTotal * ($voucher->discount_value / 100);
                if ($voucher->max_discount_amount && $discountAmount > $voucher->max_discount_amount) {
                    $discountAmount = $voucher->max_discount_amount;
                }
            }

            if ($discountAmount > $cartTotal) {
                $discountAmount = $cartTotal;
            }

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
            return response()->json([
                'success' => false,
                'message' => 'Error PHP: ' . $e->getMessage() . ' (Baris ' . $e->getLine() . ')'
            ]);
        }
    }

    public function removeVoucher(Request $request)
    {
        try {
            $website = $request->get('website');
            
            session()->forget("applied_voucher_{$website->id}");

            $cartKey = 'cart_' . $website->id;
            $cart = session()->get($cartKey, []);
            $cartTotal = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);

            return response()->json([
                'success' => true,
                'message' => 'Voucher berhasil dibatalkan.',
                'original_total' => $cartTotal,
                'original_total_formatted' => 'Rp ' . number_format($cartTotal, 0, ',', '.')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function addBundle(Request $request)
    {
        $website = $request->website;
        
        $request->validate([
            'main_product_id' => 'required|integer',
            'variant_id' => 'nullable|integer',
            'bundle_product_ids' => 'required|array',
        ]);

        $cart = session()->get('cart_' . $website->id, []);
        $totalBundlePrice = 0;
        $bundleCartKeys = []; 

        $addItemToCart = function($product, $varId, $qty) use (&$cart, &$totalBundlePrice, &$bundleCartKeys) {
            $cartKey = $varId ? $product->id . '_' . $varId : $product->id;
            $bundleCartKeys[] = $cartKey; 

            // Cek jumlah akhir yang akan dimasukkan ke keranjang
            $currentQty = isset($cart[$cartKey]) ? $cart[$cartKey]['quantity'] : 0;
            $newQty = $currentQty + $qty;

            // 🚨 HITUNG HARGA GROSIR (Jika pembelian ini menembus batas min_qty) 🚨
            $dynamicPrice = $this->getApplicablePrice($product->id, $varId, $newQty);
            
            $variantName = null;
            $weight = $product->weight ?? 1000;

            if ($varId) {
                $variant = $product->variants()->find($varId);
                if ($variant) {
                    $variantName = $variant->name;
                    $weight = $variant->weight ?? $weight;
                }
            }

            if(isset($cart[$cartKey])) {
                $cart[$cartKey]['quantity'] = $newQty;
                $cart[$cartKey]['price'] = $dynamicPrice; // Terapkan Harga Grosir
            } else {
                $cart[$cartKey] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => $dynamicPrice, // Terapkan Harga Grosir
                    'quantity' => $newQty,
                    'weight' => $weight,
                    'image' => $product->image,
                    'variant_id' => $varId,
                    'variant_name' => $variantName
                ];
            }
            $totalBundlePrice += ($dynamicPrice * $qty);
        };

        // 1. MASUKKAN PRODUK UTAMA KE KERANJANG
        $mainProduct = \App\Models\Product::findOrFail($request->main_product_id);
        $addItemToCart($mainProduct, $request->variant_id, 1);

        // 2. MASUKKAN PRODUK PELENGKAP (BUNDLING)
        foreach($request->bundle_product_ids as $bId) {
            $bProduct = \App\Models\Product::find($bId);
            if($bProduct && $bProduct->is_active && $bProduct->stock > 0) {
                $bVarId = null;
                if($bProduct->hasVariants()) {
                    $firstVariant = $bProduct->variants()->where('is_active', true)->where('stock', '>', 0)->first();
                    if($firstVariant) $bVarId = $firstVariant->id;
                }
                $addItemToCart($bProduct, $bVarId, 1);
            }
        }

        // Simpan keranjang terbaru
        session()->put('cart_' . $website->id, $cart);

        // 3. DAFTARKAN DISKON BUNDLING KE SESSION (Jika ada)
        if ($request->is_discount && $request->discount_percentage > 0) {
            $discountAmount = $totalBundlePrice * ($request->discount_percentage / 100);
            
            session()->put('bundle_discount_' . $website->id, [
                'amount' => $discountAmount,
                'percentage' => $request->discount_percentage,
                'name' => 'Promo Bundling Cerdas (' . $request->discount_percentage . '%)',
                'bundle_keys' => $bundleCartKeys 
            ]);
        }

        return response()->json([
            'status' => 'success', 
            'message' => 'Paket berhasil ditambahkan ke keranjang!'
        ]);
    }
}
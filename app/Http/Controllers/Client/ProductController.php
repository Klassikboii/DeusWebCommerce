<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Website;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    private function getLimit($website)
    {
        $subscription = $website->activeSubscription;
        if ($subscription) {
            return $subscription->package->max_products;
        }
        // Fallback ke Free jika expired/null
        $free = \App\Models\Package::where('price', 0)->first();
        return $free ? $free->max_products : 2;
    }
  public function index(Request $request, Website $website)
    {
        $this->authorize('viewAny', $website);

        $query = $website->products();
        
        // Logika Search (Real-time compatible)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                  ->orWhere('sku', 'like', '%'.$search.'%');
            });
        }

        $products = $query->latest()->paginate(10)->withQueryString();

        // === TAMBAHAN LOGIKA AJAX ===
        if ($request->ajax()) {
            return view('client.products.partials.product_table', compact('website', 'products'))->render();
        }
        // ============================

        // Data statistik limit (tetap dikirim untuk view utama)
        $currentCount = $website->products()->count();
        $limit = $this->getLimit($website);
        $isLimitReached = $currentCount >= $limit;

        return view('client.products.index', compact('website', 'products', 'currentCount', 'limit', 'isLimitReached'));
    }
    public function create(Website $website)
    {
        $this->authorize('create', $website);

        // --- CEGAT DI PINTU DEPAN (Redirect sebelum ngisi form) ---
        $limit = $this->getLimit($website);
        if ($website->products()->count() >= $limit) {
            return redirect()->route('client.products.index', $website->id)
                             ->with('error', "Limit produk tercapai ({$limit} item). Silakan upgrade paket.");
        }
        // ----------------------------------------------------------

        $categories = $website->categories;
        return view('client.products.create', compact('website', 'categories'));
    }
    public function store(Request $request, Website $website)
    {
        // --- 1. TENTUKAN BATAS LIMIT ---
        $limit = 0;
        $subscription = $website->activeSubscription;

        if ($subscription) {
            $limit = $subscription->package->max_products;
        } else {
            $freePackage = \App\Models\Package::where('price', 0)->first();
            $limit = $freePackage ? $freePackage->max_products : 2; 
        }

        // --- 2. CEK JUMLAH PRODUK VS LIMIT ---
        $currentCount = $website->products()->count();
        if ($currentCount >= $limit) {
            return redirect()->back()->with('error', "Ups! Batas produk tercapai ({$limit} item). Paket Anda mungkin telah berakhir. Silakan upgrade.");
        }

        $this->authorize('create', $website);

        // --- 3. VALIDASI INPUT YANG BENAR ---
        $hasVariants = $request->has('has_variants') && $request->has_variants == '1';

        $rules = [
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable',
            'image' => 'nullable|image|max:2048',
            'sku' => 'nullable|string|max:50',
            'weight' => 'nullable|numeric|min:0',
        ];

        // Jika TIDAK pakai varian, Harga & Stok Utama WAJIB diisi
        if (!$hasVariants) {
            $rules['price'] = 'required|numeric|min:0';
            $rules['stock'] = 'required|numeric|min:0';
        }
        $request->validate($rules);
        DB::beginTransaction();

        try {
            // 1. Simpan Produk Utama
            $product = $website->products()->create([
                'category_id' => $request->category_id,
                'name'        => $request->name,
                'slug'        => Str::slug($request->name) . '-' . Str::random(4),
                'description' => $request->description,
                'image'       => $request->file('image') ? $request->file('image')->store('products', 'public') : null,
                'price'       => $hasVariants ? 0 : $request->price,
                'stock'       => $hasVariants ? 0 : $request->stock,
                'weight'      => $request->weight ?? 1000,
                'sku'         => $request->sku,
            ]);

            // 2. Simpan Varian (Jika Ada)
            if ($hasVariants && is_array($request->variants)) {
                $minPrice = null;
                $totalStock = 0;
                
                foreach ($request->variants as $variantData) {
                    if (empty($variantData['name'])) continue;
                    $product->variants()->create([
                        'name'    => $variantData['name'],
                        'options' => ['name' => $variantData['name']], 
                        'price'   => $variantData['price'] ?? 0,
                        'stock'   => $variantData['stock'] ?? 0,
                        'sku'     => $variantData['sku'] ?? null,
                        'weight'  => $request->weight ?? 1000, 
                    ]);
                    
                    $price = (int) ($variantData['price'] ?? 0);
                    if ($minPrice === null || $price < $minPrice) $minPrice = $price;
                    $totalStock += (int) ($variantData['stock'] ?? 0);
                }
                
                $product->update(['price' => $minPrice ?? 0, 'stock' => $totalStock]);
            }
            
            DB::commit();

            // =========================================================
            // 3. SINKRONISASI KE ACCURATE (BARANG + INISIALISASI STOK)
            // =========================================================
            $accurateSyncFailed = false; 
            try {
                $accurateService = new \App\Services\AccurateService($website);
                $product->refresh(); 

                if ($hasVariants && $product->variants->count() > 0) {
                    foreach ($product->variants as $variant) {
                        // Sync Barang
                        $status = $accurateService->syncProductVariant($variant);
                        // Sync Stok Awal (Jika barang sukses dibuat)
                        if ($status && $variant->stock > 0) {
                            $accurateService->syncInventoryAdjustment($variant->sku, $variant->stock);
                        }
                        if (!$status) $accurateSyncFailed = true;
                    }
                } else {
                    $singleItem = (object)[
                        'sku' => $product->sku,
                        'price' => $product->price,
                        'name' => '', 
                        'product' => $product
                    ];
                    // Sync Barang
                    $status = $accurateService->syncProductVariant($singleItem);
                    // Sync Stok Awal (Jika barang sukses dibuat)
                    if ($status && $product->stock > 0) {
                        $accurateService->syncInventoryAdjustment($product->sku, $product->stock);
                    }
                    if (!$status) $accurateSyncFailed = true;
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Accurate Sync Store Error: ' . $e->getMessage());
                $accurateSyncFailed = true;
            }

            if ($accurateSyncFailed) {
                return redirect()->route('client.products.index', $website->id)
                    ->with('warning', 'Produk tersimpan, namun sinkronisasi Accurate gagal (Cek SKU).');
            }

            return redirect()->route('client.products.index', $website->id)
                ->with('success', 'Produk berhasil ditambahkan dan tersinkronisasi dengan Accurate!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan produk: ' . $e->getMessage())->withInput();
        }
    }

    // 1. TAMPILKAN FORM EDIT
    public function edit(Website $website, Product $product)
    {
        // Security Check: Pastikan produk milik website ini
        if ($product->website_id !== $website->id) abort(403);

        $categories = $website->categories;
        return view('client.products.edit', compact('website', 'product', 'categories'));
    }

    // 2. PROSES UPDATE DATA
   public function update(Request $request, Website $website, Product $product)
{
    $this->authorize('update', $website);
    // 🚨 CCTV 1: CEK DATA YANG DIKIRIM DARI HTML
        \Illuminate\Support\Facades\Log::info("CCTV 1 - Request Update Produk {$product->sku}: ", $request->all());

    // 1. Cek Status Varian (Checkbox HTML tidak kirim value kalau uncheck, jadi kita cek keberadaannya)
    $hasVariants = $request->has('has_variants') && $request->has_variants == '1';

    // 2. Validasi Dasar
    $rules = [
        'name' => 'required|string|max:255',
        'category_id' => 'nullable|exists:categories,id',
        'image' => 'nullable|image|max:2048',
        'description' => 'nullable',
        'sku' => 'nullable|string|max:50',
        'weight' => 'nullable|numeric|min:0',
    ];

    // Jika TIDAK pakai varian, Harga & Stok Utama WAJIB diisi
    if (!$hasVariants) {
        $rules['price'] = 'required|numeric|min:0';
        $rules['stock'] = 'required|numeric|min:0';
    }

    $request->validate($rules);
    DB::beginTransaction();

        try {
            // =========================================================
           // =========================================================
            // 🚨 ALAT PENYADAP SEMENTARA
            // =========================================================
            $accurateService = new \App\Services\AccurateService($website);
            // $accurateService->debugAdjustmentFormat(); // Kosongkan dalam kurungnya!
            // =========================================================
            // 🚨 TANGKAP STOK LAMA SEBELUM DI-UPDATE!
            // =========================================================
            $oldStocks = [];
            if ($product->variants->count() > 0) {
                foreach ($product->variants as $v) {
                    $oldStocks[$v->sku] = $v->stock;
                }
            } else {
                $oldStocks[$product->sku] = $product->stock;
            }
            // 🚨 CCTV 2: CEK DATA YANG AKAN DISIMPAN KE DATABASE LOKAL
            $dataToUpdate = [
                'category_id' => $request->category_id,
                'name'        => $request->name,
                'description' => $request->description,
                'weight'      => $request->weight ?? 1000,
                'sku'         => $request->sku,
                'price'       => $hasVariants ? 0 : $request->price,
                'stock'       => $hasVariants ? 0 : $request->stock,
            ];

            // ==========================================
            // 🚨 TAMBAHAN: LOGIKA UPDATE GAMBAR
            // ==========================================
            if ($request->hasFile('image')) {
                // 1. Hapus gambar lama (jika ada) agar hardisk tidak penuh
                if ($product->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($product->image)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image);
                }
                
                // 2. Simpan gambar baru ke folder storage/app/public/products
                $dataToUpdate['image'] = $request->file('image')->store('products', 'public');
            }
            // ==========================================
            \Illuminate\Support\Facades\Log::info("CCTV 2 - Data yang akan di-save: ", $dataToUpdate);

            $product->update($dataToUpdate);

            // ... (KODE UPDATE PRODUK UTAMA & VARIAN ANDA TETAP SAMA SEPERTI SEBELUMNYA) ...
            // (Pastikan Anda menggunakan kode update bawaan Anda dari baris $dataToUpdate = [...] sampai $product->variants()->delete();)
            
            DB::commit();
            // 🚨 CCTV 3: CEK APAKAH DATABASE BERHASIL COMMIT
            \Illuminate\Support\Facades\Log::info("CCTV 3 - Sukses Commit DB Lokal! Stok sekarang: " . $product->stock);

           // =========================================================
            // SINKRONISASI UPDATE & SELISIH STOK KE ACCURATE (VERSI CERDAS)
            // =========================================================
            $accurateSyncFailed = false;
            try {
                $accurateService = new \App\Services\AccurateService($website);
                $product->refresh(); 

                if ($product->variants->count() > 0) {
                    foreach ($product->variants as $variant) {
                        // 1. Update/Buat Wujud Barang di Accurate
                        $status = $accurateService->syncProductVariant($variant);
                        
                        // 2. 🚨 IDE ANDA: Tanya Accurate, "Stok kamu sekarang berapa?"
                        $stokAccurate = $accurateService->getAccurateStock($variant->sku);
                        
                        // 3. 🚨 IDE ANDA: Hitung Selisih
                        $selisihStok = $variant->stock - $stokAccurate;
                        
                        // 4. Kirim Penyesuaian ke Accurate (Hanya jika ada selisih)
                        if ($status && $selisihStok != 0) {
                            $accurateService->syncInventoryAdjustment($variant->sku, $selisihStok);
                        }
                        if (!$status) $accurateSyncFailed = true;
                    }
                } else {
                    $singleItem = (object)[
                        'sku' => $product->sku,
                        'price' => $product->price,
                        'name' => '', 
                        'product' => $product
                    ];
                    
                    // 1. Update/Buat Wujud Barang di Accurate
                    $status = $accurateService->syncProductVariant($singleItem);
                    
                    // 2. 🚨 IDE ANDA: Tanya Accurate
                    $stokAccurate = $accurateService->getAccurateStock($product->sku);
                    
                    // 3. 🚨 IDE ANDA: Hitung Selisih
                    $selisihStok = $product->stock - $stokAccurate;

                    // 4. Kirim Penyesuaian
                    if ($status && $selisihStok != 0) {
                        $accurateService->syncInventoryAdjustment($product->sku, $selisihStok);
                    }
                    if (!$status) $accurateSyncFailed = true;
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Accurate Sync Update Error: ' . $e->getMessage());
                $accurateSyncFailed = true;
            }

            if ($accurateSyncFailed) {
                return redirect()->route('client.products.index', $website->id)
                    ->with('warning', 'Produk diperbarui, namun sinkronisasi Accurate gagal.');
            }

            return redirect()->route('client.products.index', $website->id)
                ->with('success', 'Produk berhasil diperbarui di Toko dan Accurate!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal update: ' . $e->getMessage())->withInput();
        }
    }
    // 3. PROSES HAPUS DATA
    public function destroy(Website $website, Product $product)
    {
        if ($product->website_id !== $website->id) abort(403);

        // Hapus file gambar dari folder
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->back()->with('success', 'Produk dihapus');
    }
}
<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Website;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Services\AccurateService;

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
  // 2. PROSES UPDATE DATA
    public function update(Request $request, Website $website, Product $product)
    {
        $this->authorize('update', $website);
        
        \Illuminate\Support\Facades\Log::info("CCTV 1 - Request Update Produk {$product->sku}: ", $request->all());

        // 1. Cek Status Varian
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
            // ==========================================
            // 1. UPDATE DATA PRODUK UTAMA
            // ==========================================
            $dataToUpdate = [
                'category_id' => $request->category_id,
                'name'        => $request->name,
                'description' => $request->description,
                'weight'      => $request->weight ?? 1000,
                // Induk tidak boleh punya SKU jika dia punya varian (agar Accurate tidak bingung)
                'sku'         => $hasVariants ? null : $request->sku, 
            ];

            // Logika Update Gambar
            if ($request->hasFile('image')) {
                if ($product->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($product->image)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image);
                }
                $dataToUpdate['image'] = $request->file('image')->store('products', 'public');
            }

            $product->update($dataToUpdate);

            // ==========================================
            // 2. UPDATE VARIAN DI DATABASE LOKAL
            // ==========================================
            if ($hasVariants && is_array($request->variants)) {
                $variantIdsToKeep = [];
                $minPrice = null;
                $totalStock = 0;

                foreach ($request->variants as $variantData) {
                    if (empty($variantData['sku']) || empty($variantData['name'])) continue;

                    $stockInput = (int)($variantData['stock'] ?? 0);
                    $priceInput = (float)($variantData['price'] ?? 0);

                    // Simpan atau Update Varian
                    $variant = \App\Models\ProductVariant::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'sku' => $variantData['sku'],
                        ],
                        [
                            'name' => $variantData['name'],
                            'price' => $priceInput,
                            'stock' => $stockInput,
                        ]
                    );

                    $variantIdsToKeep[] = $variant->id;

                    // Hitung total stok dan harga minimal untuk di-set di induk
                    if ($minPrice === null || $priceInput < $minPrice) $minPrice = $priceInput;
                    $totalStock += $stockInput;
                }

                // Hapus varian yang dihapus oleh admin di form
                \App\Models\ProductVariant::where('product_id', $product->id)
                    ->whereNotIn('id', $variantIdsToKeep)
                    ->delete();

                // Update harga minimal dan stok kumulatif ke produk induk
                $product->update(['price' => $minPrice ?? 0, 'stock' => $totalStock]);

            } else {
                // Jika produk berubah dari Varian ke Single, bersihkan sisa variannya
                \App\Models\ProductVariant::where('product_id', $product->id)->delete();
                $product->update([
                    'price' => $request->price,
                    'stock' => $request->stock
                ]);
            }

            DB::commit();
            \Illuminate\Support\Facades\Log::info("CCTV 3 - Sukses Commit DB Lokal! Stok Induk sekarang: " . $product->stock);

            // =========================================================
            // 3. SINKRONISASI UPDATE & SELISIH STOK KE ACCURATE
            // =========================================================
            $accurateSyncFailed = false;
            try {
                $accurateService = new \App\Services\AccurateService($website);
                $product->refresh(); 

                if ($hasVariants && $product->variants->count() > 0) {
                    // --- MODE VARIAN ---
                    foreach ($product->variants as $variant) {
                        $status = $accurateService->syncProductVariant($variant);
                        
                        // Hitung Selisih Stok
                        $stokAccurate = $accurateService->getAccurateStock($variant->sku);
                        $selisihStok = $variant->stock - $stokAccurate;

                        \Illuminate\Support\Facades\Log::info("Update Varian {$variant->sku}: Input ({$variant->stock}) - Accurate ({$stokAccurate}) = Selisih ({$selisihStok})");

                        if ($status && $selisihStok != 0) {
                            $accurateService->syncInventoryAdjustment($variant->sku, $selisihStok);
                        }
                        if (!$status) $accurateSyncFailed = true;
                    }
                } else {
                    // --- MODE SINGLE ITEM ---
                    $singleItem = (object)[
                        'sku' => $product->sku,
                        'price' => $product->price,
                        'name' => '', 
                        'product' => $product
                    ];
                    
                    $status = $accurateService->syncProductVariant($singleItem);
                    
                    // Hitung Selisih Stok
                    $stokAccurate = $accurateService->getAccurateStock($product->sku);
                    $selisihStok = $product->stock - $stokAccurate;

                    \Illuminate\Support\Facades\Log::info("Update Single {$product->sku}: Input ({$product->stock}) - Accurate ({$stokAccurate}) = Selisih ({$selisihStok})");

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
                    ->with('warning', 'Produk diperbarui, namun sinkronisasi Accurate gagal (Cek SKU/Koneksi).');
            }

            return redirect()->route('client.products.index', $website->id)
                ->with('success', 'Produk berhasil diperbarui di Toko dan sinkron dengan Accurate!');

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
    // 1. Fungsi Download Template CSV
    public function downloadTemplate()
    {
        $headers = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=template_import_produk.csv',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $columns = ['SKU', 'Nama Produk', 'Harga', 'Stok', 'Berat (Gram)', 'Deskripsi'];

        $callback = function() use($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns); // Tulis header
            // Tulis satu baris contoh
            fputcsv($file, ['PROD-001', 'Sepatu Sneakers Hitam', '250000', '50', '800', 'Sepatu kasual pria ukuran 42']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // 2. Fungsi Proses Import CSV
    public function importCsv(Request $request, $websiteId)
    {
        $request->validate([
            'file_csv' => 'required|mimes:csv,txt|max:2048'
        ]);

        $file = $request->file('file_csv');
        $filePath = $file->getRealPath();
        $fileHandle = fopen($filePath, 'r');
        
        $header = fgetcsv($fileHandle); // Lewati baris pertama (Header)
        $importedCount = 0;
        $updatedCount = 0;

        while ($row = fgetcsv($fileHandle)) {
            // Validasi: Abaikan baris kosong atau yang tidak punya nama
            if (empty($row[0]) || empty($row[1])) {
                continue; 
            }

            $sku = trim($row[0]);
            $name = trim($row[1]);
            $price = (int) $row[2];
            $stock = (int) $row[3];
            $weight = (int) ($row[4] ?? 1000); // Default 1000 gram jika kosong
            $description = $row[5] ?? '';

            // LOGIKA INTI: Update jika SKU ada, Create jika SKU baru
            $product = Product::updateOrCreate(
                [
                    'website_id' => $websiteId,
                    'sku' => $sku, // SKU adalah penentu utama
                ],
                [
                    'name' => $name,
                    'slug' => Str::slug($name) . '-' . Str::random(4),
                    'price' => $price,
                    'stock' => $stock,
                    'weight' => $weight,
                    'description' => $description,
                    'is_active' => true
                ]
            );

            if ($product->wasRecentlyCreated) {
                $importedCount++;
            } else {
                $updatedCount++;
            }
        }

        fclose($fileHandle);

        return back()->with('success', "Import berhasil! $importedCount produk baru ditambahkan, $updatedCount produk diupdate.");
    }
public function syncAccurate(Request $request, $websiteId)
{
    $website = \App\Models\Website::findOrFail($websiteId);

    // Cek apakah toko ini sudah terhubung ke Accurate
    if (!$website->accurateIntegration || !$website->accurateIntegration->access_token) {
        return back()->with('error', 'Toko ini belum terhubung ke Accurate Online. Silakan hubungkan di menu Pengaturan.');
    }

    // Panggil Mesin Penyedot Data
    $accurateService = new AccurateService($website);
    $success = $accurateService->syncProductsFromAccurate();

    if ($success) {
        return back()->with('success', 'Sinkronisasi berhasil! Data produk (Harga, Stok, dan Status) telah diperbarui sesuai dengan Accurate Online.');
    } else {
        return back()->with('error', 'Gagal menarik data dari Accurate. Silakan cek log sistem atau pastikan koneksi API valid.');
    }
}
public function destroyAll(Request $request, $websiteId)
    {
        $website = \App\Models\Website::findOrFail($websiteId);

        // Ambil semua ID produk dari toko ini
        $productIds = $website->products()->pluck('id');

        // Cari ID produk yang sudah nyangkut di pesanan (order_items)
        $usedProductIds = \App\Models\OrderItem::whereIn('product_id', $productIds)
                                               ->pluck('product_id')
                                               ->toArray();

        // 1. Hapus Permanen produk yang BELUM PERNAH dipesan
        $website->products()->whereNotIn('id', $usedProductIds)->delete();

        // 2. Nonaktifkan (Soft Delete) produk yang SUDAH PERNAH dipesan
        $website->products()->whereIn('id', $usedProductIds)->update([
            'is_active' => false,
            'stock' => 0
        ]);

        return back()->with('success', 'Katalog berhasil dikosongkan! Produk yang memiliki riwayat pesanan hanya dinonaktifkan demi keamanan data.');
    }
}
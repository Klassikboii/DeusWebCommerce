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
use App\Models\OrderItem;

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
        
        // 1. PENCARIAN (Nama & SKU)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                  ->orWhere('sku', 'like', '%'.$search.'%');
            });
        }

        // 2. FILTER STATUS
        $query->when($request->filled('status'), function ($q) use ($request) {
            $q->where('is_active', $request->status);
        });

        // 3. FILTER STOK
        $query->when($request->filled('stock_status'), function ($q) use ($request) {
            $q->where('stock_status', $request->stock_status); 
        });

        // 4. FILTER GAMBAR
        $query->when($request->filled('image_status'), function ($q) use ($request) {
            if ($request->image_status == 'missing') {
                $q->where(function($subQ) {
                    $subQ->whereNull('image')->orWhere('image', '');
                });
            } else {
                $q->whereNotNull('image')->where('image', '!=', '');
            }
        });

        // 5. SISTEM SORTING (Termasuk Status)
        $sort = $request->input('sort', 'newest'); 
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'status_active_first':
                $query->orderBy('is_active', 'desc'); // 1 (Aktif) di atas
                break;
            case 'status_inactive_first':
                $query->orderBy('is_active', 'asc');  // 0 (Non-Aktif) di atas
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // 6. PAGINATION
        $products = $query->paginate(10)->withQueryString();

        // === LOGIKA AJAX ===
        if ($request->ajax()) {
            // Pastikan path view ini sesuai dengan file partial Anda
            return view('client.products.partials.product_table', compact('website', 'products'))->render();
        }
        // ===================

        // 7. STATISTIK & LIMIT
        $currentCount = $website->products()->count();
        $limit = $this->getLimit($website);
        $isLimitReached = $currentCount >= $limit;
        $activeCount = $website->products()->where('is_active', true)->count();

        return view('client.products.index', compact('website', 'products', 'currentCount', 'limit', 'isLimitReached', 'activeCount'));
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
                'is_active'   => $request->has('is_active'), // 🚨 TAMBAHAN
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
                        'is_active' => isset($variantData['is_active']) ? $variantData['is_active'] : 1, // 🚨 TAMBAHAN
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
                        $status = $accurateService->syncItemToAccurate($variant);
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
                        'name' => $product->name,
                        'product' => $product,
                        'stock' => $product->stock // 🚨 INI YANG KEMARIN HILANG!
                    ];
                    // Sync Barang
                    $status = $accurateService->syncItemToAccurate($singleItem);
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
                'is_active'   => $request->has('is_active') ? 1 : 0,
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

                foreach ($request->variants as $index => $variantData) {
                    if (empty($variantData['sku']) || empty($variantData['name'])) continue;

                    $stockInput = (int)($variantData['stock'] ?? 0);
                    $priceInput = (float)($variantData['price'] ?? 0);
                    
                    // 🚨 CEK APAKAH USER MENEKAN TOMBOL HAPUS GAMBAR
                    $isRemoveImage = isset($variantData['remove_image']) && $variantData['remove_image'] == '1';

                    $variant = \App\Models\ProductVariant::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'sku' => $variantData['sku'],
                        ],
                        [
                            'name'          => $variantData['name'],
                            'price'         => $priceInput,
                            'stock'         => $stockInput,
                            'is_active'     => isset($variantData['is_active']) ? $variantData['is_active'] : 1,
                        ]
                    );

                    // 🚨 LOGIKA GAMBAR FINAL
                    if ($request->hasFile("variants.{$index}.image")) {
                        // 1. Jika User Mengupload Gambar Baru
                        if ($variant->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($variant->image)) {
                            \Illuminate\Support\Facades\Storage::disk('public')->delete($variant->image);
                        }
                        $variant->update(['image' => $request->file("variants.{$index}.image")->store('variants', 'public')]);
                    
                    } elseif ($isRemoveImage) {
                        // 2. Jika User menekan tombol silang X (Tanpa upload gambar baru)
                        if ($variant->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($variant->image)) {
                            \Illuminate\Support\Facades\Storage::disk('public')->delete($variant->image);
                        }
                        $variant->update(['image' => null]);
                    }

                    $variantIdsToKeep[] = $variant->id;

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
                        $status = $accurateService->syncItemToAccurate($variant);
                        
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
                        'name' => $product->name,
                        'product' => $product
                    ];
                    
                    $status = $accurateService->syncItemToAccurate($singleItem);
                    
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
    // 3. PROSES HAPUS DATA (SAFE DELETE)
    public function destroy(Website $website, Product $product)
    {
        if ($product->website_id !== $website->id) abort(403);

        // 1. Cek apakah ada pesanan yang MASIH PENDING
        $pendingOrders = \App\Models\OrderItem::where('product_id', $product->id)
            ->whereHas('order', function($q) {
                $q->whereIn('status', ['pending', 'processing', 'shipped']);
            })->exists();

        if ($pendingOrders) {
            return back()->with('error', 'Gagal! Produk ini sedang berada dalam pesanan pelanggan yang belum selesai. Gunakan fitur Edit untuk Menonaktifkannya.');
        }

        // 2. Cek apakah produk ini PERNAH DIBELI sebelumnya (History)
        $hasAnyOrders = \App\Models\OrderItem::where('product_id', $product->id)->exists();

        if ($hasAnyOrders) {
            // Jangan dihapus! Cukup dinonaktifkan agar riwayat pesanan lama tidak error
            $product->update(['is_active' => false, 'stock' => 0]);
            $product->variants()->update(['is_active' => false, 'stock' => 0]);
            
            return back()->with('success', 'Produk memiliki riwayat pesanan, sehingga hanya dinonaktifkan (disembunyikan) demi keamanan data keuangan.');
        }

        // 3. JIKA BELUM PERNAH DIBELI SAMA SEKALI -> Hapus Permanen Aman
        if ($product->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($product->image)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image);
        }
        $product->delete();

        return redirect()->back()->with('success', 'Produk berhasil dihapus permanen.');
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

            // 🚨 LOGIKA SMART ACTIVATION UNTUK CSV
            $isEligibleForSale = $price > 0 ? true : false;

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
                    'is_active' => $isEligibleForSale
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

 public function insight(\App\Models\Website $website, \App\Models\Product $product)
    {
        $this->authorize('view', $website);
        if ($product->website_id !== $website->id) abort(404);

        // ==========================================
        // 🤖 LOGIKA 1: PREDIKSI KUANTITAS RESTOCK (Update Status)
        // ==========================================
        $targetDays = 30; // Target aman stok untuk 1 bulan ke depan
        
        // 🚨 KITA MASUKKAN STANDAR BARU DI SINI UNTUK STATUS TERKINI
        $minVelocityThreshold = 3 / 30; // Standar minimal hidup (0.1 per hari)

        $currentStock = $product->stock;
        $velocity = $product->velocity > 0 ? $product->velocity : 0; // Pastikan positif
        $runwayDays = null;
        $stockStatus = 'Normal';

        if ($currentStock <= 0) {
            $runwayDays = 0;
            $stockStatus = 'Empty';
        } else {
            // Cek standar: Apakah kecepatannya memenuhi syarat minimal?
            if ($velocity >= $minVelocityThreshold) {
                $runwayDays = (int) round($currentStock / $velocity);
                $stockStatus = ($runwayDays <= 7) ? 'Critical' : 'Safe';
            } else {
                // Di bawah standar = Overstock
                $runwayDays = (int) round($currentStock / $minVelocityThreshold); // Runway teoritis
                $stockStatus = 'Overstock'; 
            }
        }

        // Hitung Kuantitas Restock
        $neededForTarget = $velocity * $targetDays; 
        $recommendedRestock = (int) ceil($neededForTarget - $currentStock);
        if ($recommendedRestock < 0) {
            $recommendedRestock = 0;
        }

        // ==========================================
        // 📈 LOGIKA 2: DATA GRAFIK (DUA GARIS) - DIPERBAIKI (Anti-Single Point)
        // ==========================================
        $chartLabels = [];
        $chartData = [];       // Garis Merah (Proyeksi Stok)
        $targetLineData = [];  // Garis Hijau Putus-putus (Batas Aman)
        
        // 🚨 PERBAIKAN: Selama ada stok fisik, GRAFIK HARUS MUNCUL
        if ($currentStock > 0) {
            
            // Tentukan durasi plot: default 30 hari, atau runway jika runway <= 30 (bukan overstock)
            $plotDuration = 30; 
            if ($stockStatus !== 'Overstock' && $runwayDays !== null && $runwayDays <= 30 && $runwayDays > 0) {
                $plotDuration = $runwayDays;
            }

            // Tentukan langkah (step) agar label sumbu X terlihat rapi (maks 6 label)
            // 🚨 PERBAIKAN: Pastikan step minimal 1
            $step = max(1, (int)($plotDuration / 5)); 

            // Loop untuk plot
            for ($i = 0; $i <= $plotDuration; $i += $step) {
                $chartLabels[] = "Hari +" . $i;
                
                // Hitung proyeksi penurunan (max 0). Jika velocity 0, nilai akan konstan currentStock
                $projectedValue = $currentStock;
                if ($velocity > 0) {
                   $projectedValue = max(0, $currentStock - ($velocity * $i));
                }
                $chartData[] = round($projectedValue);
                
                // Hitung batas aman (neededForTarget) - mendatar konstan
                $targetLineData[] = round($neededForTarget); 
            }
            
            // Tambahkan titik "Habis" di ujung jika durasi plot <= 30 hari (bukan overstock)
            if ($stockStatus !== 'Overstock' && $runwayDays !== null && $runwayDays <= 30 && $runwayDays > 0) {
                if (!in_array("Hari +" . $runwayDays . " (Habis)", $chartLabels)) {
                    $chartLabels[] = "Hari +" . $runwayDays . " (Habis)";
                    $chartData[] = 0;
                    $targetLineData[] = round($neededForTarget);
                }
            }
        }

        // Update status stok terkini ke model agar sinkron dengan View
        $product->update([
            'velocity' => $velocity,
            'runway_days' => $runwayDays,
            'stock_status' => $stockStatus
        ]);
$penjualantotal = OrderItem::where('product_id', $product->id)->sum('qty'); 
        return view('client.products.insight', compact(
            'website', 'product', 'targetDays', 'recommendedRestock', 'chartLabels', 'chartData', 'targetLineData', 'penjualantotal'
        ));
    }
    /**
     * Mengubah status Aktif/Inaktif langsung dari tabel Index (Inline Toggle)
     */
    public function toggleActive(Request $request, $websiteId, $productId)
    {
        try {
            $website = Website::findOrFail($websiteId);
            $product = $website->products()->findOrFail($productId);
            
            $newStatus = filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN);

            // 🚨 SATPAM KUOTA: Jika klien mencoba MENGAKTIFKAN produk
            if ($newStatus === true) {
                $activeCount = $website->products()->where('is_active', true)->count();
                $limit = $this->getLimit($website);

                if ($activeCount >= $limit) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Kuota produk aktif penuh! Maksimal {$limit} produk. Silakan nonaktifkan produk lain terlebih dahulu."
                    ], 403); // 403 = Forbidden (Ditolak)
                }
            }

            // Jika aman, atau jika klien MENGINAKTIFKAN produk, simpan perubahannya
            $product->update(['is_active' => $newStatus]);

            // Hitung ulang jumlah produk aktif untuk memperbarui angka di UI
            $newActiveCount = $website->products()->where('is_active', true)->count();

            return response()->json([
                'status' => 'success',
                'active_count' => $newActiveCount,
                'message' => 'Status produk berhasil diperbarui!'
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan sistem.'], 500);
        }
    }
}
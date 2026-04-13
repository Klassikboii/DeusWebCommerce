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
        
        // 1. PENCARIAN (Nama & SKU Induk + Varian)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                // A. Cari di tabel Induk
                $q->where('name', 'like', '%'.$search.'%')
                  ->orWhere('sku', 'like', '%'.$search.'%')
                // B. Cari di tabel Anak (Varian)
                  ->orWhereHas('variants', function($qVarian) use ($search) {
                      $qVarian->where('name', 'like', '%'.$search.'%')
                              ->orWhere('sku', 'like', '%'.$search.'%');
                  });
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
        
        $activeCount = $website->products()->where('is_active', true)->count();
        $isLimitReached = $activeCount >= $limit;

        return view('client.products.index', compact('website', 'products', 'currentCount', 'limit', 'isLimitReached', 'activeCount'));
    }
    public function create(Website $website)
    {
        $this->authorize('create', $website);

        // --- CEGAT DI PINTU DEPAN (Redirect sebelum ngisi form) ---
        $limit = $this->getLimit($website);
        // if ($website->products()->count() >= $limit) {
        //     return redirect()->route('client.products.index', $website->id)
        //                      ->with('error', "Limit produk tercapai ({$limit} item). Silakan upgrade paket.");
        // }
        // // ----------------------------------------------------------

        $categories = $website->categories;
        return view('client.products.create', compact('website', 'categories'));
    }
    public function store(Request $request, Website $website)
    {
        $this->authorize('create', $website);

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
        $activeCount = $website->products()->where('is_active', true)->count();

        // Tentukan Niat Awal User (Apakah dia centang checkbox?)
        $userWantsActive = $request->has('is_active') && $request->is_active != '0';
        
        // Siapkan variabel default
        $finalIsActive = $userWantsActive; 
        $alertType = 'success';
        $alertMessage = 'Produk berhasil ditambahkan dan tersinkronisasi dengan Accurate!';
        

        // 🚨 LOGIKA PEMAKSAAN LIMIT 
        if ($userWantsActive && $activeCount >= $limit) {
            $finalIsActive = false; // PAKSA MATIKAN!
            $alertType = 'warning';
            $alertMessage = "Produk berhasil disimpan, namun diset Non-Aktif karena Kuota Etalase penuh (Maksimal {$limit} produk).";
        }

        // --- 3. VALIDASI INPUT YANG BENAR ---
        $hasVariants = $request->has('has_variants') && $request->has_variants == '1';

        $mainSku = $request->sku;
            if (!$hasVariants && empty($mainSku)) {
                $mainSku = $this->generateUniqueSku();
            }

        $rules = [
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable',
            'image' => 'nullable|image|max:2048',
            'sku' => 'nullable|string|max:50',
            'weight' => 'nullable|numeric|min:0',
        ];

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
                'slug'        => \Illuminate\Support\Str::slug($request->name) . '-' . \Illuminate\Support\Str::random(4),
                'description' => $request->description,
                'image'       => $request->file('image') ? $request->file('image')->store('products', 'public') : null,
                'price'       => $hasVariants ? 0 : $request->price,
                'stock'       => $hasVariants ? 0 : $request->stock,
                'weight'      => $request->weight ?? 1000,
                'sku'         => $hasVariants ? null : $mainSku, // 🚨 Gunakan $mainSku di sini                // 🚨 GUNAKAN VARIABEL FINAL DI SINI
                'is_active'   => $finalIsActive, 
            ]);

            // 2. Simpan Varian (Jika Ada)
            if ($hasVariants && is_array($request->variants)) {
                $minPrice = null;
                $totalStock = 0;
                
                foreach ($request->variants as $variantData) {
                    if (empty($variantData['name'])) continue;

                    // 🚨 LOGIKA AUTO-SKU VARIAN
                    $variantSku = !empty($variantData['sku']) ? $variantData['sku'] : $this->generateUniqueSku();
                    
                    // Varian juga mengikuti status induknya
                    $variantIsActive = isset($variantData['is_active']) ? $variantData['is_active'] : 1;
                    if (!$finalIsActive) $variantIsActive = 0; // Jika induk dipaksa mati, varian ikut mati

                    $product->variants()->create([
                        'name'      => $variantData['name'],
                        'options'   => ['name' => $variantData['name']], 
                        'price'     => $variantData['price'] ?? 0,
                        'stock'     => $variantData['stock'] ?? 0,
                        'sku'       => $variantSku, // 🚨 Gunakan $variantSku di sini                        'weight'    => $request->weight ?? 1000, 
                        // 🚨 GUNAKAN STATUS YANG SUDAH DISESUAIKAN
                        'is_active' => $variantIsActive, 
                    ]);
                    
                    $price = (int) ($variantData['price'] ?? 0);
                    if ($minPrice === null || $price < $minPrice) $minPrice = $price;
                    $totalStock += (int) ($variantData['stock'] ?? 0);
                }
                
                $product->update(['price' => $minPrice ?? 0, 'stock' => $totalStock]);
            }
            
            DB::commit();

            // =========================================================
            // 3. SINKRONISASI KE ACCURATE 
            // =========================================================
            $accurateSyncFailed = false; 
            try {
                $accurateService = new \App\Services\AccurateService($website);
                $product->refresh(); 

                if ($hasVariants && $product->variants->count() > 0) {
                    foreach ($product->variants as $variant) {
                        $status = $accurateService->syncItemToAccurate($variant);
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
                        'stock' => $product->stock 
                    ];
                    $status = $accurateService->syncItemToAccurate($singleItem);
                    if ($status && $product->stock > 0) {
                        $accurateService->syncInventoryAdjustment($product->sku, $product->stock);
                    }
                    if (!$status) $accurateSyncFailed = true;
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Accurate Sync Store Error: ' . $e->getMessage());
                $accurateSyncFailed = true;
            }

            // 🚨 TIMPA PESAN JIKA ACCURATE GAGAL TAPI PRODUK TERSIMPAN
            if ($accurateSyncFailed) {
                $alertType = 'warning';
                $alertMessage = 'Produk tersimpan, namun sinkronisasi Accurate gagal (Cek SKU/Koneksi).';
            }

            // 🚨 RETURN DENGAN VARIABEL DINAMIS
            return redirect()->route('client.products.index', $website->id)
                ->with($alertType, $alertMessage);

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
public function update(Request $request, Website $website, Product $product)
    {
        $this->authorize('update', $website);
        
        \Illuminate\Support\Facades\Log::info("CCTV 1 - Request Update Produk {$product->sku}: ", $request->all());

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
        // PENTING: Pengecualian ID agar produk ini tidak menghitung dirinya sendiri
        $activeCount = $website->products()
                               ->where('is_active', true)
                               ->where('id', '!=', $product->id)
                               ->count();

        // Tentukan Niat Awal User
        $userWantsActive = $request->has('is_active') && $request->is_active != '0';
        
        // Siapkan variabel default
        $finalIsActive = $userWantsActive; 
        $alertType = 'success';
        $alertMessage = 'Produk berhasil diperbarui di Toko dan sinkron dengan Accurate!';

        // 🚨 LOGIKA PEMAKSAAN LIMIT 
        if ($userWantsActive && $activeCount >= $limit) {
            $finalIsActive = false; // PAKSA MATIKAN!
            $alertType = 'warning';
            $alertMessage = "Produk diperbarui, namun diset Non-Aktif karena Kuota Etalase penuh (Maksimal {$limit} produk).";
        }

        // --- 3. VALIDASI DASAR ---
        $hasVariants = $request->has('has_variants') && $request->has_variants == '1';
        if (!$hasVariants && empty($mainSku)) {
                // Gunakan SKU lama jika ada, jika tidak ada (kosong dari awal) buatkan baru
                $mainSku = $product->sku ?: $this->generateUniqueSku();
            }

        $rules = [
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|max:2048',
            'description' => 'nullable',
            'sku' => 'nullable|string|max:50',
            'weight' => 'nullable|numeric|min:0',
        ];

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
                'sku'         => $hasVariants ? null : $mainSku, // 🚨 Gunakan $mainSku
                'is_active'   => $finalIsActive, // 🚨 PAKAI VARIABEL FINAL
            ];
            $product->update($dataToUpdate);

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

                    // 🚨 LOGIKA AUTO-SKU VARIAN (Cari SKU Lama atau Bikin Baru)
                    $variantSku = $variantData['sku'] ?? null;
                    if (empty($variantSku)) {
                        $variantSku = $this->generateUniqueSku();
                    }

                    $stockInput = (int)($variantData['stock'] ?? 0);
                    $priceInput = (float)($variantData['price'] ?? 0);
                    $isRemoveImage = isset($variantData['remove_image']) && $variantData['remove_image'] == '1';

                    // 🚨 Varian mengikuti status induknya
                    $variantIsActive = isset($variantData['is_active']) ? $variantData['is_active'] : 1;
                    if (!$finalIsActive) $variantIsActive = 0; // Jika induk dipaksa mati, anak ikut mati

                    $variant = \App\Models\ProductVariant::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'sku' => $variantSku,
                        ],
                        [
                            'name'      => $variantData['name'],
                            'price'     => $priceInput,
                            'stock'     => $stockInput,
                            'is_active' => $variantIsActive, // 🚨 PAKAI VARIABEL FINAL
                        ]
                    );

                    // Logika Gambar Varian
                    if ($request->hasFile("variants.{$index}.image")) {
                        if ($variant->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($variant->image)) {
                            \Illuminate\Support\Facades\Storage::disk('public')->delete($variant->image);
                        }
                        $variant->update(['image' => $request->file("variants.{$index}.image")->store('variants', 'public')]);
                    
                    } elseif ($isRemoveImage) {
                        if ($variant->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($variant->image)) {
                            \Illuminate\Support\Facades\Storage::disk('public')->delete($variant->image);
                        }
                        $variant->update(['image' => null]);
                    }

                    $variantIdsToKeep[] = $variant->id;

                    if ($minPrice === null || $priceInput < $minPrice) $minPrice = $priceInput;
                    $totalStock += $stockInput;
                }

                // Hapus varian yang dibuang
                \App\Models\ProductVariant::where('product_id', $product->id)
                    ->whereNotIn('id', $variantIdsToKeep)
                    ->delete();

                // Update harga & stok induk
                $product->update(['price' => $minPrice ?? 0, 'stock' => $totalStock]);

            } else {
                \App\Models\ProductVariant::where('product_id', $product->id)->delete();
                $product->update([
                    'price' => $request->price,
                    'stock' => $request->stock
                ]);
            }

            DB::commit();

            // =========================================================
            // 3. SINKRONISASI UPDATE & SELISIH STOK KE ACCURATE
            // =========================================================
            $accurateSyncFailed = false;
            try {
                $accurateService = new \App\Services\AccurateService($website);
                $product->refresh(); 

                if ($hasVariants && $product->variants->count() > 0) {
                    foreach ($product->variants as $variant) {
                        $status = $accurateService->syncItemToAccurate($variant);
                        
                        $stokAccurate = $accurateService->getAccurateStock($variant->sku);
                        $selisihStok = $variant->stock - $stokAccurate;

                        if ($status && $selisihStok != 0) {
                            $accurateService->syncInventoryAdjustment($variant->sku, $selisihStok);
                        }
                        if (!$status) $accurateSyncFailed = true;
                    }
                } else {
                    $singleItem = (object)[
                        'sku' => $product->sku,
                        'price' => $product->price,
                        'name' => $product->name,
                        'product' => $product
                    ];
                    
                    $status = $accurateService->syncItemToAccurate($singleItem);
                    
                    $stokAccurate = $accurateService->getAccurateStock($product->sku);
                    $selisihStok = $product->stock - $stokAccurate;

                    if ($status && $selisihStok != 0) {
                        $accurateService->syncInventoryAdjustment($product->sku, $selisihStok);
                    }
                    if (!$status) $accurateSyncFailed = true;
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Accurate Sync Update Error: ' . $e->getMessage());
                $accurateSyncFailed = true;
            }

            // 🚨 TIMPA PESAN JIKA ACCURATE GAGAL TAPI PRODUK TERSIMPAN
            if ($accurateSyncFailed) {
                $alertType = 'warning';
                // Jika tadi sudah kena warning limit, kita gabung pesannya
                if (!$finalIsActive && $userWantsActive) {
                    $alertMessage .= ' (Catatan tambahan: Sinkronisasi ke Accurate juga mengalami kendala).';
                } else {
                    $alertMessage = 'Produk diperbarui, namun sinkronisasi Accurate gagal (Cek SKU/Koneksi).';
                }
            }

            // 🚨 RETURN DENGAN VARIABEL DINAMIS
            return redirect()->route('client.products.index', $website->id)
                ->with($alertType, $alertMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal update: ' . $e->getMessage())->withInput();
        }
    }
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
            // 🤖 LOGIKA 1: PREDIKSI & STATUS (DIPERBAIKI)
            // ==========================================
            $targetDays = 30; 
            $minVelocityThreshold = 1 / 30; // 🚨 Samakan dengan Command (1 barang/bulan)

            $currentStock = $product->stock;
            $velocity = $product->velocity > 0 ? $product->velocity : 0; 
            $runwayDays = null;
            $stockStatus = 'Safe'; // Default
            $criticalThreshold = 14; // Jika Anda ingin mengetes Critical di bawah 30 hari

            if ($currentStock <= 0) {
                $runwayDays = 0;
                $stockStatus = 'Empty';
            } else {
                if ($velocity >= $minVelocityThreshold) {
                    $runwayDays = (int) round($currentStock / $velocity);
                    // Status Kritis jika laku keras dan mau habis
                    $stockStatus = ($runwayDays <= $criticalThreshold) ? 'Critical' : 'Safe';
                } else {
                    // 🚨 PERBAIKAN: Hanya sebut Overstock jika stok FISIK banyak (misal > 5)
                    // Jika stok cuma 1-3 tapi lambat, sebut saja "Safe" (Slow Mover)
                    if ($currentStock > 5) {
                        $stockStatus = 'Overstock';
                        $runwayDays = (int) round($currentStock / $minVelocityThreshold); 
                    } else {
                        $stockStatus = 'Safe';
                        $runwayDays = 99; // Indikasi stok awet karena jarang laku
                    }
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

   public function bulkSyncAccurate(Request $request, \App\Models\Website $website)
    {
        $this->authorize('view', $website);

        $conflictResolution = $request->input('conflict_resolution', 'skip');
        $forceUpdatePrice = ($conflictResolution === 'overwrite');

        // Ambil semua produk beserta variannya
        $products = \App\Models\Product::with('variants')->where('website_id', $website->id)->get();

        if ($products->isEmpty()) {
            return back()->with('error', 'Tidak ada produk di katalog web untuk dikirim ke Accurate.');
        }

        $accurateService = new AccurateService($website);
        $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0];

        foreach ($products as $product) {
            // 🚨 CEK APAKAH PRODUK PUNYA VARIAN?
            if ($product->variants->count() > 0) {
                // Looping dan kirim setiap anak (varian)
                foreach ($product->variants as $variant) {
                    // Gabungkan nama agar di Accurate lebih jelas (Contoh: Baju - Merah)
                    $originalName = $variant->name;
                    $variant->name = $product->name . ' - ' . $variant->name; 

                    $result = $accurateService->pushProduct($variant, $forceUpdatePrice);
                    
                    // Rekap Status
                    if ($result['status'] === 'created') $stats['created']++;
                    elseif ($result['status'] === 'updated') $stats['updated']++;
                    elseif ($result['status'] === 'skipped') $stats['skipped']++;
                    else $stats['failed']++;

                    // Kembalikan nama ke semula di memori
                    $variant->name = $originalName;
                }
            } else {
                // Jika produk satuan biasa
                $result = $accurateService->pushProduct($product, $forceUpdatePrice);

                if ($result['status'] === 'created') $stats['created']++;
                elseif ($result['status'] === 'updated') $stats['updated']++;
                elseif ($result['status'] === 'skipped') $stats['skipped']++;
                else $stats['failed']++;
            }
        }

        $message = "Hasil Sinkronisasi Web ke Accurate: {$stats['created']} baru dibuat, {$stats['updated']} harga diupdate, {$stats['skipped']} dilewati, {$stats['failed']} gagal.";
        
        if ($stats['failed'] > 0) {
            return back()->with('warning', $message . ' (Cek file storage/logs/laravel.log untuk detail yang gagal).');
        }
        return back()->with('success', $message);
    }
    /**
     * Membuat SKU 6 karakter unik secara otomatis (Alfanumerik Kapital)
     */
    private function generateUniqueSku()
    {
        do {
            // Menghasilkan 6 karakter acak (contoh: A8X9QB)
            $sku = strtoupper(\Illuminate\Support\Str::random(6));
            
            // Cek apakah SKU ini sudah ada di tabel produk ATAU varian
            $existsInProducts = \App\Models\Product::where('sku', $sku)->exists();
            $existsInVariants = class_exists('\App\Models\ProductVariant') 
                                && \App\Models\ProductVariant::where('sku', $sku)->exists();
                                
        } while ($existsInProducts || $existsInVariants);

        return $sku;
    }
}
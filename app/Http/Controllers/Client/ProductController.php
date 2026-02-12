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
        
        // (Kode pencarian/search yang lama biarkan saja)
        if ($request->search) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $products = $query->latest()->paginate(10);

        // --- TAMBAHAN BARU: HITUNG LIMIT ---
        $currentCount = $website->products()->count();
        $limit = $this->getLimit($website);
        $isLimitReached = $currentCount >= $limit;
        // -----------------------------------

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
            // Skenario A: Punya paket aktif (Pro/Business/Starter)
            $limit = $subscription->package->max_products;
        } else {
            // Skenario B: Tidak punya paket / Expired -> JATUH KE PAKET FREE
            $freePackage = \App\Models\Package::where('price', 0)->first();
            $limit = $freePackage ? $freePackage->max_products : 2; // Default angka 2 jika DB gagal
        }

        // --- 2. CEK JUMLAH PRODUK VS LIMIT ---
        $currentCount = $website->products()->count();
        
        if ($currentCount >= $limit) {
            return redirect()->back()->with('error', "Ups! Batas produk tercapai ({$limit} item). Paket Anda mungkin telah berakhir. Silakan upgrade.");
        }
        // 1. Validasi Input
      $this->authorize('create', $website);

    $request->validate([
        'name' => 'required|string|max:255',
        'category_id' => 'nullable|exists:categories,id',
        'description' => 'nullable',
        'image' => 'nullable|image|max:2048',
        // Validasi Kondisional (Akan kita handle manual atau pakai 'required_without')
    ]);

    // DB Transaction agar aman (Produk + Varian harus sukses bareng)
    DB::beginTransaction();

    try {
        // 1. Simpan Data Produk Utama
        $product = $website->products()->create([
            'category_id' => $request->category_id,
            'name'        => $request->name,
            'slug'        => Str::slug($request->name) . '-' . Str::random(4),
            'description' => $request->description,
            'image'       => $request->file('image') ? $request->file('image')->store('products', 'public') : null,
            // Jika punya varian, set harga/stok utama jadi 0 atau ambil dari varian pertama (opsional)
            'price'       => $request->has_variants ? 0 : $request->price,
            'stock'       => $request->has_variants ? 0 : $request->stock,
            'weight'      => $request->weight ?? 1000,
            'sku'         => $request->sku,
            'status'      => 'active'
        ]);

        // 2. Simpan Varian (Jika dicentang)
        if ($request->has_variants && is_array($request->variants)) {
            
            foreach ($request->variants as $variantData) {
                // Skip jika nama kosong (baris sampah)
                if (empty($variantData['name'])) continue;

                $product->variants()->create([
                    'name'    => $variantData['name'],
                    // Kita simpan opsi sederhana dulu: {"name": "Merah - XL"}
                    // Nanti bisa dikembangkan jadi Key-Value terpisah
                    'options' => ['name' => $variantData['name']], 
                    'price'   => $variantData['price'],
                    'stock'   => $variantData['stock'],
                    'sku'     => $variantData['sku'] ?? null,
                    'weight'  => $request->weight, // Warisi berat induk sementara
                ]);
            }
            
            // Update harga display produk induk (ambil harga terendah varian)
            $minPrice = $product->variants()->min('price');
            $totalStock = $product->variants()->sum('stock');
            
            $product->update([
                'price' => $minPrice,
                'stock' => $totalStock
            ]);
        }

        DB::commit();
        return redirect()->route('client.products.index', $website->id)->with('success', 'Produk berhasil ditambahkan!');

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

    // Validasi mirip store
    $request->validate([
        'name' => 'required|string|max:255',
        'category_id' => 'nullable|exists:categories,id',
        'image' => 'nullable|image|max:2048',
    ]);

    DB::beginTransaction();

    try {
        // 1. Update Data Dasar Produk
        $dataToUpdate = [
            'category_id' => $request->category_id,
            'name' => $request->name,
            'description' => $request->description,
            'weight' => $request->weight ?? 1000,
            'sku' => $request->sku,
            // Reset harga/stok jika mode Varian aktif (akan dihitung ulang di bawah)
            'price' => $request->has_variants ? 0 : $request->price,
            'stock' => $request->has_variants ? 0 : $request->stock,
        ];

        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $dataToUpdate['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($dataToUpdate);

        // 2. Logic Varian
        if ($request->has_variants && is_array($request->variants)) {
            
            // A. Kumpulkan ID varian yang dikirim dari Form (untuk mendeteksi yang dihapus)
            $submittedVariantIds = collect($request->variants)->pluck('id')->filter()->toArray();
            
            // B. Hapus varian di Database yang TIDAK ada di list form (artinya user menghapusnya)
            $product->variants()->whereNotIn('id', $submittedVariantIds)->delete();

            // C. Loop Update / Create
            foreach ($request->variants as $variantData) {
                if (empty($variantData['name'])) continue;

                $product->variants()->updateOrCreate(
                    ['id' => $variantData['id'] ?? null], // Kunci pencarian (jika null, buat baru)
                    [
                        'name' => $variantData['name'],
                        'options' => ['name' => $variantData['name']],
                        'price' => $variantData['price'],
                        'stock' => $variantData['stock'],
                        'sku' => $variantData['sku'] ?? null,
                        'weight' => $request->weight,
                    ]
                );
            }

            // D. Hitung Ulang Total Stok & Harga Terendah Parent
            $product->update([
                'price' => $product->variants()->min('price'),
                'stock' => $product->variants()->sum('stock')
            ]);

        } else {
            // Kasus: User mematikan toggle varian (Switch back to Single)
            // Hapus semua varian
            $product->variants()->delete();
        }

        DB::commit();
        return redirect()->route('client.products.index', $website->id)->with('success', 'Produk berhasil diperbarui!');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Gagal update: ' . $e->getMessage());
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
<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Website;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'sku' => 'nullable|string|max:50',
            'stock' => 'nullable|integer|min:0',
            // Validasi Gambar: Harus gambar, max 2MB (2048 KB)
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        // 2. LOGIK UPLOAD GAMBAR BARU
        $imagePath = null;
        if ($request->hasFile('image')) {
            // Simpan di folder: storage/app/public/products/{id_website}
            // Agar gambar antar toko tidak tercampur
            $imagePath = $request->file('image')->store('products/' . $website->id, 'public');
        }

        // 2. Simpan ke Database
        $website->products()->create([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . Str::random(5), // Slug unik
            'sku' => $request->sku,
            'price' => $request->price,
            'stock' => $request->stock ?? 0,
            'weight' => $request->weight ?? 0,
            'description' => $request->description,
            'image' => $imagePath,
            'is_active' => true,
        ]);

        // 3. Kembali ke halaman list dengan pesan sukses
        return redirect()->route('client.products.index', $website->id)
                         ->with('success', 'Produk berhasil ditambahkan!');
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
        if ($product->website_id !== $website->id) abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'sku' => 'nullable|string|max:50',
            'stock' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $data = [
            'category_id' => $request->category_id,
            'name' => $request->name,
            'sku' => $request->sku,
            'price' => $request->price,
            'stock' => $request->stock,
            'weight' => $request->weight,
            'description' => $request->description,
        ];

        // LOGIKA GANTI GAMBAR
        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            // Upload gambar baru
            $data['image'] = $request->file('image')->store('products/' . $website->id, 'public');
        }

        $product->update($data);
        

        return redirect()->route('client.products.index', $website->id)
                         ->with('success', 'Produk berhasil diperbarui');
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
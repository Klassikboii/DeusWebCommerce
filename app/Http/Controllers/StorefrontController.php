<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; // <--- WAJIB IMPORT
use App\Models\Website;
use Illuminate\Support\Facades\Auth; // <--- WAJIB ADA
use App\Models\Post;

class StorefrontController extends Controller
{
    // Contoh di StorefrontController.php

public function index(Request $request) 
{
    // Kamu bisa mengambil data website yang disuntikkan oleh Middleware seperti ini:
    $website = $request->website; 

    // ATAU: Karena di middleware kamu sudah melakukan View::share('website', $website);
    // Kamu bahkan bisa langsung me-return view tanpa melempar variabel $website lagi!
    
    return view('storefront.index'); 
    // Di dalam file blade storefront.index, variabel $website sudah otomatis tersedia!
}
    
   // Tambahkan parameter $subdomain di sini
    public function blogIndex(Request $request)
    
    {
        $website = $request->website;
  

        // 2. Ambil Postingan (Hanya yang statusnya published, opsional)
        $posts = $website->posts()->latest()->paginate(9); 

        // 3. Tampilkan View
        return view('storefront.blog.index', [
            'website' => $website,
            'posts' => $posts
        ]);
    }

    // Tambahkan parameter $subdomain di sini juga
    public function blogShow(Request $request, $slug)
    {
        $website = $request->website;

        // Cari post berdasarkan slug DAN id website
        $post = $website->posts() // Pakai relasi agar lebih aman
                    ->where('slug', $slug)
                    ->firstOrFail();

        // Opsional: Ambil recent posts untuk sidebar
        $recentPosts = $website->posts()
                        ->where('id', '!=', $post->id)
                        ->latest()
                        ->take(5)
                        ->get();

        return view('storefront.blog.show', [
            'website' => $website,
            'post' => $post,
            'recentPosts' => $recentPosts // Kirim variabel ini ke view
        ]);
    }
public function products(Request $request)
    {
        $website = $request->website;
        
        // 1. QUERY DASAR
        // Kita mulai dari relasi products() tanpa filter status dulu
        $query = $website->products()->where('is_active', true)->where('price', '>', 0);

        // 2. FILTER: PENCARIAN (Search)
        // Cek apakah ada parameter 'search' di URL
        if ($request->has('search') && $request->get('search') != '') {
            $searchTerm = $request->get('search');
            $query->where('name', 'like', '%' . $searchTerm . '%');
        }

        // 3. FILTER: KATEGORI
        if ($request->filled('category')) {
            $category = $website->categories()->where('slug', $request->category)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        // 4. FILTER: HARGA
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // 5. SORTING
        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'price_asc': $query->orderBy('price', 'asc'); break;
                case 'price_desc': $query->orderBy('price', 'desc'); break;
                case 'oldest': $query->oldest(); break;
                default: $query->latest(); break;
            }
        } else {
            $query->latest();
        }

        // 6. EKSEKUSI DATA (Mencegah Error Undefined Variable)
        if ($request->ajax() && $request->input('type') == 'dropdown') {
            // Skenario A: Navbar Dropdown (Limit 5)
            $products = $query->take(5)->get();
        } else {
            // Skenario B: Halaman Utama (Pagination 12)
            $products = $query->paginate(12)->withQueryString();
        }

        // 7. RETURN VIEW
        if ($request->ajax()) {
            if ($request->input('type') == 'dropdown') {
                return view('storefront.products.partials.search_dropdown', compact('products', 'website'))->render();
            }
            return view('storefront.products.partials.product_list', compact('products', 'website'))->render();
        }

        // Data pendukung untuk view utama
        $categories = $website->categories;
        $minProductPrice = $website->products()->min('price') ?? 0;
        $maxProductPrice = $website->products()->max('price') ?? 0;

        return view('storefront.products.index', compact(
            'website', 'products', 'categories', 'minProductPrice', 'maxProductPrice'
        ));
    }
public function product(Request $request, $slug)
{
    // 1. Ambil Data Website
    $website = $request->website;

    // 2. Cari Produk berdasarkan Slug
    $product = $website->products()
        ->where('slug', $slug)
        ->where('is_active', true) // 🚨 Kunci gembok diaktifkan
        ->firstOrFail();

    
// 🚨 1. CARI REKOMENDASI DARI MESIN AI (MARKET BASKET ANALYSIS)
        // Ambil maksimal 4 produk yang direkomendasikan dengan nilai Lift (ikatan) tertinggi
       // 🚨 1. RAK AI: SERING DIBELI BERSAMAAN (Maksimal 4)
        $aiRecommendations = \App\Models\ProductRecommendation::where('website_id', $website->id)
            ->where('product_id', $product->id)
            ->orderBy('lift', 'desc')
            ->take(4)
            ->with('recommendedProduct')
            ->get();

        $aiProducts = collect();
        foreach ($aiRecommendations as $rec) {
            if ($rec->recommendedProduct && $rec->recommendedProduct->is_active) {
                $aiProducts->push($rec->recommendedProduct);
            }
        }

        // 🚨 2. RAK KATEGORI: PRODUK TERKAIT (Maksimal 4)
        // Ambil ID produk AI agar tidak ada barang yang muncul dua kali di halaman yang sama
        $excludeIds = $aiProducts->pluck('id')->toArray();
        $excludeIds[] = $product->id; // Kecualikan produk yang sedang dilihat

        $categoryProducts = \App\Models\Product::where('website_id', $website->id)
            ->where('is_active', true)
            ->where('category_id', $product->category_id)
            ->whereNotIn('id', $excludeIds)
            ->inRandomOrder() 
            ->take(4)
            ->get();

        // Lempar dua variabel terpisah ke view
        return view('storefront.product.show', compact('website', 'product', 'aiProducts', 'categoryProducts'));
}

// --- FITUR CEK PESANAN (TRACK ORDER) ---

    public function trackOrder(Request $request)
    {
        $website = $request->website;
        return view('storefront.track_order', compact('website'));
    }

    public function processTrackOrder(Request $request)
    {
        $website = $request->website;

        // 1. Validasi Input
        $request->validate([
            'order_number' => 'required|string',
            'contact'      => 'required|string', // Bisa Email atau No HP
        ]);

        // 2. Cari Order
        // Logika: Cari order di website ini, dengan No Order yg sesuai,
        // DAN (Email cocok ATAU No HP cocok) -> demi keamanan data.
        $order = $website->orders()
            ->where('order_number', $request->order_number)
            ->where(function($q) use ($request) {
                $q->where('customer_whatsapp', $request->contact);
            })
            ->first();

        // 3. Eksekusi
        if ($order) {
            // Jika statusnya unpaid, arahkan ke pembayaran
            if ($order->status == 'unpaid') {
                return redirect()->route('store.payment', ['order_number' => $order->order_number]);
            }
            
            // Jika sudah paid/shipped, mungkin nanti kita buat halaman detail status (Opsional)
            // Untuk sekarang kita arahkan ke pembayaran juga (biasanya di sana ada status 'Sudah Dibayar')
            return redirect()->route('store.payment', ['order_number' => $order->order_number]);
        }

        // 4. Jika Tidak Ketemu
        return back()->with('error', 'Pesanan tidak ditemukan. Pastikan Nomor Order dan No HP sesuai.');
    }
}
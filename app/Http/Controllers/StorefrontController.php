<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; // <--- WAJIB IMPORT
use App\Models\Website;
use Illuminate\Support\Facades\Auth; // <--- WAJIB ADA
use App\Models\Post;

class StorefrontController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil data website (Otomatis di-inject oleh Middleware ResolveTenant)
        // Kalau pakai $request->merge(['website' => $website]) di middleware
        $website = $request->website; 

        // Fallback jika null (misal akses manual tanpa middleware yang benar)
        if (!$website) {
             abort(404, 'Data Website tidak ditemukan di Request.');
        }

        // 2. Ambil Template & Produk
        $templateName = $website->active_template ?? 'modern';
        $products = $website->products()->latest()->get();
        $sections = $website->sections ?? []; // Data section builder

        // 3. Tampilkan View TANPA Redirect ke Dashboard
        return view("storefront.index", [
            'website' => $website,
            'products' => $products,
            'sections' => $sections, 
        ]);
    }
    
   // Tambahkan parameter $subdomain di sini
    public function blogIndex(Request $request, $subdomain)
    {
        // 1. Ambil data website
        // Kita cari manual saja biar lebih aman & pasti
        $website = Website::where('subdomain', $subdomain)->firstOrFail();

        // 2. Ambil Postingan (Hanya yang statusnya published, opsional)
        $posts = $website->posts()->latest()->paginate(9); 

        // 3. Tampilkan View
        return view('storefront.blog.index', [
            'website' => $website,
            'posts' => $posts
        ]);
    }

    // Tambahkan parameter $subdomain di sini juga
    public function blogShow(Request $request, $subdomain, $slug)
    {
        $website = Website::where('subdomain', $subdomain)->firstOrFail();

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
public function products(Request $request, $subdomain)
    {
        $website = Website::where('subdomain', $subdomain)->firstOrFail();
        
        // 1. QUERY DASAR
        // Kita mulai dari relasi products() tanpa filter status dulu
        $query = $website->products(); 

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
public function product(Request $request, $subdomain, $slug)
{
    // 1. Ambil Data Website
    $website = $request->get('website');
    if (!$website) {
        $website = \App\Models\Website::where('subdomain', $subdomain)->firstOrFail();
    }

    // 2. Cari Produk berdasarkan Slug
    $product = $website->products()
        ->where('slug', $slug)
        // ->where('status', 'active') // Pastikan hanya produk aktif
        ->firstOrFail();

    // 3. Ambil Produk Terkait (Opsional, tapi bagus untuk sales)
    // Ambil 4 produk lain dari kategori yang sama, kecuali produk yang sedang dilihat
    $relatedProducts = $website->products()
        ->where('category_id', $product->category_id)
        ->where('id', '!=', $product->id)
        // ->where('status', 'active')
        ->inRandomOrder()
        ->limit(4)
        ->get();

    // 4. Tampilkan View
    return view('storefront.product.show', [
        'website' => $website,
        'product' => $product,
        'relatedProducts' => $relatedProducts
    ]);
}

// --- FITUR CEK PESANAN (TRACK ORDER) ---

    public function trackOrder(Request $request, $subdomain)
    {
        $website = Website::where('subdomain', $subdomain)->firstOrFail();
        return view('storefront.track_order', compact('website'));
    }

    public function processTrackOrder(Request $request, $subdomain)
    {
        $website = Website::where('subdomain', $subdomain)->firstOrFail();

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
                return redirect()->route('store.payment', ['subdomain' => $subdomain, 'order_number' => $order->order_number]);
            }
            
            // Jika sudah paid/shipped, mungkin nanti kita buat halaman detail status (Opsional)
            // Untuk sekarang kita arahkan ke pembayaran juga (biasanya di sana ada status 'Sudah Dibayar')
            return redirect()->route('store.payment', ['subdomain' => $subdomain, 'order_number' => $order->order_number]);
        }

        // 4. Jika Tidak Ketemu
        return back()->with('error', 'Pesanan tidak ditemukan. Pastikan Nomor Order dan No HP sesuai.');
    }
}
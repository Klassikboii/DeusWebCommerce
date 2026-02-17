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
    
    public function blogIndex(Request $request)
{
    // 1. Ambil data website
    // (Otomatis dari middleware, atau ambil manual jika null)
    $website = $request->get('website');
    
    if (!$website) {
        $subdomain = $request->route('subdomain');
        $website = \App\Models\Website::where('subdomain', $subdomain)->firstOrFail();
    }

    // 2. Ambil Postingan (Hanya yang statusnya published, opsional)
    // Gunakan paginate agar halaman tidak berat
    $posts = $website->posts()->latest()->paginate(9); 

    // 3. Tampilkan View
    return view('storefront.blog.index', [
        'website' => $website,
        'posts' => $posts
    ]);
}

public function blogShow(Request $request, $subdomain, $slug)
{
    $website = $request->get('website');
    if (!$website) {
        $website = \App\Models\Website::where('subdomain', $subdomain)->firstOrFail();
    }

    // Cari post berdasarkan slug DAN id website (biar tidak bocor)
    $post = Post::where('website_id', $website->id)
                ->where('slug', $slug)
                ->firstOrFail();

    return view('storefront.blog.show', [
        'website' => $website,
        'post' => $post
    ]);
}

// Menampilkan halaman Katalog Produk (Search + Filter)
    public function products(Request $request, $subdomain)
    {
        $website = Website::where('subdomain', $subdomain)->firstOrFail();
        
        // Query Dasar (Hanya produk aktif)
        $query = $website->products()->where('status', 'active');

        // 1. FILTER: PENCARIAN (Search)
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // 2. FILTER: KATEGORI
        if ($request->has('category')) {
            // Bisa menerima ID atau Slug kategori
            $category = $website->categories()->where('slug', $request->category)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        // 3. FILTER: RANGE HARGA
        if ($request->has('min_price') && $request->min_price != '') {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price') && $request->max_price != '') {
            $query->where('price', '<=', $request->max_price);
        }

        // 4. SORTING (Urutan)
        if ($request->has('sort')) {
            switch ($request->sort) {
                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;
                case 'newest':
                    $query->latest();
                    break;
                case 'oldest':
                    $query->oldest();
                    break;
                default:
                    $query->latest();
                    break;
            }
        } else {
            $query->latest(); // Default: Terbaru
        }

        // 5. PAGINATION (12 Produk per halaman)
        // withQueryString() penting agar saat pindah halaman, filter tidak hilang
        $products = $query->paginate(12)->withQueryString();

        // Data tambahan untuk Sidebar Filter
        $categories = $website->categories;
        
        // Ambil Harga Min & Max dari seluruh produk (untuk batas input range)
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

    


}
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
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

    // app/Http/Controllers/StorefrontController.php

// app/Http/Controllers/StorefrontController.php

public function preview($id)
{
    // 1. Cari Website berdasarkan ID
    $website = \App\Models\Website::findOrFail($id);
    
    // 2. Ambil Produk (Untuk ditampilkan di section product)
    $products = $website->products()->latest()->get();

    // 3. PERBAIKAN LOGIKA SECTIONS (Anti-Error JSON)
    // Ambil data sections. Karena sudah di-cast 'array' di Model, ini sudah jadi Array.
    // Kita cek: jika null, beri array kosong [].
    $sections = $website->sections ?? []; 

    // 4. Render View yang BENAR (storefront.index)
    // Kita kirim variabel $sections secara terpisah
    return view('storefront.index', [
        'website' => $website,
        'products' => $products,
        'sections' => $sections, // <--- Gunakan variabel ini di View nanti
        'is_preview' => true,    // Penanda mode preview
    ]);
}
}